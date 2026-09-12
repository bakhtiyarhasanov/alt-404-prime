<?php
/**
 * API for newsletter subscribers
 */

verifyAuth();
$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

if ($method === 'GET') {
    try {
        // Ensure table exists just in case no one has subscribed yet
        $db->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $stmt = $db->query('SELECT * FROM newsletter_subscribers ORDER BY created_at DESC');
        echo json_encode($stmt->fetchAll());
    } catch (Exception $e) {
        echo json_encode([]);
    }
    exit;
}

if ($method === 'DELETE') {
    if (!$resourceId) {
        http_response_code(400);
        echo json_encode(['error' => 'Subscriber ID is required']);
        exit;
    }

    try {
        $stmt = $db->prepare('DELETE FROM newsletter_subscribers WHERE id = ?');
        $stmt->execute([$resourceId]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete']);
    }
    exit;
}
