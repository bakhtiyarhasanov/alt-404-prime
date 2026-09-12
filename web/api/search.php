<?php
/**
 * Public AJAX Search API for articles
 * Supports live query & category filtering.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';

if (!headers_sent()) {
    header("Content-Type: application/json; charset=UTF-8");
}

$query = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? 'ALL');

if (mb_strlen($query) < 2) {
    echo json_encode([
        'success' => true,
        'query' => $query,
        'category' => $category,
        'count' => 0,
        'results' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $rows = searchArticles($query, $category);
    $results = array_map(function($a) {
        $tags = $a['tags'] ?? [];
        if (is_string($tags)) {
            $tags = json_decode($tags, true) ?: [];
        }

        return [
            'id' => (string)$a['id'],
            'title' => $a['title'],
            'slug' => $a['slug'],
            'category' => $a['category'],
            'category_label' => getCategoryLabel($a['category']),
            'excerpt' => $a['excerpt'] ?? '',
            'image_url' => $a['image_url'] ?? '',
            'reading_time' => (int)($a['reading_time'] ?? 3) . ' dəq',
            'date' => formatDateAz($a['created_at']),
            'tags' => $tags,
        ];
    }, $rows);

    echo json_encode([
        'success' => true,
        'query' => $query,
        'category' => $category,
        'count' => count($results),
        'results' => $results
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Axtarış zamanı xəta baş verdi',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
