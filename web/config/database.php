<?php
/**
 * Database configuration — loads .env and provides PDO singleton.
 */

// Parse .env file
function loadEnv(string $path): void {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Load .env from web root
loadEnv(__DIR__ . '/../.env');

function env(string $key, string $default = ''): string {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

/**
 * Get PDO instance (singleton).
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $host = env('DB_HOST', 'localhost');
        $port = env('DB_PORT', '3306');
        $name = env('DB_NAME', 'alt404');
        $user = env('DB_USER', 'root');
        $pass = env('DB_PASS', '');

        $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            // Check if API request
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (strpos($uri, '/api/') === 0) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Disconnected',
                    'message' => 'Bağlantı kəsildi.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Normal HTML view
            http_response_code(503);
            require_once __DIR__ . '/../pages/disconnected.php';
            exit;
        }
    }
    return $pdo;
}

