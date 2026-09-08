<?php
/**
 * Duplicate News Detector
 * Compares incoming news across sources and against main web articles
 */

namespace AiWriter\Core;

require_once __DIR__ . '/Database.php';

use PDO;

class DuplicateDetector {
    private ?PDO $aiDb = null;
    private ?PDO $webDb = null;
    private float $threshold = 0.70;
    private bool $enabled = true;

    public function __construct() {
        try {
            $this->aiDb = Database::getAiDB();
            $enabledSetting = Database::getSetting('duplicate_check_enabled', '1');
            $this->enabled = ($enabledSetting === '1');

            $thresholdSetting = Database::getSetting('duplicate_threshold', '0.70');
            $this->threshold = (float)$thresholdSetting;
        } catch (\Throwable $e) {
            $this->aiDb = null;
        }

        try {
            $this->webDb = Database::getWebDB();
        } catch (\Throwable $e) {
            $this->webDb = null;
        }
    }

    /**
     * Check if a news item is a duplicate
     * 
     * @param string $title Incoming news title
     * @param string $sourceUrl Incoming news URL
     * @param int|null $excludeId ID in grabbed_news to exclude (for reprocessing)
     * @return array ['is_duplicate' => bool, 'reason' => string, 'matched_id' => mixed, 'similarity' => float]
     */
    public function check(string $title, string $sourceUrl = '', ?int $excludeId = null): array {
        if (!$this->enabled || !$this->aiDb) {
            return ['is_duplicate' => false, 'reason' => '', 'matched_id' => null, 'similarity' => 0.0];
        }

        // 1. Exact URL match in grabbed_news
        if (!empty($sourceUrl)) {
            $stmt = $this->aiDb->prepare("
                SELECT id, source_title, status FROM grabbed_news 
                WHERE source_url = :url " . ($excludeId ? "AND id != :ex_id" : "") . "
                LIMIT 1
            ");
            $params = ['url' => $sourceUrl];
            if ($excludeId) $params['ex_id'] = $excludeId;
            $stmt->execute($params);
            $exact = $stmt->fetch();
            if ($exact) {
                return [
                    'is_duplicate' => true,
                    'reason' => "Exact URL already exists (ID: {$exact['id']}, Status: {$exact['status']})",
                    'matched_id' => $exact['id'],
                    'similarity' => 1.0
                ];
            }
        }

        // 2. Title similarity in grabbed_news (especially posted or new news from last 14 days)
        $stmt = $this->aiDb->prepare("
            SELECT id, source_title, rewritten_title, status FROM grabbed_news 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
            " . ($excludeId ? "AND id != :ex_id" : "") . "
            ORDER BY id DESC LIMIT 300
        ");
        $params = [];
        if ($excludeId) $params['ex_id'] = $excludeId;
        $stmt->execute($params);
        $recentNews = $stmt->fetchAll();

        foreach ($recentNews as $row) {
            // Check against source title
            $score1 = $this->calculateSimilarity($title, $row['source_title']);
            if ($score1 >= $this->threshold) {
                return [
                    'is_duplicate' => true,
                    'reason' => sprintf("Similar to grabbed news #%d (%.0f%% match): '%s'", $row['id'], $score1 * 100, $row['source_title']),
                    'matched_id' => $row['id'],
                    'similarity' => $score1
                ];
            }

            // Check against rewritten title if present
            if (!empty($row['rewritten_title'])) {
                $score2 = $this->calculateSimilarity($title, $row['rewritten_title']);
                if ($score2 >= $this->threshold) {
                    return [
                        'is_duplicate' => true,
                        'reason' => sprintf("Similar to rewritten news #%d (%.0f%% match): '%s'", $row['id'], $score2 * 100, $row['rewritten_title']),
                        'matched_id' => $row['id'],
                        'similarity' => $score2
                    ];
                }
            }
        }

        // 3. Title similarity against target web articles table
        if ($this->webDb) {
            try {
                $stmtWeb = $this->webDb->query("
                    SELECT id, title FROM articles 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
                    ORDER BY created_at DESC LIMIT 200
                ");
                $webArticles = $stmtWeb->fetchAll();
                foreach ($webArticles as $art) {
                    $score = $this->calculateSimilarity($title, $art['title']);
                    if ($score >= $this->threshold) {
                        return [
                            'is_duplicate' => true,
                            'reason' => sprintf("Already posted on web article '%s' (%.0f%% match)", $art['title'], $score * 100),
                            'matched_id' => $art['id'],
                            'similarity' => $score
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // If web db is unreachable, continue with grabbed news check
            }
        }

        return ['is_duplicate' => false, 'reason' => '', 'matched_id' => null, 'similarity' => 0.0];
    }

    /**
     * Compute normalized similarity score between two headlines (0.0 to 1.0)
     */
    public function calculateSimilarity(string $text1, string $text2): float {
        $tokens1 = $this->tokenize($text1);
        $tokens2 = $this->tokenize($text2);

        if (empty($tokens1) || empty($tokens2)) return 0.0;

        // Jaccard similarity of word tokens
        $intersection = array_intersect($tokens1, $tokens2);
        $union = array_unique(array_merge($tokens1, $tokens2));

        $jaccard = count($union) > 0 ? count($intersection) / count($union) : 0.0;

        // String similar_text ratio
        similar_text(mb_strtolower($text1), mb_strtolower($text2), $percent);
        $stringSim = $percent / 100.0;

        // Weighted hybrid score
        return ($jaccard * 0.7) + ($stringSim * 0.3);
    }

    /**
     * Tokenize text into normalized lowercase alphanumeric words
     */
    private function tokenize(string $text): array {
        $clean = mb_strtolower($text);
        // Replace punctuation with spaces
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $clean);
        $words = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);

        // Filter out short stop words
        $stopWords = ['ve', 'və', 'ilə', 'bu', 'bir', 'the', 'a', 'an', 'in', 'on', 'at', 'for', 'of', 'and', 'to', 'is', 'it', 'da', 'də', 'haqqında', 'yeni', 'new'];
        return array_values(array_filter($words, function($w) use ($stopWords) {
            return mb_strlen($w) > 2 && !in_array($w, $stopWords);
        }));
    }
}
