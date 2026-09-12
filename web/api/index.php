<?php
/**
 * alt404 Prime — API Front Controller
 * Handles CORS and routes to matching API scripts.
 */

require_once __DIR__ . '/../config/database.php';

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../helpers/functions.php';

// Parse routing segments
$apiUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$apiUri = rtrim($apiUri, '/');
$segments = explode('/', trim($apiUri, '/')); // e.g. ['api', 'articles', '123']

// Shift 'api'
if (isset($segments[0]) && $segments[0] === 'api') {
    array_shift($segments);
}

$resource = $segments[0] ?? '';
$resourceId = $segments[1] ?? null;

// Auth helper
function getBearerToken(): ?string
{
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        return $matches[1];
    }
    return null;
}

function verifyAuth(): array
{
    $token = getBearerToken();
    if (!$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized: Missing token']);
        exit;
    }

    $db = getDB();
    $tokenHash = hash('sha256', $token);

    // Check if token exists and is valid
    $stmt = $db->prepare('
        SELECT t.*, u.email, u.name, u.avatar_url, u.role 
        FROM auth_tokens t 
        JOIN admin_users u ON t.user_id = u.id 
        WHERE t.token_hash = ? AND t.expires_at > NOW()
    ');
    $stmt->execute([$tokenHash]);
    $session = $stmt->fetch();

    if (!$session) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized: Invalid or expired token']);
        exit;
    }

    return $session;
}

// Routes mapping
switch ($resource) {
    case 'auth':
        require_once __DIR__ . '/auth.php';
        break;
    case 'users':
        require_once __DIR__ . '/users.php';
        break;
    case 'articles':
        require_once __DIR__ . '/articles.php';
        break;
    case 'categories':
        require_once __DIR__ . '/categories.php';
        break;
    case 'ads':
        require_once __DIR__ . '/ads.php';
        break;
    case 'media':
        require_once __DIR__ . '/media.php';
        break;
    case 'contacts':
        require_once __DIR__ . '/contacts.php';
        break;
    case 'videos':
        require_once __DIR__ . '/videos.php';
        break;
    case 'settings':
        require_once __DIR__ . '/settings.php';
        break;
    case 'search':
        require_once __DIR__ . '/search.php';
        break;
    case 'projects':
        require_once __DIR__ . '/projects.php';
        break;
    case 'subscribers':
        require_once __DIR__ . '/subscribers.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'API endpoint not found']);
        break;
}
