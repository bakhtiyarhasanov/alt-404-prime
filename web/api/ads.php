<?php
/**
 * CRUD API for ads
 */

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode(getAds());
    exit;
}

verifyAuth();
$input = json_decode(file_get_contents('php://input'), true) ?? [];

if ($method === 'PUT') {
    if (!$resourceId) {
        http_response_code(400);
        echo json_encode(['error' => 'Ad ID is required']);
        exit;
    }

    $db = getDB();
    $enabled = !empty($input['enabled']) ? 1 : 0;
    $image_url = $input['image_url'] ?? '';
    $link_url = $input['link_url'] ?? '#';

    $stmt = $db->prepare('
        UPDATE ads 
        SET enabled = ?, image_url = ?, link_url = ?
        WHERE id = ?
    ');
    $stmt->execute([$enabled, $image_url, $link_url, $resourceId]);

    echo json_encode(['success' => true]);
    exit;
}
