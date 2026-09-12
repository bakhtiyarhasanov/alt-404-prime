<?php
/**
 * CRUD API for Projects (Xüsusi Layihələr)
 */

$method = $_SERVER['REQUEST_METHOD'];

// Public / Admin GET
if ($method === 'GET') {
    $all = isset($_GET['all']) && $_GET['all'] == '1';
    echo json_encode(getProjects(!$all, 100), JSON_UNESCAPED_UNICODE);
    exit;
}

// Protected operations
verifyAuth();
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$db = getDB();

if ($method === 'POST') {
    $title = trim($input['title'] ?? '');
    $subtitle = trim($input['subtitle'] ?? '');
    $category = trim($input['category'] ?? 'EKSPERİMENT');
    $image = trim($input['image'] ?? '');
    $duration = trim($input['duration'] ?? '15:00');
    $youtube_url = trim($input['youtube_url'] ?? '');
    $description = trim($input['description'] ?? '');
    $sort_order = (int)($input['sort_order'] ?? 0);
    $enabled = isset($input['enabled']) ? ($input['enabled'] ? 1 : 0) : 1;

    if (!$title || !$youtube_url) {
        http_response_code(400);
        echo json_encode(['error' => 'Layihə başlığı və YouTube keçidi mütləqdir']);
        exit;
    }

    // If image is empty, auto-generate from YouTube
    if (!$image) {
        $image = getYouTubeThumbnail($youtube_url);
    }

    // Generate unique ID
    $id = 'proj-' . bin2hex(random_bytes(6));

    $stmt = $db->prepare('
        INSERT INTO projects (id, title, subtitle, category, image, duration, youtube_url, description, sort_order, enabled)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $id, $title, $subtitle, $category,
        $image, $duration, $youtube_url, $description,
        $sort_order, $enabled
    ]);

    echo json_encode(['success' => true, 'id' => $id]);
    exit;
}

if ($method === 'PUT') {
    if (!$resourceId) {
        http_response_code(400);
        echo json_encode(['error' => 'Layihə ID-si tələb olunur']);
        exit;
    }

    $title = trim($input['title'] ?? '');
    $subtitle = trim($input['subtitle'] ?? '');
    $category = trim($input['category'] ?? 'EKSPERİMENT');
    $image = trim($input['image'] ?? '');
    $duration = trim($input['duration'] ?? '15:00');
    $youtube_url = trim($input['youtube_url'] ?? '');
    $description = trim($input['description'] ?? '');
    $sort_order = (int)($input['sort_order'] ?? 0);
    $enabled = isset($input['enabled']) ? ($input['enabled'] ? 1 : 0) : 1;

    if (!$title || !$youtube_url) {
        http_response_code(400);
        echo json_encode(['error' => 'Layihə başlığı və YouTube keçidi mütləqdir']);
        exit;
    }

    if (!$image) {
        $image = getYouTubeThumbnail($youtube_url);
    }

    $stmt = $db->prepare('
        UPDATE projects 
        SET title = ?, subtitle = ?, category = ?, image = ?, duration = ?, youtube_url = ?, description = ?, sort_order = ?, enabled = ?
        WHERE id = ?
    ');
    $stmt->execute([
        $title, $subtitle, $category,
        $image, $duration, $youtube_url, $description,
        $sort_order, $enabled, $resourceId
    ]);

    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'DELETE') {
    if (!$resourceId) {
        http_response_code(400);
        echo json_encode(['error' => 'Layihə ID-si tələb olunur']);
        exit;
    }

    $stmt = $db->prepare('DELETE FROM projects WHERE id = ?');
    $stmt->execute([$resourceId]);

    echo json_encode(['success' => true]);
    exit;
}
