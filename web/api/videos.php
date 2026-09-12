<?php
/**
 * CRUD API for home videos
 */

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode(getHomeVideos(50));
    exit;
}

verifyAuth();
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$db = getDB();

if ($method === 'POST') {
    $title = $input['title'] ?? '';
    $youtube_url = $input['youtube_url'] ?? '';
    $thumbnail_url = $input['thumbnail_url'] ?? '';
    $sort_order = (int)($input['sort_order'] ?? 0);

    if (!$title || !$youtube_url) {
        http_response_code(400);
        echo json_encode(['error' => 'Title and YouTube URL are required']);
        exit;
    }

    if (!$thumbnail_url) {
        $thumbnail_url = getYouTubeThumbnail($youtube_url);
    }

    $stmt = $db->prepare('
        INSERT INTO home_videos (title, youtube_url, thumbnail_url, sort_order)
        VALUES (?, ?, ?, ?)
    ');
    $stmt->execute([$title, $youtube_url, $thumbnail_url, $sort_order]);

    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'PUT') {
    if (!$resourceId) {
        http_response_code(400);
        echo json_encode(['error' => 'Video ID is required']);
        exit;
    }

    $title = $input['title'] ?? '';
    $youtube_url = $input['youtube_url'] ?? '';
    $thumbnail_url = $input['thumbnail_url'] ?? '';
    $sort_order = (int)($input['sort_order'] ?? 0);

    if (!$thumbnail_url && $youtube_url) {
        $thumbnail_url = getYouTubeThumbnail($youtube_url);
    }

    $stmt = $db->prepare('
        UPDATE home_videos 
        SET title = ?, youtube_url = ?, thumbnail_url = ?, sort_order = ?
        WHERE id = ?
    ');
    $stmt->execute([$title, $youtube_url, $thumbnail_url, $sort_order, $resourceId]);

    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'DELETE') {
    if (!$resourceId) {
        http_response_code(400);
        echo json_encode(['error' => 'Video ID is required']);
        exit;
    }

    $stmt = $db->prepare('DELETE FROM home_videos WHERE id = ?');
    $stmt->execute([$resourceId]);

    echo json_encode(['success' => true]);
    exit;
}
