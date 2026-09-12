<?php
/**
 * Helper functions — date formatting, slugify, SEO, data fetchers.
 */

require_once __DIR__ . '/../config/database.php';

// ── Date Formatting ──────────────────────────────────────────────────────────

function formatDateAz(string $dateStr): string {
    $months = [
        'yanvar', 'fevral', 'mart', 'aprel', 'may', 'iyun',
        'iyul', 'avqust', 'sentyabr', 'oktyabr', 'noyabr', 'dekabr',
    ];
    $ts = strtotime($dateStr);
    if ($ts === false) return '';
    return date('j', $ts) . ' ' . $months[(int)date('n', $ts) - 1] . ' ' . date('Y', $ts);
}

// ── Slugify ──────────────────────────────────────────────────────────────────

function slugify(string $text): string {
    $map = ['ə' => 'e', 'ö' => 'o', 'ü' => 'u', 'ğ' => 'g', 'ı' => 'i', 'ş' => 's', 'ç' => 'c'];
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/\s+/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

// ── Reading time ─────────────────────────────────────────────────────────────

function estimateReadingTime(string $content): int {
    $plain = strip_tags($content);
    $words = str_word_count($plain);
    return max(1, (int)ceil($words / 200));
}

// ── Data Fetchers ────────────────────────────────────────────────────────────

function getCategories(): array {
    $db = getDB();
    $stmt = $db->query('SELECT * FROM categories ORDER BY sort_order ASC');
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['show_on_site'] = (bool)$row['show_on_site'];
        $row['curated_tags'] = json_decode($row['curated_tags'] ?? '[]', true) ?: [];
    }
    return $rows;
}

function getVisibleCategories(): array {
    return array_values(array_filter(getCategories(), fn($c) => $c['show_on_site']));
}

function getCategoryLabel(string $slug): string {
    $db = getDB();
    $stmt = $db->prepare('SELECT label FROM categories WHERE slug = ?');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    return $row ? $row['label'] : $slug;
}

function isValidCategory(string $slug): bool {
    $db = getDB();
    $stmt = $db->prepare('SELECT 1 FROM categories WHERE slug = ?');
    $stmt->execute([$slug]);
    return (bool)$stmt->fetch();
}

function getPublishedArticles(int $limit = 100, int $offset = 0): array {
    $db = getDB();
    $stmt = $db->prepare(
        'SELECT id, title, slug, excerpt, category, image_url, tags, featured, published, updating, reading_time, views, created_at, updated_at
         FROM articles 
         WHERE published = 1 
           AND (start_time IS NULL OR start_time <= NOW()) 
           AND (end_time IS NULL OR end_time >= NOW()) 
         ORDER BY created_at DESC LIMIT ? OFFSET ?'
    );
    $stmt->execute([$limit, $offset]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['tags'] = json_decode($row['tags'] ?? '[]', true) ?: [];
        $row['featured'] = (bool)$row['featured'];
        $row['published'] = (bool)$row['published'];
        $row['updating'] = (bool)($row['updating'] ?? false);
    }
    return $rows;
}

function getTotalPublishedArticlesCount(): int {
    $db = getDB();
    $stmt = $db->query(
        'SELECT COUNT(*) FROM articles 
         WHERE published = 1 
           AND (start_time IS NULL OR start_time <= NOW()) 
           AND (end_time IS NULL OR end_time >= NOW())'
    );
    return (int)$stmt->fetchColumn();
}

function getArticleBySlug(string $slug): ?array {
    $db = getDB();
    $stmt = $db->prepare('
        SELECT * FROM articles 
        WHERE slug = ? 
          AND published = 1 
          AND (start_time IS NULL OR start_time <= NOW()) 
          AND (end_time IS NULL OR end_time >= NOW()) 
        LIMIT 1
    ');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    if (!$row) return null;
    $row['tags'] = json_decode($row['tags'] ?? '[]', true) ?: [];
    $row['featured'] = (bool)$row['featured'];
    $row['published'] = (bool)$row['published'];
    $row['updating'] = (bool)($row['updating'] ?? false);
    $row['versions'] = json_decode($row['versions'] ?? '[]', true) ?: [];
    return $row;
}

function getArticlesByCategorySlugs(string $category, int $limit = 20): array {
    $db = getDB();
    $stmt = $db->prepare(
        'SELECT id, title, slug, excerpt, category, image_url, tags, featured, published, updating, reading_time, views, created_at, updated_at
         FROM articles 
         WHERE published = 1 
           AND (start_time IS NULL OR start_time <= NOW()) 
           AND (end_time IS NULL OR end_time >= NOW()) 
           AND category = ? 
         ORDER BY created_at DESC LIMIT ?'
    );
    $stmt->execute([$category, $limit]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['tags'] = json_decode($row['tags'] ?? '[]', true) ?: [];
        $row['featured'] = (bool)$row['featured'];
        $row['published'] = (bool)$row['published'];
        $row['updating'] = (bool)($row['updating'] ?? false);
    }
    return $rows;
}

function getRelatedArticles(string $category, string $excludeId, int $limit = 3): array {
    $db = getDB();
    $stmt = $db->prepare(
        'SELECT id, title, slug, excerpt, category, image_url, tags, featured, reading_time, views, created_at, updating
         FROM articles 
         WHERE published = 1 
           AND (start_time IS NULL OR start_time <= NOW()) 
           AND (end_time IS NULL OR end_time >= NOW()) 
           AND category = ? 
           AND id != ? 
         ORDER BY created_at DESC LIMIT ?'
    );
    $stmt->execute([$category, $excludeId, $limit]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['tags'] = json_decode($row['tags'] ?? '[]', true) ?: [];
        $row['featured'] = (bool)$row['featured'];
        $row['updating'] = (bool)($row['updating'] ?? false);
    }
    return $rows;
}

function searchArticles(string $query, ?string $category = null): array {
    $db = getDB();
    $like = '%' . $query . '%';
    $params = [$like, $like, $like];
    $catSql = '';
    if (!empty($category) && strtoupper($category) !== 'ALL') {
        $catSql = ' AND category = ?';
        $params[] = $category;
    }
    $stmt = $db->prepare(
        "SELECT id, title, slug, excerpt, category, image_url, tags, featured, reading_time, views, created_at, updating
         FROM articles 
         WHERE published = 1 
           AND (start_time IS NULL OR start_time <= NOW()) 
           AND (end_time IS NULL OR end_time >= NOW()) 
           AND (title LIKE ? OR excerpt LIKE ? OR tags LIKE ?)
           $catSql
         ORDER BY created_at DESC LIMIT 50"
    );
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['tags'] = json_decode($row['tags'] ?? '[]', true) ?: [];
        $row['featured'] = (bool)$row['featured'];
        $row['updating'] = (bool)($row['updating'] ?? false);
    }
    return $rows;
}

