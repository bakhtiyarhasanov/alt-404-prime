<?php
/**
 * Helper functions — date formatting, slugify, SEO, data fetchers.
 */

require_once __DIR__ . '/../config/database.php';

// ── Locale & Language Configuration ──────────────────────────────────────────
setlocale(LC_ALL, 'az_AZ.UTF-8', 'az_AZ', 'az', 'aze');
setlocale(LC_NUMERIC, 'C'); // Preserve standard decimal dot notation for SQL and float operations
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}
if (class_exists('Locale')) {
    \Locale::setDefault('az_AZ');
}

// ── Date Formatting ──────────────────────────────────────────────────────────

function formatDateAz(string $dateStr): string
{
    $months = [
        'yanvar',
        'fevral',
        'mart',
        'aprel',
        'may',
        'iyun',
        'iyul',
        'avqust',
        'sentyabr',
        'oktyabr',
        'noyabr',
        'dekabr',
    ];
    $ts = strtotime($dateStr);
    if ($ts === false)
        return '';
    return date('j', $ts) . ' ' . $months[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);
}

// ── Slugify ──────────────────────────────────────────────────────────────────

function slugify(string $text): string
{
    $map = ['ə' => 'e', 'ö' => 'o', 'ü' => 'u', 'ğ' => 'g', 'ı' => 'i', 'ş' => 's', 'ç' => 'c'];
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/\s+/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

// ── Reading time ─────────────────────────────────────────────────────────────

function estimateReadingTime(string $content): int
{
    $plain = strip_tags($content);
    $words = str_word_count($plain);
    return max(1, (int) ceil($words / 200));
}

// ── Data Fetchers ────────────────────────────────────────────────────────────

function getCategories(): array
{
    $db = getDB();
    $stmt = $db->query('SELECT * FROM categories ORDER BY sort_order ASC');
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['show_on_site'] = (bool) $row['show_on_site'];
        $row['curated_tags'] = json_decode($row['curated_tags'] ?? '[]', true) ?: [];
    }
    return $rows;
}

function getVisibleCategories(): array
{
    return array_values(array_filter(getCategories(), fn($c) => $c['show_on_site']));
}

function getCategoryLabel(string $slug): string
{
    $db = getDB();
    $stmt = $db->prepare('SELECT label FROM categories WHERE slug = ?');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    return $row ? $row['label'] : $slug;
}

function isValidCategory(string $slug): bool
{
    $db = getDB();
    $stmt = $db->prepare('SELECT 1 FROM categories WHERE slug = ?');
    $stmt->execute([$slug]);
    return (bool) $stmt->fetch();
}

function getPublishedArticles(int $limit = 100, int $offset = 0): array
{
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
        $row['featured'] = (bool) $row['featured'];
        $row['published'] = (bool) $row['published'];
        $row['updating'] = (bool) ($row['updating'] ?? false);
    }
    return $rows;
}

function getFeaturedArticles(int $limit = 10, int $offset = 0): array
{
    $db = getDB();
    $stmt = $db->prepare(
        'SELECT id, title, slug, excerpt, category, image_url, tags, featured, published, updating, reading_time, views, created_at, updated_at
         FROM articles 
         WHERE published = 1 
           AND featured = 1
           AND (start_time IS NULL OR start_time <= NOW()) 
           AND (end_time IS NULL OR end_time >= NOW()) 
         ORDER BY created_at DESC LIMIT ? OFFSET ?'
    );
    $stmt->execute([$limit, $offset]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['tags'] = json_decode($row['tags'] ?? '[]', true) ?: [];
        $row['featured'] = (bool) $row['featured'];
        $row['published'] = (bool) $row['published'];
        $row['updating'] = (bool) ($row['updating'] ?? false);
    }
    return $rows;
}

function getTotalPublishedArticlesCount(): int
{
    $db = getDB();
    $stmt = $db->query(
        'SELECT COUNT(*) FROM articles 
         WHERE published = 1 
           AND (start_time IS NULL OR start_time <= NOW()) 
           AND (end_time IS NULL OR end_time >= NOW())'
    );
    return (int) $stmt->fetchColumn();
}

/**
 * Retrieve the most used tags from published articles within the last N days (10 max).
 *
 * @param int $days Number of days to look back (default 3)
 * @param int $limit Maximum number of tags to return (default 10)
 * @param bool $fallback If true, falls back to recent published articles when 0 tags exist in the last N days
 * @return array List of top ranked tags formatted with '#' prefix
 */
