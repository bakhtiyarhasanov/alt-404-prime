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

if ($method === 'PUT' || $method === 'PATCH') {
    if (!$resourceId) {
        http_response_code(400);
        echo json_encode(['error' => 'Ad ID is required']);
        exit;
    }

    $db = getDB();

    // Check if ad exists
    $stmt = $db->prepare('SELECT * FROM ads WHERE id = ?');
    $stmt->execute([$resourceId]);
    $current = $stmt->fetch();
    if (!$current) {
        http_response_code(404);
        echo json_encode(['error' => 'Ad not found']);
        exit;
    }

    $enabled = isset($input['enabled']) ? (!empty($input['enabled']) ? 1 : 0) : (int)$current['enabled'];
    $image_url = array_key_exists('image_url', $input) ? (string)$input['image_url'] : ($current['image_url'] ?? '');
    $link_url = array_key_exists('link_url', $input) ? (string)$input['link_url'] : ($current['link_url'] ?? '#');
    $label = !empty($input['label']) ? (string)$input['label'] : $current['label'];
    $width = !empty($input['width']) ? (int)$input['width'] : (int)$current['width'];
    $height = !empty($input['height']) ? (int)$input['height'] : (int)$current['height'];

    $stmt = $db->prepare('
        UPDATE ads 
        SET enabled = ?, image_url = ?, link_url = ?, label = ?, width = ?, height = ?
        WHERE id = ?
    ');
    $stmt->execute([$enabled, $image_url, $link_url, $label, $width, $height, $resourceId]);

    echo json_encode([
        'success' => true,
        'ad' => getAd($resourceId)
    ]);
    exit;
}
