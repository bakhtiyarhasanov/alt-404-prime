<?php
/**
 * Publisher Engine
 * Inserts rewritten or direct-copied news into the Alt404 web database as Draft (published = 0)
 */

namespace AiWriter\Core;

require_once __DIR__ . '/Database.php';

use PDO;
use Exception;

class Publisher {
    private ?PDO $webDb = null;
    private PDO $aiDb;

    public function __construct() {
        $this->aiDb = Database::getAiDB();
        try {
            $this->webDb = Database::getWebDB();
        } catch (\Throwable $e) {
            $this->webDb = null;
        }
    }

    /**
     * Publish an article into the web articles table as a Draft
     * 
     * @param int $grabbedNewsId ID in grabbed_news table
     * @param array|null $rewrittenData Optional override of rewritten data
     * @return array ['success' => bool, 'article_id' => string, 'slug' => string]
     */
    public function publishAsDraft(int $grabbedNewsId, ?array $rewrittenData = null): array {
        if (!$this->webDb) {
            throw new Exception("Əsas veb saytın məlumat bazasına (alt404) qoşulmaq mümkün olmadı.");
        }

        // Fetch the grabbed news item
        $stmt = $this->aiDb->prepare("SELECT * FROM grabbed_news WHERE id = :id");
        $stmt->execute(['id' => $grabbedNewsId]);
        $item = $stmt->fetch();

        if (!$item) {
            throw new Exception("Xəbər tapılmadı: ID #{$grabbedNewsId}");
        }

        $title = $rewrittenData['title'] ?? $item['rewritten_title'] ?? $item['source_title'];
        $excerpt = $rewrittenData['excerpt'] ?? $item['rewritten_excerpt'] ?? $item['source_excerpt'] ?? '';
        $content = $rewrittenData['content'] ?? $item['rewritten_content'] ?? $item['source_content'] ?? '';
        $tags = $rewrittenData['tags'] ?? (is_string($item['rewritten_tags']) ? json_decode($item['rewritten_tags'], true) : $item['rewritten_tags']);
        if (empty($tags)) {
            $tags = is_string($item['tags']) ? json_decode($item['tags'], true) : ($item['tags'] ?? []);
        }

        $category = $item['category'] ?: 'texnologiya';
        $imageUrl = $item['source_image_url'] ?: '';

        if (empty($title)) {
            throw new Exception("Başlıq boş ola bilməz.");
        }

        // Calculate reading time
        $wordCount = str_word_count(strip_tags($content));
        $readingTime = max(1, (int)ceil($wordCount / 180));

        // If article was already posted to web database, update it (Regeneration mode)
        if (!empty($item['posted_article_id'])) {
            $checkStmt = $this->webDb->prepare("SELECT id, slug, versions FROM articles WHERE id = :id");
            $checkStmt->execute(['id' => $item['posted_article_id']]);
            $existing = $checkStmt->fetch();

            if ($existing) {
                $existingVersions = json_decode($existing['versions'] ?? '[]', true) ?: [];
                $newVerNum = count($existingVersions) + 1;
                $existingVersions[] = [
                    'version' => $newVerNum,
                    'title' => $title,
                    'content' => $content,
                    'created_at' => date('c'),
                    'author' => 'AI Writer (Regenerated v' . $newVerNum . ')'
                ];

                $updateArt = $this->webDb->prepare("
                    UPDATE articles SET
                        title = :title,
                        excerpt = :excerpt,
                        content = :content,
                        tags = :tags,
                        reading_time = :reading_time,
                        versions = :versions,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $updateArt->execute([
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'content' => $content,
                    'tags' => json_encode($tags ?: ['Texnologiya']),
                    'reading_time' => $readingTime,
                    'versions' => json_encode($existingVersions),
                    'id' => $existing['id']
                ]);

                // Update grabbed_news record
                $this->aiDb->prepare("
                    UPDATE grabbed_news 
                    SET status = 'posted', 
                        posted_at = NOW(),
                        status_message = :msg
                    WHERE id = :id
                ")->execute([
                    'msg' => "Xəbər yenidən yazıldı və veb qaralama yeniləndi (slug: {$existing['slug']})",
                    'id' => $grabbedNewsId
                ]);

                return [
                    'success' => true,
                    'article_id' => $existing['id'],
                    'slug' => $existing['slug']
                ];
            }
        }

        // Generate slug with Azerbaijani character transliteration
        $baseSlug = $this->slugify($title);
        $slug = $baseSlug;
        $i = 1;

        // Ensure unique slug in articles table
        while (true) {
            $stmtSlug = $this->webDb->prepare("SELECT 1 FROM articles WHERE slug = :slug");
            $stmtSlug->execute(['slug' => $slug]);
            if (!$stmtSlug->fetch()) {
                break;
            }
            $slug = $baseSlug . '-' . $i++;
        }

        // Generate UUID for article
        $articleId = $this->generateUuid();

        // Initial version tracking
        $versions = json_encode([[
            'version' => 1,
            'title' => $title,
            'content' => $content,
            'created_at' => date('c'),
            'author' => 'AI Writer (' . ($item['source_id'] ?? 'auto') . ')'
        ]]);

        // Insert as Draft (published = 0)
        $insertStmt = $this->webDb->prepare("
            INSERT INTO articles (
                id, title, slug, excerpt, content, category, 
                image_url, tags, featured, published, updating, 
                reading_time, versions, created_at, updated_at
            ) VALUES (
                :id, :title, :slug, :excerpt, :content, :category,
                :image_url, :tags, 0, 0, 0,
                :reading_time, :versions, NOW(), NOW()
            )
        ");

        $insertStmt->execute([
            'id' => $articleId,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'category' => $category,
            'image_url' => $imageUrl,
            'tags' => json_encode($tags ?: ['Texnologiya']),
            'reading_time' => $readingTime,
            'versions' => $versions
        ]);

        // Update grabbed_news record to status 'posted'
        $updateStmt = $this->aiDb->prepare("
            UPDATE grabbed_news 
            SET status = 'posted', 
                posted_article_id = :art_id, 
                posted_at = NOW(),
                status_message = :msg
            WHERE id = :id
        ");
        $updateStmt->execute([
            'art_id' => $articleId,
            'msg' => "Successfully posted as draft to web articles (slug: {$slug})",
            'id' => $grabbedNewsId
        ]);

        return [
            'success' => true,
            'article_id' => $articleId,
            'slug' => $slug
        ];
    }

    /**
     * Generate slug with Azerbaijani transliteration
     */
    private function slugify(string $text): string {
        $translit = [
            'ə' => 'e', 'Ə' => 'e',
            'ı' => 'i', 'I' => 'i', 'İ' => 'i',
            'ö' => 'o', 'Ö' => 'o',
            'ü' => 'u', 'Ü' => 'u',
            'ğ' => 'g', 'Ğ' => 'g',
            'ş' => 's', 'Ş' => 's',
            'ç' => 'c', 'Ç' => 'c',
        ];
        $text = strtr($text, $translit);
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }

    private function generateUuid(): string {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
