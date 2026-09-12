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
        $startTime = microtime(true);
        $runTime = date('Y-m-d H:i:s');

        try {
            // Update source status to running
            $this->aiDb->prepare("UPDATE sources SET last_status = 'running' WHERE id = :id")->execute(['id' => $sourceId]);

            // Reset grab state for fresh tracking
            if ($grabber instanceof BaseGrabber) {
                $grabber->resetGrabState();
            }

            $grabbedItems = $grabber->grab();
            $totalFetched = count($grabbedItems);

            // Validate HTTP response code, Cloudflare, and fetched news items
            $httpCode = ($grabber instanceof BaseGrabber) ? $grabber->getMainHttpCode() : 200;
            $httpErr = ($grabber instanceof BaseGrabber) ? $grabber->getMainError() : null;
            $isCf = ($grabber instanceof BaseGrabber) && $grabber->isCloudflareBlocked();

            if ($isCf) {
                throw new Exception($httpErr ?: "Cloudflare mühafizəsi aktivdir (HTTP {$httpCode} / Bot Challenge). Mənbə xəbər vermədi.");
            }

            if ($httpCode !== 0 && $httpCode !== 200) {
                throw new Exception("HTTP {$httpCode} statusu qayıtdı (200 gözlənilirdi). Mənbə ilə əlaqə qurulmadı.");
            }

            if ($httpErr !== null && $totalFetched === 0) {
                throw new Exception($httpErr);
            }

            if ($totalFetched === 0) {
                throw new Exception("Mənbənin cavabında heç bir xəbər tapılmadı (0 xəbər toplandı).");
            }

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

            $duration = round(microtime(true) - $startTime, 2);

            // Update source status to success and record last_grabbed_at & last_news_grabbed_at
            $updateSql = "
                UPDATE sources 
                SET last_grabbed_at = NOW(), 
                    last_status = 'idle', 
                    last_error = NULL" . 
                ($insertedCount > 0 || $totalFetched > 0 ? ", last_news_grabbed_at = NOW()" : "") . "
                WHERE id = :id
            ";
            $this->aiDb->prepare($updateSql)->execute([
                'id' => $sourceId
            ]);

            // Save run record in grab_history
            try {
                $histStmt = $this->aiDb->prepare("
                    INSERT INTO grab_history (
                        source_id, run_time, duration_seconds, news_collected, 
                        news_added, duplicate_count, error_count, status, error_message
                    ) VALUES (
                        :source_id, :run_time, :duration, :news_collected, 
                        :news_added, :duplicate_count, :error_count, 'success', NULL
                    )
                ");
                $histStmt->execute([
                    'source_id' => $sourceId,
                    'run_time' => $runTime,
                    'duration' => $duration,
                    'news_collected' => $totalFetched,
                    'news_added' => $insertedCount,
                    'duplicate_count' => $duplicateCount,
                    'error_count' => $errorCount
                ]);
            } catch (\Throwable $hEx) {
                // history logging safeguard
            }

        } catch (Throwable $e) {
            $duration = round(microtime(true) - $startTime, 2);
            $errorCount++;
            $this->aiDb->prepare("
                UPDATE sources 
                SET last_status = 'error', last_error = :err 
                WHERE id = :id
            ")->execute(['id' => $sourceId, 'err' => $e->getMessage()]);

            // Save error record in grab_history
            try {
                $histStmt = $this->aiDb->prepare("
                    INSERT INTO grab_history (
                        source_id, run_time, duration_seconds, news_collected, 
                        news_added, duplicate_count, error_count, status, error_message
                    ) VALUES (
                        :source_id, :run_time, :duration, 0, 
                        0, 0, 1, 'error', :err
                    )
                ");
                $histStmt->execute([
                    'source_id' => $sourceId,
                    'run_time' => $runTime,
                    'duration' => $duration,
                    'err' => mb_substr($e->getMessage(), 0, 1000)
                ]);
            } catch (\Throwable $hEx) {
                // history logging safeguard
            }

            throw $e;
        }

        return [
            'source_id' => $sourceId,
            'source_name' => $grabber->getName(),
            'run_time' => $runTime,
            'duration_seconds' => $duration,
            'total_fetched' => $totalFetched,
            'news_collected' => $totalFetched,
            'new_items' => $insertedCount,
            'news_added' => $insertedCount,
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

    /**
     * Get grab history logs with optional filtering and pagination
     */
    public function getGrabHistory(?string $sourceId = null, int $limit = 25, int $page = 1): array {
        $offset = ($page - 1) * $limit;
        $where = [];
        $params = [];

        if ($sourceId && $sourceId !== 'all') {
            $where[] = "h.source_id = :sid";
            $params['sid'] = $sourceId;
        }

        $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $countStmt = $this->aiDb->prepare("SELECT COUNT(*) FROM grab_history h {$whereSql}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $query = "
            SELECT h.*, s.name as source_name, s.url as source_url, s.category as source_category
            FROM grab_history h
            LEFT JOIN sources s ON h.source_id = s.id
            {$whereSql}
            ORDER BY h.run_time DESC, h.id DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $this->aiDb->prepare($query);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        foreach ($items as &$item) {
            $item['id'] = (int)$item['id'];
            $item['news_collected'] = (int)$item['news_collected'];
            $item['news_added'] = (int)$item['news_added'];
            $item['duplicate_count'] = (int)$item['duplicate_count'];
            $item['error_count'] = (int)$item['error_count'];
            $item['duration_seconds'] = (float)$item['duration_seconds'];
        }

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => $limit > 0 ? (int)ceil($total / $limit) : 1
        ];
    }

    /**
     * Get aggregate statistics from grab_history
     */
    public function getGrabHistoryStats(): array {
        try {
            $stats = $this->aiDb->query("
                SELECT 
                    COUNT(*) as total_runs,
                    COALESCE(SUM(news_collected), 0) as total_collected,
                    COALESCE(SUM(news_added), 0) as total_added,
                    COALESCE(SUM(duplicate_count), 0) as total_duplicates,
                    MAX(run_time) as last_run_time
                FROM grab_history
            ")->fetch();

            return [
                'total_runs' => (int)($stats['total_runs'] ?? 0),
                'total_collected' => (int)($stats['total_collected'] ?? 0),
                'total_added' => (int)($stats['total_added'] ?? 0),
                'total_duplicates' => (int)($stats['total_duplicates'] ?? 0),
                'last_run_time' => $stats['last_run_time'] ?? null
            ];
        } catch (\Throwable $e) {
            return [
                'total_runs' => 0,
                'total_collected' => 0,
                'total_added' => 0,
                'total_duplicates' => 0,
                'last_run_time' => null
            ];
        }
    }
}