function getTopTags(int $days = 3, int $limit = 10, bool $fallback = true): array
{
    $db = getDB();
    $days = max(1, (int) $days);
    $limit = max(1, (int) $limit);

    $stmt = $db->prepare('
        SELECT tags 
        FROM articles 
        WHERE published = 1 
          AND (start_time IS NULL OR start_time <= NOW()) 
          AND (end_time IS NULL OR end_time >= NOW()) 
          AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ORDER BY created_at DESC
    ');
    $stmt->execute([$days]);
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $tagCounts = [];
    $tagDisplay = [];

    foreach ($rows as $tagsJson) {
        $tags = is_array($tagsJson) ? $tagsJson : json_decode($tagsJson ?? '[]', true);
        if (!empty($tags) && is_array($tags)) {
            foreach ($tags as $t) {
                $cleaned = trim((string) $t);
                if ($cleaned === '') continue;
                $formatted = str_starts_with($cleaned, '#') ? $cleaned : ('#' . $cleaned);
                $lower = mb_strtolower($formatted, 'UTF-8');
                $tagCounts[$lower] = ($tagCounts[$lower] ?? 0) + 1;
                if (!isset($tagDisplay[$lower]) || ($tagDisplay[$lower] === $lower && $formatted !== $lower)) {
                    $tagDisplay[$lower] = $formatted;
                }
            }
        }
    }

    // Fallback: If no tags were found in the last $days days (e.g. idle database),
    // optionally fall back to the most recent published articles so the header isn't empty.
    if (empty($tagCounts) && $fallback) {
        $stmtFallback = $db->query('
            SELECT tags 
            FROM articles 
            WHERE published = 1 
              AND (start_time IS NULL OR start_time <= NOW()) 
              AND (end_time IS NULL OR end_time >= NOW()) 
            ORDER BY created_at DESC 
            LIMIT 50
        ');
        $fallbackRows = $stmtFallback->fetchAll(PDO::FETCH_COLUMN);
        foreach ($fallbackRows as $tagsJson) {
            $tags = is_array($tagsJson) ? $tagsJson : json_decode($tagsJson ?? '[]', true);
            if (!empty($tags) && is_array($tags)) {
                foreach ($tags as $t) {
                    $cleaned = trim((string) $t);
                    if ($cleaned === '') continue;
                    $formatted = str_starts_with($cleaned, '#') ? $cleaned : ('#' . $cleaned);
                    $lower = mb_strtolower($formatted, 'UTF-8');
                    $tagCounts[$lower] = ($tagCounts[$lower] ?? 0) + 1;
                    if (!isset($tagDisplay[$lower]) || ($tagDisplay[$lower] === $lower && $formatted !== $lower)) {
                        $tagDisplay[$lower] = $formatted;
                    }
                }
            }
        }
    }

    arsort($tagCounts);
    $topRankedTags = [];
    foreach (array_slice(array_keys($tagCounts), 0, $limit) as $lowerKey) {
        $topRankedTags[] = $tagDisplay[$lowerKey] ?? $lowerKey;
    }

    return $topRankedTags;
}

function getArticleBySlug(string $slug): ?array
{
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
    if (!$row)
        return null;
    $row['tags'] = json_decode($row['tags'] ?? '[]', true) ?: [];
    $row['featured'] = (bool) $row['featured'];
    $row['published'] = (bool) $row['published'];
    $row['updating'] = (bool) ($row['updating'] ?? false);
    $row['versions'] = json_decode($row['versions'] ?? '[]', true) ?: [];
    return $row;
}

function getArticlesByCategorySlugs(string $category, int $limit = 20): array
{
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
        $row['featured'] = (bool) $row['featured'];
        $row['published'] = (bool) $row['published'];
        $row['updating'] = (bool) ($row['updating'] ?? false);
    }
    return $rows;
}

function getRelatedArticles(string $category, string $excludeId, int $limit = 3): array
{
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
        $row['featured'] = (bool) $row['featured'];
        $row['updating'] = (bool) ($row['updating'] ?? false);
    }
    return $rows;
}

function searchArticles(string $query, ?string $category = null): array
{
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
        $row['featured'] = (bool) $row['featured'];
        $row['updating'] = (bool) ($row['updating'] ?? false);
    }
    return $rows;
}

