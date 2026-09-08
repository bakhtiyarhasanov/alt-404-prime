<?php
/**
 * Grabber Manager Engine
 * Manages source lifecycle, runs grabbers, orchestrates deduplication, rewriting, and publishing
 */

namespace AiWriter\Core;

use PDO;
use Exception;
use Throwable;

class GrabberManager {
    private PDO $aiDb;
    private array $grabbers = [];
    private DuplicateDetector $detector;
    private AiRewriter $rewriter;
    private Publisher $publisher;

    public function __construct() {
        $this->aiDb = Database::getAiDB();
        $this->detector = new DuplicateDetector();
        $this->rewriter = new AiRewriter();
        $this->publisher = new Publisher();

        $this->registerDefaultGrabbers();
    }

    /**
     * Register all available grabber instances
     */
    private function registerDefaultGrabbers(): void {
        $grabberClasses = [
            Grabbers\DonanimHaberGrabber::class,
            Grabbers\KaldataGrabber::class,
            Grabbers\LogGrabber::class,
            Grabbers\ReutersGrabber::class,
            Grabbers\DexertoGrabber::class,
            Grabbers\CnetGrabber::class,
            Grabbers\EngadgetGrabber::class,
            Grabbers\AzertagGrabber::class,
            Grabbers\IddaGrabber::class,
            Grabbers\AynaGrabber::class,
            Grabbers\MincomGrabber::class,
            Grabbers\CertGrabber::class,
        ];

        foreach ($grabberClasses as $class) {
            if (class_exists($class)) {
                $inst = new $class();
                $this->grabbers[$inst->getId()] = $inst;
            }
        }
    }

    /**
     * Get all registered grabber instances
     * @return GrabberInterface[]
     */
    public function getGrabbers(): array {
        return $this->grabbers;
    }

    /**
     * Get specific grabber by source id
     */
    public function getGrabber(string $id): ?GrabberInterface {
        return $this->grabbers[$id] ?? null;
    }

