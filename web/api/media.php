<?php
/**
 * CRUD API for media library
 */

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $db = getDB();
    $stmt = $db->query('SELECT * FROM media_library ORDER BY created_at DESC');
    echo json_encode($stmt->fetchAll());
    exit;
}

verifyAuth();

if ($method === 'POST') {
    if (!isset($_FILES['file'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No file uploaded']);
        exit;
    }

    $file = $_FILES['file'];
    $title = $_POST['title'] ?? '';
    $alt_text = $_POST['alt_text'] ?? '';

    // Create uploads folder if missing
    $uploadDir = __DIR__ . '/../uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('media_', true) . '.' . $ext;
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $url = '/uploads/' . $fileName;
        $db = getDB();
        $stmt = $db->prepare('
            INSERT INTO media_library (url, alt_text, title, file_name)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([$url, $alt_text, $title, $file['name']]);
        
        echo json_encode([
            'success' => true,
            'url' => $url,
            'title' => $title,
            'alt_text' => $alt_text,
            'file_name' => $file['name']
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save uploaded file']);
    }
    exit;
}

if ($method === 'DELETE') {
    if (!$resourceId) {
        http_response_code(400);
        echo json_encode(['error' => 'Media ID is required']);
        exit;
    }

    $db = getDB();
    $stmt = $db->prepare('SELECT url FROM media_library WHERE id = ?');
    $stmt->execute([$resourceId]);
    $media = $stmt->fetch();

    if ($media) {
        // Delete actual file
        $filePath = __DIR__ . '/..' . $media['url'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        // Delete from database
        $stmt = $db->prepare('DELETE FROM media_library WHERE id = ?');
        $stmt->execute([$resourceId]);
    }

    echo json_encode(['success' => true]);
    exit;
}
