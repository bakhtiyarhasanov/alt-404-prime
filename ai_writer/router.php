<?php
/**
 * Router script for PHP Built-in Server
 * Usage:
 *   php -S localhost:8000 router.php          (inside ai_writer/)
 *   php -S localhost:8000 ai_writer/router.php (inside root)
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static files if they exist
$filePath = __DIR__ . $uri;
if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false;
}

// Route any API request to api/index.php
if (str_contains($uri, '/api') || isset($_GET['endpoint'])) {
    require __DIR__ . '/api/index.php';
    exit;
}

// Route manager dist static files or fallback
if (file_exists(__DIR__ . '/manager/dist/index.html')) {
    require __DIR__ . '/index.php';
    exit;
}

// Default response
header('Content-Type: application/json');
echo json_encode(['status' => 'AI Writer Core Server Running', 'time' => date('c')]);
exit;
