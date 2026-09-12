<?php
/**
 * CRUD API for articles
 */

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $db = getDB();
    if ($resourceId) {
        // Fetch article with creator name
        $stmt = $db->prepare('
            SELECT a.*, u.name as creator_name 
            FROM articles a 
            LEFT JOIN admin_users u ON a.created_by = u.id 
            WHERE a.id = :id OR a.slug = :slug
        ');
        $stmt->execute(['id' => $resourceId, 'slug' => $resourceId]);
        $article = $stmt->fetch();
        if ($article) {
            $article['tags'] = json_decode($article['tags'] ?? '[]', true) ?: [];
            $article['featured'] = (bool) $article['featured'];
            $article['published'] = (bool) $article['published'];
            $article['updating'] = (bool) ($article['updating'] ?? false);
            $article['newsletter'] = (bool) ($article['newsletter'] ?? false);
            $article['versions'] = json_decode($article['versions'] ?? '[]', true) ?: [];

            // Fetch edit history
            $stmtHist = $db->prepare('
                SELECT h.id, h.changes, h.created_at, u.name as user_name 
                FROM news_edit_history h 
                JOIN admin_users u ON h.user_id = u.id 
                WHERE h.news_id = :news_id 
                ORDER BY h.created_at DESC
            ');
            $stmtHist->execute(['news_id' => $article['id']]);
            $history = $stmtHist->fetchAll();
            foreach ($history as &$hist) {
                $hist['changes'] = json_decode($hist['changes'] ?? '{}', true);
            }
            $article['history'] = $history;
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Article not found']);
            exit;
        }
        echo json_encode($article);
    } else {
        // Return list with creator details
        $stmt = $db->query('
            SELECT a.id, a.title, a.slug, a.excerpt, a.category, a.image_url, a.tags, a.featured, a.published, a.updating, a.newsletter, a.code, a.reading_time, a.start_time, a.end_time, a.views, a.created_at, a.updated_at, a.created_by, u.name as creator_name, JSON_LENGTH(a.versions) as version_count
            FROM articles a
            LEFT JOIN admin_users u ON a.created_by = u.id
            ORDER BY a.created_at DESC
        ');
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['tags'] = json_decode($row['tags'] ?? '[]', true) ?: [];
            $row['featured'] = (bool) $row['featured'];
            $row['published'] = (bool) $row['published'];
            $row['updating'] = (bool) ($row['updating'] ?? false);
            $row['newsletter'] = (bool) ($row['newsletter'] ?? false);
        }
        echo json_encode($rows);
    }
    exit;
}

// Write endpoints require authentication
$currentUser = verifyAuth();

$input = json_decode(file_get_contents('php://input'), true) ?? [];

if ($method === 'POST') {
    $title = $input['title'] ?? '';
    $slug = trim($input['slug'] ?? '');
    $excerpt = $input['excerpt'] ?? '';
    $content = $input['content'] ?? '';
    $category = $input['category'] ?? 'texnologiya';
    $image_url = $input['image_url'] ?? '';
    $tags = $input['tags'] ?? [];
    $featured = !empty($input['featured']) ? 1 : 0;
    $published = isset($input['published']) ? (!empty($input['published']) ? 1 : 0) : 1;
    $updating = !empty($input['updating']) ? 1 : 0;
    $newsletter = !empty($input['newsletter']) ? 1 : 0;
    $code = $input['code'] ?? '';
    $start_time = !empty($input['start_time']) ? $input['start_time'] : null;
    $end_time = !empty($input['end_time']) ? $input['end_time'] : null;

    // Role checks
    if (($currentUser['role'] ?? '') === 'reporter') {
        $published = 0; // Reporter cannot publish directly
    }

    $reading_time = estimateReadingTime($content);

    if (!$title) {
        http_response_code(400);
        echo json_encode(['error' => 'Title is required']);
        exit;
    }

    if (!$slug) {
        $slug = slugify($title);
    }

    $db = getDB();

    // Auto-resolve duplicate slug
    $baseSlug = $slug;
    $i = 1;
    while (true) {
        $stmt = $db->prepare('SELECT 1 FROM articles WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);
        if (!$stmt->fetch())
            break;
        $slug = $baseSlug . '-' . $i++;
    }

    // Save initial version
    $versions = json_encode([
        [
            'version' => 1,
            'title' => $title,
            'content' => $content,
            'created_at' => date('c'),
            'author' => $currentUser['name']
        ]
    ]);

    $stmt = $db->prepare('
        INSERT INTO articles (title, slug, excerpt, content, category, image_url, tags, featured, published, updating, newsletter, code, reading_time, start_time, end_time, versions, created_by)
        VALUES (:title, :slug, :excerpt, :content, :category, :image_url, :tags, :featured, :published, :updating, :newsletter, :code, :reading_time, :start_time, :end_time, :versions, :created_by)
    ');
    $stmt->execute([
        'title' => $title,
        'slug' => $slug,
        'excerpt' => $excerpt,
        'content' => $content,
        'category' => $category,
        'image_url' => $image_url,
        'tags' => json_encode($tags),
        'featured' => $featured,
        'published' => $published,
        'updating' => $updating,
        'newsletter' => $newsletter,
        'code' => $code,
        'reading_time' => $reading_time,
        'start_time' => $start_time,
        'end_time' => $end_time,
        'versions' => $versions,
        'created_by' => $currentUser['user_id']
    ]);

    $newId = $db->lastInsertId();
    echo json_encode(['success' => true, 'id' => $newId, 'slug' => $slug]);
    exit;
}

if ($method === 'PUT') {
    if (!$resourceId) {
        http_response_code(400);
        echo json_encode(['error' => 'Article ID or slug is required for updates']);
        exit;
    }

    $db = getDB();
    // Resolve article first
    $stmt = $db->prepare('SELECT * FROM articles WHERE id = :id OR slug = :slug');
    $stmt->execute(['id' => $resourceId, 'slug' => $resourceId]);
    $article = $stmt->fetch();

    if (!$article) {
        http_response_code(404);
        echo json_encode(['error' => 'Article not found']);
        exit;
    }

    // Reporter restriction: cannot edit published news
    if (($currentUser['role'] ?? '') === 'reporter' && (int) $article['published'] === 1) {
        http_response_code(403);
        echo json_encode(['error' => 'Giriş qadağandır. Müxbirlər dərc olunmuş xəbərləri redaktə edə bilməz.']);
        exit;
    }

    $title = $input['title'] ?? $article['title'];
    $slug = trim($input['slug'] ?? $article['slug']);
    $excerpt = $input['excerpt'] ?? $article['excerpt'];
    $content = $input['content'] ?? $article['content'];
    $category = $input['category'] ?? $article['category'];
    $image_url = $input['image_url'] ?? $article['image_url'];
    $tags = isset($input['tags']) ? $input['tags'] : json_decode($article['tags'], true);
    $featured = isset($input['featured']) ? (!empty($input['featured']) ? 1 : 0) : $article['featured'];
    $published = isset($input['published']) ? (!empty($input['published']) ? 1 : 0) : $article['published'];
    $updating = isset($input['updating']) ? (!empty($input['updating']) ? 1 : 0) : $article['updating'];
    $newsletter = isset($input['newsletter']) ? (!empty($input['newsletter']) ? 1 : 0) : $article['newsletter'];
    $code = array_key_exists('code', $input) ? $input['code'] : $article['code'];
    $start_time = array_key_exists('start_time', $input) ? (!empty($input['start_time']) ? $input['start_time'] : null) : $article['start_time'];
    $end_time = array_key_exists('end_time', $input) ? (!empty($input['end_time']) ? $input['end_time'] : null) : $article['end_time'];

    // Reporter cannot publish or keep it published when editing
    if (($currentUser['role'] ?? '') === 'reporter') {
        $published = 0;
    }

    $reading_time = estimateReadingTime($content);

    // Compute edit history changes
    $changes = [];
    $fieldsToCompare = [
        'title' => 'string',
        'excerpt' => 'string',
        'content' => 'string',
        'category' => 'string',
        'image_url' => 'string',
        'featured' => 'int',
        'published' => 'int',
        'updating' => 'int',
        'newsletter' => 'int',
        'code' => 'string',
        'start_time' => 'string',
        'end_time' => 'string'
    ];

    foreach ($fieldsToCompare as $field => $type) {
        $oldVal = $article[$field];
        $newVal = null;
        if ($field === 'title') {
            $newVal = $title;
        } elseif ($field === 'excerpt') {
            $newVal = $excerpt;
        } elseif ($field === 'content') {
            $newVal = $content;
        } elseif ($field === 'category') {
            $newVal = $category;
        } elseif ($field === 'image_url') {
            $newVal = $image_url;
        } elseif ($field === 'featured') {
            $newVal = $featured;
        } elseif ($field === 'published') {
            $newVal = $published;
        } elseif ($field === 'updating') {
            $newVal = $updating;
        } elseif ($field === 'newsletter') {
            $newVal = $newsletter;
        } elseif ($field === 'code') {
            $newVal = $code;
        } elseif ($field === 'start_time') {
            $newVal = $start_time;
        } elseif ($field === 'end_time') {
            $newVal = $end_time;
        }

        if ($type === 'int') {
            if ((int) $oldVal !== (int) $newVal) {
                $changes[$field] = ['old' => (int) $oldVal, 'new' => (int) $newVal];
            }
        } else {
            if ($oldVal !== $newVal) {
                $changes[$field] = ['old' => $oldVal, 'new' => $newVal];
            }
        }
    }

    // Compare tags
    $oldTags = json_decode($article['tags'] ?? '[]', true) ?: [];
    $newTags = $tags;
    sort($oldTags);
    sort($newTags);
    if ($oldTags !== $newTags) {
        $changes['tags'] = ['old' => json_decode($article['tags'] ?? '[]', true), 'new' => $tags];
    }

    if (!empty($changes)) {
        $historyId = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
        $stmtHist = $db->prepare('
            INSERT INTO news_edit_history (id, user_id, news_id, changes)
            VALUES (:id, :user_id, :news_id, :changes)
        ');
        $stmtHist->execute([
            'id' => $historyId,
            'user_id' => $currentUser['user_id'],
            'news_id' => $article['id'],
            'changes' => json_encode($changes)
        ]);
    }

    // Save article revision history/versioning
    $versionsList = json_decode($article['versions'] ?? '[]', true) ?: [];
    if ($content !== $article['content'] || $title !== $article['title']) {
        $nextVersion = count($versionsList) + 1;
        $versionsList[] = [
            'version' => $nextVersion,
            'title' => $title,
            'content' => $content,
            'created_at' => date('c'),
            'author' => $currentUser['name']
        ];
        // Cap version list to last 15 revisions to prevent overflow
        if (count($versionsList) > 15) {
            array_shift($versionsList);
        }
    }

    // Handle slug change / unique check
    if ($slug !== $article['slug']) {
        $baseSlug = $slug;
        $i = 1;
        while (true) {
            $stmt = $db->prepare('SELECT 1 FROM articles WHERE slug = :slug AND id != :id');
            $stmt->execute(['slug' => $slug, 'id' => $article['id']]);
            if (!$stmt->fetch())
                break;
            $slug = $baseSlug . '-' . $i++;
        }
    }

    $stmt = $db->prepare('
        UPDATE articles 
        SET title = :title, slug = :slug, excerpt = :excerpt, content = :content, category = :category, image_url = :image_url, tags = :tags, featured = :featured, published = :published, updating = :updating, newsletter = :newsletter, code = :code, reading_time = :reading_time, start_time = :start_time, end_time = :end_time, versions = :versions
        WHERE id = :id
    ');
    $stmt->execute([
        'title' => $title,
        'slug' => $slug,
        'excerpt' => $excerpt,
        'content' => $content,
        'category' => $category,
        'image_url' => $image_url,
        'tags' => json_encode($tags),
        'featured' => $featured,
        'published' => $published,
        'updating' => $updating,
        'newsletter' => $newsletter,
        'code' => $code,
        'reading_time' => $reading_time,
        'start_time' => $start_time,
        'end_time' => $end_time,
        'versions' => json_encode($versionsList),
        'id' => $article['id']
    ]);

    echo json_encode(['success' => true, 'slug' => $slug]);
    exit;
}

if ($method === 'DELETE') {
    if (!$resourceId) {
        http_response_code(400);
        echo json_encode(['error' => 'Article ID is required']);
        exit;
    }

    $db = getDB();
    $stmt = $db->prepare('DELETE FROM articles WHERE id = :id OR slug = :slug');
    $stmt->execute(['id' => $resourceId, 'slug' => $resourceId]);

    echo json_encode(['success' => true]);
    exit;
}