function incrementArticleViews(string $id): void
{
    $db = getDB();
    $stmt = $db->prepare('UPDATE articles SET views = views + 1 WHERE id = ?');
    $stmt->execute([$id]);
}

function syncStandardAds(PDO $db): void
{
    static $synced = false;
    if ($synced) return;
    $synced = true;

    try {
        $check = $db->query("SELECT COUNT(*) FROM `ads` WHERE `id` = 'spotlight'")->fetchColumn();
        if ((int)$check === 0) {
            $standardAds = [
                ['spotlight', 'Spotlight Banner (Ana Səhifə 4-cü Kart)', 1, 'https://images.pexels.com/photos/1779487/pexels-photo-1779487.jpeg?auto=compress&cs=tinysrgb&w=600&h=750&fit=crop', '#', 400, 500],
                ['home-promo-2', '2-ci Promo Banner (Ana Səhifə - Bölmələrarası)', 1, 'https://images.pexels.com/photos/2599244/pexels-photo-2599244.jpeg?auto=compress&cs=tinysrgb&w=1200&h=200&fit=crop', '#', 1200, 200],
                ['category-banner', 'Kateqoriya Səhifəsi Banneri', 1, 'https://images.pexels.com/photos/3861969/pexels-photo-3861969.jpeg?auto=compress&cs=tinysrgb&w=1200&h=200&fit=crop', '#', 1200, 200]
            ];
            $stmt = $db->prepare("INSERT IGNORE INTO `ads` (`id`, `label`, `enabled`, `image_url`, `link_url`, `width`, `height`) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($standardAds as $ad) {
                $stmt->execute($ad);
            }
            $db->exec("UPDATE `ads` SET `label` = '1-ci Əsas Banner (Ana Səhifə - Üst)', `width` = 1200, `height` = 200 WHERE `id` = 'leaderboard'");
            $db->exec("UPDATE `ads` SET `label` = 'Məqalə İçi Reklam (Məqalə Səhifəsi)', `width` = 800, `height` = 160 WHERE `id` = 'inline'");
            $db->exec("DELETE FROM `ads` WHERE `id` IN ('sidebar-left', 'sidebar-right')");
        }
    } catch (PDOException $e) {
        // Silently ignore if table not accessible yet
    }
}

function getAds(): array
{
    $db = getDB();
    syncStandardAds($db);
    $stmt = $db->query("
        SELECT * FROM ads 
        ORDER BY FIELD(id, 'spotlight', 'leaderboard', 'home-promo-2', 'inline', 'category-banner', id)
    ");
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['enabled'] = (bool) $row['enabled'];
    }
    return $rows;
}

function getAd(string $id): ?array
{
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM ads WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row)
        return null;
    $row['enabled'] = (bool) $row['enabled'];
    return $row;
}

function extractYouTubeID(string $url): ?string
{
    $clean = trim($url);
    $clean = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $clean);
    $clean = trim($clean, "\"'` \t\n\r\0\x0B");
    if (empty($clean)) {
        return null;
    }
    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $clean)) {
        return $clean;
    }

    // Shorts URLs (matches /shorts/ID, including @channel/shorts/ID, youtu.be/shorts/ID)
    if (preg_match('/\/shorts\/([a-zA-Z0-9_-]{11})/i', $clean, $m)) {
        return $m[1];
    }

    // youtu.be/ID
    if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})/i', $clean, $m)) {
        return $m[1];
    }

    // /embed/ID, /v/ID, /vi/ID, /live/ID
    if (preg_match('/\/(?:embed|v|vi|live)\/([a-zA-Z0-9_-]{11})/i', $clean, $m)) {
        return $m[1];
    }

    // ?v=ID or &v=ID
    if (preg_match('/[?&]v=([a-zA-Z0-9_-]{11})/i', $clean, $m)) {
        return $m[1];
    }

    // Generic youtube domain pattern
    if (preg_match('/(?:youtube(?:-nocookie)?\.com|youtu\.be).*(?:[\/=])([a-zA-Z0-9_-]{11})/i', $clean, $m)) {
        return $m[1];
    }

    return null;
}

function isYouTubeShort(string $url): bool
{
    return stripos($url, 'shorts') !== false || stripos($url, '/oar2.jpg') !== false;
}