function incrementArticleViews(string $id): void {
    $db = getDB();
    $stmt = $db->prepare('UPDATE articles SET views = views + 1 WHERE id = ?');
    $stmt->execute([$id]);
}

function getAds(): array {
    $db = getDB();
    $stmt = $db->query('SELECT * FROM ads');
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['enabled'] = (bool)$row['enabled'];
    }
    return $rows;
}

function getAd(string $id): ?array {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM ads WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) return null;
    $row['enabled'] = (bool)$row['enabled'];
    return $row;
}

function extractYouTubeID(string $url): ?string {
    if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $url, $m)) {
        return $m[1];
    }
    return null;
}

function getYouTubeThumbnail(string $url, string $fallback = ''): string {
    $ytId = extractYouTubeID($url);
    if ($ytId) {
        return "https://img.youtube.com/vi/{$ytId}/hqdefault.jpg";
    }
    return $fallback;
}

function getHomeVideos(int $limit = 4): array {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM home_videos ORDER BY sort_order ASC LIMIT ?');
    $stmt->execute([$limit]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        // Automatically derive YouTube thumbnail if missing
        if (empty($row['thumbnail_url']) && !empty($row['youtube_url'])) {
            $row['thumbnail_url'] = getYouTubeThumbnail($row['youtube_url']);
        }
    }
    return $rows;
}

function getProjects(bool $onlyEnabled = true, int $limit = 50): array {
    $db = getDB();
    $where = $onlyEnabled ? 'WHERE enabled = 1' : '';
    $stmt = $db->prepare("SELECT * FROM projects {$where} ORDER BY sort_order ASC, created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['enabled'] = (bool)$row['enabled'];
        $row['sort_order'] = (int)$row['sort_order'];
    }
    return $rows;
}

function getProject(string $id): ?array {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM projects WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) return null;
    $row['enabled'] = (bool)$row['enabled'];
    $row['sort_order'] = (int)$row['sort_order'];
    return $row;
}

function getSettingValue(string $key, string $default = ''): string {
    static $settings = null;
    if ($settings === null) {
        try {
            $db = getDB();
            $stmt = $db->query('SELECT `key`, `value` FROM settings');
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['key']] = $row['value'] ?? '';
            }
        } catch (PDOException $e) {
            $settings = [];
        }
    }
    return $settings[$key] ?? $default;
}

// ── HTML escaping shortcut ───────────────────────────────────────────────────

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// ── Render ad zone HTML ──────────────────────────────────────────────────────

function renderAdZone(string $id, string $extraClass = ''): string {
    $ad = getAd($id);
    if (!$ad || !$ad['enabled']) return '';

    $isSidebar = strpos($id, 'sidebar') !== false;
    $h = $isSidebar ? 600 : 90;
    $w = $isSidebar ? '160px' : '100%';

    $href = e($ad['link_url'] ?: '#');
    $imgHtml = '';
    if ($ad['image_url']) {
        $imgHtml = '<img src="' . e($ad['image_url']) . '" alt="Reklam" class="ad-zone-img">';
    } else {
        $imgHtml = '<div class="ad-zone-empty"><span class="ad-zone-label-text">Reklam</span><span class="ad-zone-size">' . $ad['width'] . '×' . $ad['height'] . '</span></div>';
    }

    return '<a href="' . $href . '" target="_blank" rel="noopener noreferrer" class="ad-zone neo-inset ' . e($extraClass) . '" style="width:' . $w . ';height:' . $h . 'px;">'
         . $imgHtml
         . '<span class="ad-label">Reklam</span></a>';
}
