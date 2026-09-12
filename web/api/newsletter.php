<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$email = trim($input['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Düzgün e-poçt ünvanı daxil edin.']);
    exit;
}

try {
    $db = getDB();
    
    // Ensure table exists
    $db->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Insert ignoring duplicates
    $stmt = $db->prepare("INSERT IGNORE INTO newsletter_subscribers (email) VALUES (?)");
    $stmt->execute([$email]);

    echo json_encode(['success' => true, 'message' => 'Bülletenimizə abunə olduğunuz üçün təşəkkür edirik!']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Xəta baş verdi. Zəhmət olmasa, daha sonra yenidən cəhd edin.']);
}