    /**
     * Run grabber for a specific source
     */
    public function runSource(string $sourceId, bool $force = false): array {
        $grabber = $this->getGrabber($sourceId);
        if (!$grabber) {
            throw new Exception("Grabber tapılmadı: {$sourceId}");
        }

        // Check if source is enabled in DB
        $stmt = $this->aiDb->prepare("SELECT * FROM sources WHERE id = :id");
        $stmt->execute(['id' => $sourceId]);
        $source = $stmt->fetch();

        if ($source && !(bool)$source['is_enabled'] && !$force) {
            return ['status' => 'skipped', 'message' => 'Source is disabled'];
        }

        $insertedCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;

        try {
            // Update source status to running
            $this->aiDb->prepare("UPDATE sources SET last_status = 'running' WHERE id = :id")->execute(['id' => $sourceId]);

            $grabbedItems = $grabber->grab();

            foreach ($grabbedItems as $item) {
                $extId = $item['external_id'] ?? md5($item['url']);
                $url = $item['url'] ?? '';
                $title = trim($item['title'] ?? '');

                if (empty($title) || empty($url)) continue;

                // Check if already stored in grabbed_news by source_id + external_id
                $checkStmt = $this->aiDb->prepare("SELECT id, status FROM grabbed_news WHERE source_id = :sid AND external_id = :eid");
                $checkStmt->execute(['sid' => $sourceId, 'eid' => $extId]);
                $existing = $checkStmt->fetch();

                if ($existing) {
                    continue; // Already processed in past runs
                }

                // Check for duplicates across all sources and main web articles
                $dupCheck = $this->detector->check($title, $url);
                $status = $dupCheck['is_duplicate'] ? 'duplicate' : 'new';
                $statusMsg = $dupCheck['is_duplicate'] ? $dupCheck['reason'] : null;
                $isDup = $dupCheck['is_duplicate'] ? 1 : 0;
                $dupOfId = is_numeric($dupCheck['matched_id'] ?? null) ? (int)$dupCheck['matched_id'] : null;

                if ($isDup) {
                    $duplicateCount++;
                } else {
                    $insertedCount++;
                }

                $insertStmt = $this->aiDb->prepare("
                    INSERT INTO grabbed_news (
                        source_id, external_id, source_url, source_title, 
                        source_excerpt, source_content, source_image_url, 
                        category, tags, status, status_message, is_duplicate, duplicate_of_id
                    ) VALUES (
                        :source_id, :external_id, :source_url, :source_title,
                        :source_excerpt, :source_content, :source_image_url,
                        :category, :tags, :status, :status_message, :is_duplicate, :duplicate_of_id
                    )
                ");

                $insertStmt->execute([
                    'source_id' => $sourceId,
                    'external_id' => $extId,
                    'source_url' => $url,
                    'source_title' => $title,
                    'source_excerpt' => $item['excerpt'] ?? '',
                    'source_content' => $item['content'] ?? '',
                    'source_image_url' => $item['image_url'] ?? '',
                    'category' => $item['category'] ?? $grabber->getDefaultCategory(),
                    'tags' => json_encode($item['tags'] ?? []),
                    'status' => $status,
                    'status_message' => $statusMsg,
                    'is_duplicate' => $isDup,
                    'duplicate_of_id' => $dupOfId
                ]);
            }

            // Update source status to success
            $this->aiDb->prepare("
                UPDATE sources 
                SET last_grabbed_at = NOW(), last_status = 'idle', last_error = NULL 
                WHERE id = :id
            ")->execute(['id' => $sourceId]);

        } catch (Throwable $e) {
            $errorCount++;
            $this->aiDb->prepare("
                UPDATE sources 
                SET last_status = 'error', last_error = :err 
                WHERE id = :id
            ")->execute(['id' => $sourceId, 'err' => $e->getMessage()]);

            throw $e;
        }

        return [
            'source_id' => $sourceId,
            'source_name' => $grabber->getName(),
            'total_fetched' => count($grabbedItems ?? []),
            'new_items' => $insertedCount,
            'duplicates' => $duplicateCount,
            'errors' => $errorCount
        ];
    }

    /**
     * Run all enabled sources
     */
    public function runAllEnabled(bool $respectInterval = true): array {
        $stmt = $this->aiDb->query("SELECT * FROM sources WHERE is_enabled = 1");
        $sources = $stmt->fetchAll();
        $results = [];

        foreach ($sources as $src) {
            // Check retry interval
            if ($respectInterval && !empty($src['last_grabbed_at'])) {
                $last = strtotime($src['last_grabbed_at']);
                $intervalSecs = ($src['retry_interval_minutes'] ?? 30) * 60;
                if ((time() - $last) < $intervalSecs) {
                    $results[$src['id']] = ['status' => 'skipped', 'reason' => 'Interval not elapsed yet'];
                    continue;
                }
            }

            try {
                $results[$src['id']] = $this->runSource($src['id'], true);
            } catch (Throwable $e) {
                $results[$src['id']] = ['status' => 'error', 'error' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Process a single news item: AI Rewrite / Regenerate & Publish as draft
     */
    public function processNewsItem(int $id, bool $forceRewrite = false): array {
        // Fetch news item with source details
        $stmt = $this->aiDb->prepare("
            SELECT n.*, s.rewrite_enabled, s.name as source_name
            FROM grabbed_news n
            JOIN sources s ON n.source_id = s.id
            WHERE n.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch();

        if (!$item) {
            throw new Exception("Xəbər tapılmadı: ID #{$id}");
        }

        // Mark as generating
        $this->aiDb->prepare("UPDATE grabbed_news SET status = 'generating', status_message = 'AI generasiya / yenidən yazma davam edir...' WHERE id = :id")->execute(['id' => $id]);

        try {
            $isRewrite = $forceRewrite ? true : (bool)$item['rewrite_enabled'];
            $rewritten = $this->rewriter->rewrite($item, $isRewrite);

            // Save rewritten content
            $updateStmt = $this->aiDb->prepare("
                UPDATE grabbed_news 
                SET rewritten_title = :title,
                    rewritten_excerpt = :excerpt,
                    rewritten_content = :content,
                    rewritten_tags = :tags
                WHERE id = :id
            ");
            $updateStmt->execute([
                'title' => $rewritten['title'],
                'excerpt' => $rewritten['excerpt'],
                'content' => $rewritten['content'],
                'tags' => json_encode($rewritten['tags']),
                'id' => $id
            ]);

            // Publish as draft into web articles table
            $publishResult = $this->publisher->publishAsDraft($id, $rewritten);

            // Fetch complete fresh item from DB to return to client
            $stmtFresh = $this->aiDb->prepare("
                SELECT n.*, s.name as source_name, s.rewrite_enabled as source_rewrite_enabled
                FROM grabbed_news n
                JOIN sources s ON n.source_id = s.id
                WHERE n.id = :id
            ");
            $stmtFresh->execute(['id' => $id]);
            $freshItem = $stmtFresh->fetch();
            if ($freshItem) {
                $freshItem['tags'] = is_string($freshItem['tags']) ? json_decode($freshItem['tags'], true) : $freshItem['tags'];
                $freshItem['rewritten_tags'] = is_string($freshItem['rewritten_tags']) ? json_decode($freshItem['rewritten_tags'], true) : $freshItem['rewritten_tags'];
                $freshItem['is_duplicate'] = (bool)$freshItem['is_duplicate'];
                $freshItem['source_rewrite_enabled'] = (bool)$freshItem['source_rewrite_enabled'];
            }

            return [
                'success' => true,
                'news_id' => $id,
                'rewritten' => $rewritten,
                'article_id' => $publishResult['article_id'],
                'slug' => $publishResult['slug'],
                'item' => $freshItem
            ];

        } catch (Throwable $e) {
            $this->aiDb->prepare("
                UPDATE grabbed_news 
                SET status = 'error', status_message = :err 
                WHERE id = :id
            ")->execute(['id' => $id, 'err' => $e->getMessage()]);

            throw $e;
        }
    }
}