function getYouTubeThumbnail(string $url, string $fallback = ''): string
{
    $clean = trim($url);
    $ytId = extractYouTubeID($clean);
    if ($ytId) {
        // If it's a YouTube Short, oar2.jpg is YouTube's native 9:16 vertical poster (1080x1920)
        if (isYouTubeShort($clean)) {
            return "https://img.youtube.com/vi/{$ytId}/oar2.jpg";
        }
        return "https://img.youtube.com/vi/{$ytId}/hqdefault.jpg";
    }
    return $fallback;
}

function getHomeVideos(int $limit = 4): array
{
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

function getProjects(bool $onlyEnabled = true, int $limit = 50): array
{
    $db = getDB();
    $where = $onlyEnabled ? 'WHERE enabled = 1' : '';
    $stmt = $db->prepare("SELECT * FROM projects {$where} ORDER BY sort_order ASC, created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['enabled'] = (bool) $row['enabled'];
        $row['sort_order'] = (int) $row['sort_order'];

        // Automatically derive YouTube thumbnail if image is empty, is a video link, or if shorts needs 9:16 poster
        $needsDbUpdate = false;
        if (empty($row['image']) && !empty($row['youtube_url'])) {
            $row['image'] = getYouTubeThumbnail($row['youtube_url']);
            $needsDbUpdate = true;
        } elseif (!empty($row['image']) && extractYouTubeID($row['image'])) {
            if (strpos($row['image'], 'img.youtube.com') === false) {
                $row['image'] = getYouTubeThumbnail($row['image'] ?: ($row['youtube_url'] ?? ''));
                $needsDbUpdate = true;
            } elseif (isYouTubeShort($row['youtube_url'] ?? '') && strpos($row['image'], 'hqdefault.jpg') !== false) {
                // Upgrade short to native 9:16 vertical poster
                $row['image'] = getYouTubeThumbnail($row['youtube_url']);
                $needsDbUpdate = true;
            }
        }

        if ($needsDbUpdate && !empty($row['id']) && !empty($row['image'])) {
            try {
                $upStmt = $db->prepare('UPDATE projects SET image = ? WHERE id = ?');
                $upStmt->execute([$row['image'], $row['id']]);
            } catch (\Throwable $e) {
            }
        }
    }
    return $rows;
}

function getProject(string $id): ?array
{
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM projects WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row)
        return null;
    $row['enabled'] = (bool) $row['enabled'];
    $row['sort_order'] = (int) $row['sort_order'];

    if (empty($row['image']) && !empty($row['youtube_url'])) {
        $row['image'] = getYouTubeThumbnail($row['youtube_url']);
    } elseif (!empty($row['image']) && extractYouTubeID($row['image'])) {
        if (strpos($row['image'], 'img.youtube.com') === false) {
            $row['image'] = getYouTubeThumbnail($row['image'] ?: ($row['youtube_url'] ?? ''));
        } elseif (isYouTubeShort($row['youtube_url'] ?? '') && strpos($row['image'], 'hqdefault.jpg') !== false) {
            $row['image'] = getYouTubeThumbnail($row['youtube_url']);
        }
    }

    return $row;
}

function getSettingValue(string $key, string $default = ''): string
{
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

function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// ── Azerbaijani string casing helpers ────────────────────────────────────────

/**
 * Azerbaijani uppercase conversion.
 * Correctly converts lowercase 'i' to 'İ' and 'ı' to 'I'.
 */
function mb_strtoupper_az(string $str): string
{
    $str = str_replace(['i', 'ı'], ['İ', 'I'], $str);
    return mb_strtoupper($str, 'UTF-8');
}

// ── Render ad zone HTML ──────────────────────────────────────────────────────

function renderAdZone(string $id, string $extraClass = ''): string
{
    $ad = getAd($id);
    if (!$ad || empty($ad['enabled']) || empty($ad['image_url']))
        return '';

    $href = e($ad['link_url'] ?: '#');
    $imgHtml = '<img src="' . e($ad['image_url']) . '" alt="' . e($ad['label']) . '" class="w-full h-auto max-h-[220px] object-cover block" loading="lazy">';

    return '<aside class="relative block w-full rounded-xl overflow-hidden border border-neutral-200/80 dark:border-[#261545] shadow-2xs hover:shadow-xs hover:border-[#fcdb56]/80 transition-all ' . e($extraClass) . '">'
        . '<a href="' . $href . '" target="_blank" rel="noopener noreferrer" class="block w-full h-auto cursor-pointer relative">'
        . $imgHtml
        . '<span class="ad-label">Reklam</span>'
        . '</a></aside>';
}
