<?php
/**
 * CRUD API for settings
 */

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $db = getDB();
    $stmt = $db->query('SELECT * FROM settings ORDER BY group_name, `key`');
    echo json_encode($stmt->fetchAll());
    exit;
}

verifyAuth();

if ($method === 'PUT') {
    if (!$resourceId) {
        http_response_code(400);
        echo json_encode(['error' => 'Setting key is required']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $value = $input['value'] ?? null;

    $db = getDB();
    $stmt = $db->prepare('UPDATE settings SET value = ? WHERE `key` = ?');
    $stmt->execute([$value, $resourceId]);

    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'POST') {
    if (!$resourceId) {
        http_response_code(400);
        echo json_encode(['error' => 'Setting key is required']);
        exit;
    }

    $db = getDB();
    $value = null;
    
    // Check if file upload is present
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        $uploadDir = __DIR__ . '/../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('setting_', true) . '.' . $ext;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $value = '/uploads/' . $fileName;
            
            // Log in media library for cataloging
            $stmt = $db->prepare('
                INSERT INTO media_library (url, alt_text, title, file_name)
                VALUES (?, ?, ?, ?)
            ');
            $stmt->execute([$value, $file['name'], $file['name'], $file['name']]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save uploaded file']);
            exit;
        }
    } else {
        $value = $_POST['value'] ?? null;
    }

    $stmt = $db->prepare('UPDATE settings SET value = ? WHERE `key` = ?');
    $stmt->execute([$value, $resourceId]);

    echo json_encode(['success' => true, 'value' => $value]);
    exit;
}
