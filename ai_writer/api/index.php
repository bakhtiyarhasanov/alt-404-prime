<?php
/**
 * AI Writer REST API Front Controller
 * Serves the Vue.js Manager UI
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/GrabberInterface.php';
require_once __DIR__ . '/../core/BaseGrabber.php';
require_once __DIR__ . '/../core/DuplicateDetector.php';
require_once __DIR__ . '/../core/AiRewriter.php';
require_once __DIR__ . '/../core/Publisher.php';

foreach (glob(__DIR__ . '/../core/grabbers/*.php') as $grabberFile) {
    require_once $grabberFile;
}

require_once __DIR__ . '/../core/GrabberManager.php';

use AiWriter\Core\Database;
use AiWriter\Core\GrabberManager;

// Parse request path
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Helper response functions
function jsonOut($data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonErr(string $message, int $status = 400): void {
    http_response_code($status);
    echo json_encode(['error' => $message, 'success' => false], JSON_UNESCAPED_UNICODE);
    exit;
}

function getBearerToken(): ?string {
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER['Authorization']);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    if (!empty($headers) && preg_match('/Bearer\s(\S+)/i', $headers, $matches)) {
        return $matches[1];
    }
    return null;
}

function verifyAuth(PDO $db): array {
    $token = getBearerToken();
    if (!$token) {
        jsonErr('Giriş tələb olunur', 401);
    }

    $tokenHash = hash('sha256', $token);
    $stmt = $db->prepare('
        SELECT u.id, u.username, u.name, u.created_at
        FROM auth_tokens t
        JOIN users u ON t.user_id = u.id
        WHERE t.token_hash = :hash AND t.expires_at > NOW()
        LIMIT 1
    ');
    $stmt->execute(['hash' => $tokenHash]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonErr('Sessiyanın vaxtı bitib və ya token yanlışdır', 401);
    }

    return $user;
}

try {
    $db = Database::getAiDB();
    $manager = new GrabberManager();

    // Routing parameter or path check
    $endpoint = $_GET['endpoint'] ?? '';
    $action = $_GET['action'] ?? $input['action'] ?? '';
    if (empty($endpoint)) {
        // Extract from path e.g. /api/auth/login or /api/sources
        if (preg_match('#/api/([a-zA-Z0-9_\-]+)(?:/([a-zA-Z0-9_\-]+))?#', $uri, $matches)) {
            $endpoint = $matches[1];
            if (empty($action) && isset($matches[2])) {
                $action = $matches[2];
            }
        }
    }

    // ------------------------------------------------------------------------
    // 0. AUTH ENDPOINT (Login, Verify, Logout)
    // ------------------------------------------------------------------------
    if ($endpoint === 'auth') {
        if ($action === 'login') {
            $username = trim($input['username'] ?? '');
            $password = (string)($input['password'] ?? '');

            if (empty($username) || empty($password)) {
                jsonErr('İstifadəçi adı və şifrə daxil edilməlidir', 400);
            }

            $stmt = $db->prepare("SELECT * FROM users WHERE username = :u LIMIT 1");
            $stmt->execute(['u' => $username]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                jsonErr('İstifadəçi adı və ya şifrə yanlışdır', 401);
            }

            $sessionToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $sessionToken);
            $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

            $ins = $db->prepare("INSERT INTO auth_tokens (user_id, token_hash, expires_at) VALUES (:uid, :thash, :exp)");
            $ins->execute([
                'uid' => $user['id'],
                'thash' => $tokenHash,
                'exp' => $expires
            ]);

            jsonOut([
                'success' => true,
                'token' => $sessionToken,
                'user' => [
                    'id' => (int)$user['id'],
                    'username' => $user['username'],
                    'name' => $user['name'] ?: $user['username']
                ]
            ]);
        }

        if ($action === 'verify') {
            $currentUser = verifyAuth($db);
            jsonOut([
                'success' => true,
                'user' => [
                    'id' => (int)$currentUser['id'],
                    'username' => $currentUser['username'],
                    'name' => $currentUser['name'] ?: $currentUser['username'],
                    'created_at' => $currentUser['created_at']
                ]
            ]);
        }

        if ($action === 'logout') {
            $token = getBearerToken();
            if ($token) {
                $tokenHash = hash('sha256', $token);
                $del = $db->prepare("DELETE FROM auth_tokens WHERE token_hash = :hash");
                $del->execute(['hash' => $tokenHash]);
            }
            jsonOut(['success' => true, 'message' => 'Uğurla çıxış edildi']);
        }

        jsonErr("Bilinməyən auth əməliyyatı: {$action}", 400);
    }

    // Require authentication for all subsequent endpoints
    $currentUser = verifyAuth($db);

    // ------------------------------------------------------------------------
    // USER MANAGEMENT ENDPOINT (Admin created, single level)
    // ------------------------------------------------------------------------
    if ($endpoint === 'users') {
        if ($method === 'GET') {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id > 0) {
                $stmt = $db->prepare("SELECT id, username, name, created_at, updated_at FROM users WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $u = $stmt->fetch();
                if (!$u) jsonErr('İstifadəçi tapılmadı', 404);
                $u['id'] = (int)$u['id'];
                jsonOut(['success' => true, 'user' => $u]);
            }

            $stmt = $db->query("SELECT id, username, name, created_at, updated_at FROM users ORDER BY id ASC");
            $users = $stmt->fetchAll();
            foreach ($users as &$u) {
                $u['id'] = (int)$u['id'];
            }
            jsonOut(['success' => true, 'users' => $users]);
        }

        if ($method === 'POST') {
            if ($action === 'update') {
                $id = (int)($_GET['id'] ?? $input['id'] ?? 0);
                if (!$id) jsonErr('İstifadəçi ID-si göstərilməlidir', 400);

                $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $targetUser = $stmt->fetch();
                if (!$targetUser) jsonErr('İstifadəçi tapılmadı', 404);

                $username = trim($input['username'] ?? $targetUser['username']);
                $name = trim($input['name'] ?? $targetUser['name']);
                $password = (string)($input['password'] ?? '');

                if ($username !== $targetUser['username']) {
                    if (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_\.\-]+$/', $username)) {
                        jsonErr('İstifadəçi adı ən azı 3 simvol olmalı və yalnız hərf/rəqəm içərməlidir');
                    }
                    $check = $db->prepare("SELECT id FROM users WHERE username = :u AND id != :id");
                    $check->execute(['u' => $username, 'id' => $id]);
                    if ($check->fetch()) {
                        jsonErr('Bu istifadəçi adı artıq başqa istifadəçi tərəfindən istifadə olunur');
                    }
                }

                if (!empty($password)) {
                    if (strlen($password) < 4) {
                        jsonErr('Yeni şifrə ən azı 4 simvol olmalıdır');
                    }
                    $passHash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $db->prepare("UPDATE users SET username = :u, name = :n, password = :p WHERE id = :id");
                    $upd->execute(['u' => $username, 'n' => $name, 'p' => $passHash, 'id' => $id]);
                } else {
                    $upd = $db->prepare("UPDATE users SET username = :u, name = :n WHERE id = :id");
                    $upd->execute(['u' => $username, 'n' => $name, 'id' => $id]);
                }

                jsonOut(['success' => true, 'message' => 'İstifadəçi məlumatları uğurla yeniləndi']);
            }

            if ($action === 'delete') {
                $id = (int)($_GET['id'] ?? $input['id'] ?? 0);
                if (!$id) jsonErr('İstifadəçi ID-si göstərilməlidir', 400);
                if ($id === (int)$currentUser['id']) {
                    jsonErr('Öz hesabınızı silə bilməzsiniz', 400);
                }

                $total = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
                if ($total <= 1) {
                    jsonErr('Sistemdə ən azı bir istifadəçi qalmalıdır', 400);
                }

                $del = $db->prepare("DELETE FROM users WHERE id = :id");
                $del->execute(['id' => $id]);
                jsonOut(['success' => true, 'message' => 'İstifadəçi uğurla silindi']);
            }

            // Create new user (admin-only, no public registration)
            $username = trim($input['username'] ?? '');
            $name = trim($input['name'] ?? '');
            $password = (string)($input['password'] ?? '');

            if (empty($username) || strlen($username) < 3) {
                jsonErr('İstifadəçi adı ən azı 3 simvol olmalıdır');
            }
            if (!preg_match('/^[a-zA-Z0-9_\.\-]+$/', $username)) {
                jsonErr('İstifadəçi adı yalnız latın hərfləri, rəqəmlər, altxətt və nöqtə ola bilər');
            }
            if (empty($password) || strlen($password) < 4) {
                jsonErr('Şifrə ən azı 4 simvol olmalıdır');
            }

            $check = $db->prepare("SELECT id FROM users WHERE username = :u");
            $check->execute(['u' => $username]);
            if ($check->fetch()) {
                jsonErr('Bu istifadəçi adı artıq mövcuddur');
            }

            $passHash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $db->prepare("INSERT INTO users (username, name, password) VALUES (:u, :n, :p)");
            $ins->execute([
                'u' => $username,
                'n' => $name ?: $username,
                'p' => $passHash
            ]);

            jsonOut([
                'success' => true,
                'id' => (int)$db->lastInsertId(),
                'message' => 'Yeni istifadəçi uğurla yaradıldı'
            ]);
        }

        if ($method === 'PUT') {
            $id = (int)($_GET['id'] ?? $input['id'] ?? 0);
            if (!$id) jsonErr('İstifadəçi ID-si göstərilməlidir', 400);

            $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $targetUser = $stmt->fetch();
            if (!$targetUser) jsonErr('İstifadəçi tapılmadı', 404);

            $username = trim($input['username'] ?? $targetUser['username']);
            $name = trim($input['name'] ?? $targetUser['name']);
            $password = (string)($input['password'] ?? '');

            if ($username !== $targetUser['username']) {
                if (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_\.\-]+$/', $username)) {
                    jsonErr('İstifadəçi adı ən azı 3 simvol olmalı və yalnız hərf/rəqəm içərməlidir');
                }
                $check = $db->prepare("SELECT id FROM users WHERE username = :u AND id != :id");
                $check->execute(['u' => $username, 'id' => $id]);
                if ($check->fetch()) {
                    jsonErr('Bu istifadəçi adı artıq başqa istifadəçi tərəfindən istifadə olunur');
                }
            }

            if (!empty($password)) {
                if (strlen($password) < 4) {
                    jsonErr('Yeni şifrə ən azı 4 simvol olmalıdır');
                }
                $passHash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $db->prepare("UPDATE users SET username = :u, name = :n, password = :p WHERE id = :id");
                $upd->execute(['u' => $username, 'n' => $name, 'p' => $passHash, 'id' => $id]);
            } else {
                $upd = $db->prepare("UPDATE users SET username = :u, name = :n WHERE id = :id");
                $upd->execute(['u' => $username, 'n' => $name, 'id' => $id]);
            }

            jsonOut(['success' => true, 'message' => 'İstifadəçi məlumatları uğurla yeniləndi']);
        }

        if ($method === 'DELETE') {
            $id = (int)($_GET['id'] ?? $input['id'] ?? 0);
            if (!$id) jsonErr('İstifadəçi ID-si göstərilməlidir', 400);
            if ($id === (int)$currentUser['id']) {
                jsonErr('Öz hesabınızı silə bilməzsiniz', 400);
            }

            $total = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
            if ($total <= 1) {
                jsonErr('Sistemdə ən azı bir istifadəçi qalmalıdır', 400);
            }

            $del = $db->prepare("DELETE FROM users WHERE id = :id");
            $del->execute(['id' => $id]);
            jsonOut(['success' => true, 'message' => 'İstifadəçi uğurla silindi']);
        }
    }

    // ------------------------------------------------------------------------
    // 1. STATS ENDPOINT
    // ------------------------------------------------------------------------
    if ($endpoint === 'stats') {
        $stats = [
            'total' => (int)$db->query("SELECT COUNT(*) FROM grabbed_news")->fetchColumn(),
            'new' => (int)$db->query("SELECT COUNT(*) FROM grabbed_news WHERE status = 'new'")->fetchColumn(),
            'posted' => (int)$db->query("SELECT COUNT(*) FROM grabbed_news WHERE status = 'posted'")->fetchColumn(),
            'duplicate' => (int)$db->query("SELECT COUNT(*) FROM grabbed_news WHERE status = 'duplicate'")->fetchColumn(),
            'error' => (int)$db->query("SELECT COUNT(*) FROM grabbed_news WHERE status = 'error'")->fetchColumn(),
            'generating' => (int)$db->query("SELECT COUNT(*) FROM grabbed_news WHERE status = 'generating'")->fetchColumn(),
            'sources_total' => (int)$db->query("SELECT COUNT(*) FROM sources")->fetchColumn(),
            'sources_active' => (int)$db->query("SELECT COUNT(*) FROM sources WHERE is_enabled = 1")->fetchColumn(),
            'sources_rewrite' => (int)$db->query("SELECT COUNT(*) FROM sources WHERE rewrite_enabled = 1")->fetchColumn(),
        ];
        jsonOut(['success' => true, 'stats' => $stats]);
    }

    // ------------------------------------------------------------------------
    // 2. SOURCES ENDPOINT
    // ------------------------------------------------------------------------
    if ($endpoint === 'sources') {
        if ($method === 'GET') {
            $stmt = $db->query("
                SELECT s.*, 
                    (SELECT COUNT(*) FROM grabbed_news WHERE source_id = s.id) as total_news,
                    (SELECT COUNT(*) FROM grabbed_news WHERE source_id = s.id AND status = 'posted') as posted_news
                FROM sources s 
                ORDER BY s.is_enabled DESC, s.name ASC
            ");
            $sources = $stmt->fetchAll();
            foreach ($sources as &$src) {
                $src['is_enabled'] = (bool)$src['is_enabled'];
                $src['rewrite_enabled'] = (bool)$src['rewrite_enabled'];
                $src['retry_interval_minutes'] = (int)$src['retry_interval_minutes'];
            }
            jsonOut(['success' => true, 'sources' => $sources]);
        }

        if ($method === 'PUT' || ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'update')) {
            $id = $_GET['id'] ?? $input['id'] ?? '';
            if (!$id) jsonErr("Source ID required");

            $stmt = $db->prepare("SELECT * FROM sources WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $src = $stmt->fetch();
            if (!$src) jsonErr("Source not found", 404);

            $isEnabled = isset($input['is_enabled']) ? ($input['is_enabled'] ? 1 : 0) : $src['is_enabled'];
            $rewriteEnabled = isset($input['rewrite_enabled']) ? ($input['rewrite_enabled'] ? 1 : 0) : $src['rewrite_enabled'];
            $retryMinutes = isset($input['retry_interval_minutes']) ? max(5, (int)$input['retry_interval_minutes']) : $src['retry_interval_minutes'];
            $category = $input['category'] ?? $src['category'];

            $upd = $db->prepare("
                UPDATE sources 
                SET is_enabled = :en, rewrite_enabled = :rw, retry_interval_minutes = :ret, category = :cat
                WHERE id = :id
            ");
            $upd->execute([
                'en' => $isEnabled,
                'rw' => $rewriteEnabled,
                'ret' => $retryMinutes,
                'cat' => $category,
                'id' => $id
            ]);

            jsonOut(['success' => true, 'message' => "Source {$id} updated successfully"]);
        }

        // Trigger manual grab
        if ($method === 'POST') {
            $action = $_GET['action'] ?? $input['action'] ?? '';
            if ($action === 'grab') {
                $id = $_GET['id'] ?? $input['id'] ?? '';
                if (!$id) jsonErr("Source ID required");
                $res = $manager->runSource($id, true);
                jsonOut(['success' => true, 'result' => $res]);
            }

            if ($action === 'grab-all') {
                $res = $manager->runAllEnabled(false); // Force grab all enabled
                jsonOut(['success' => true, 'results' => $res]);
            }
        }
    }

    // ------------------------------------------------------------------------
    // 3. NEWS FEED ENDPOINT
    // ------------------------------------------------------------------------
    if ($endpoint === 'news') {
        if ($method === 'GET') {
            $status = $_GET['status'] ?? 'all';
            $sourceId = $_GET['source_id'] ?? 'all';
            $search = trim($_GET['search'] ?? '');
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = max(1, min(100, (int)($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;

            $where = [];
            $params = [];

            if ($status !== 'all') {
                $where[] = "n.status = :status";
                $params['status'] = $status;
            }

            if ($sourceId !== 'all') {
                $where[] = "n.source_id = :source_id";
                $params['source_id'] = $sourceId;
            }

            if (!empty($search)) {
                $where[] = "(n.source_title LIKE :search OR n.rewritten_title LIKE :search)";
                $params['search'] = "%{$search}%";
            }

            $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

            // Total count
            $countStmt = $db->prepare("SELECT COUNT(*) FROM grabbed_news n {$whereSql}");
            $countStmt->execute($params);
            $totalItems = (int)$countStmt->fetchColumn();

            // Fetch items with source info
            $query = "
                SELECT n.*, s.name as source_name, s.rewrite_enabled as source_rewrite_enabled
                FROM grabbed_news n
                JOIN sources s ON n.source_id = s.id
                {$whereSql}
                ORDER BY n.created_at DESC
                LIMIT {$limit} OFFSET {$offset}
            ";
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $items = $stmt->fetchAll();

            foreach ($items as &$item) {
                $item['tags'] = is_string($item['tags']) ? json_decode($item['tags'], true) : $item['tags'];
                $item['rewritten_tags'] = is_string($item['rewritten_tags']) ? json_decode($item['rewritten_tags'], true) : $item['rewritten_tags'];
                $item['is_duplicate'] = (bool)$item['is_duplicate'];
                $item['source_rewrite_enabled'] = (bool)$item['source_rewrite_enabled'];
            }

            jsonOut([
                'success' => true,
                'items' => $items,
                'total' => $totalItems,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($totalItems / $limit)
            ]);
        }

        // Actions on news items
        if ($method === 'POST') {
            $action = $_GET['action'] ?? $input['action'] ?? '';
            $id = (int)($_GET['id'] ?? $input['id'] ?? 0);

            if ($action === 'process' || $action === 'regenerate') {
                if (!$id) jsonErr("News ID required");
                $result = $manager->processNewsItem($id, true);
                jsonOut([
                    'success' => true,
                    'result' => $result,
                    'item' => $result['item'] ?? null
                ]);
            }

            if ($action === 'batch-process') {
                $ids = $input['ids'] ?? [];
                if (empty($ids)) {
                    // Grab next 5 new items
                    $stmt = $db->query("SELECT id FROM grabbed_news WHERE status = 'new' AND is_duplicate = 0 ORDER BY id ASC LIMIT 5");
                    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
                }

                $processed = [];
                $errors = [];
                foreach ($ids as $newsId) {
                    try {
                        $res = $manager->processNewsItem((int)$newsId);
                        $processed[] = ['id' => $newsId, 'slug' => $res['slug']];
                    } catch (Throwable $e) {
                        $errors[] = ['id' => $newsId, 'error' => $e->getMessage()];
                    }
                }

                jsonOut(['success' => true, 'processed' => $processed, 'errors' => $errors]);
            }

            if ($action === 'toggle-duplicate') {
                if (!$id) jsonErr("News ID required");
                $stmt = $db->prepare("SELECT is_duplicate, status FROM grabbed_news WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $cur = $stmt->fetch();
                if (!$cur) jsonErr("News not found", 404);

                $newDup = $cur['is_duplicate'] ? 0 : 1;
                $newStatus = $newDup ? 'duplicate' : 'new';
                $db->prepare("
                    UPDATE grabbed_news 
                    SET is_duplicate = :dup, status = :st, status_message = :msg 
                    WHERE id = :id
                ")->execute([
                    'dup' => $newDup,
                    'st' => $newStatus,
                    'msg' => $newDup ? 'Manually marked as duplicate' : 'Duplicate flag removed manually',
                    'id' => $id
                ]);

                jsonOut(['success' => true, 'status' => $newStatus, 'is_duplicate' => (bool)$newDup]);
            }

            if ($action === 'retry') {
                if (!$id) jsonErr("News ID required");
                $db->prepare("UPDATE grabbed_news SET status = 'new', status_message = NULL WHERE id = :id")->execute(['id' => $id]);
                jsonOut(['success' => true, 'message' => "News #{$id} reset to 'new' status"]);
            }
        }

        if ($method === 'DELETE') {
            $id = (int)($_GET['id'] ?? $input['id'] ?? 0);
            if (!$id) jsonErr("News ID required");

            $db->prepare("DELETE FROM grabbed_news WHERE id = :id")->execute(['id' => $id]);
            jsonOut(['success' => true, 'message' => "News #{$id} deleted"]);
        }
    }

    // ------------------------------------------------------------------------
    // 4. SETTINGS ENDPOINT
    // ------------------------------------------------------------------------
    if ($endpoint === 'settings') {
        if ($method === 'GET') {
            $all = Database::getAllSettings();
            jsonOut(['success' => true, 'settings' => $all]);
        }

        if ($method === 'POST') {
            $action = $_GET['action'] ?? $input['action'] ?? 'update';

            if ($action === 'test-openai') {
                $key = $input['openai_api_key'] ?? Database::getSetting('openai_api_key');
                if (empty($key)) jsonErr("OpenAI API açarı boşdur.");

                $model = $input['openai_model'] ?? Database::getSetting('openai_model', 'gpt-4o-mini');

                $ch = curl_init('https://api.openai.com/v1/models');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $key],
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);
                $res = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $err = curl_error($ch);

                if ($code === 200) {
                    jsonOut(['success' => true, 'message' => "OpenAI API açarı aktivdir və əlaqə quruldu!"]);
                } else {
                    $json = json_decode($res, true);
                    $msg = $json['error']['message'] ?? ($err ?: "HTTP {$code}");
                    jsonErr("OpenAI Xətası: {$msg}");
                }
            }

            if ($action === 'test-db') {
                $status = ['ai_db' => false, 'web_db' => false, 'ai_err' => '', 'web_err' => ''];
                try {
                    Database::getAiDB();
                    $status['ai_db'] = true;
                } catch (Throwable $e) {
                    $status['ai_err'] = $e->getMessage();
                }

                try {
                    Database::getWebDB();
                    $status['web_db'] = true;
                } catch (Throwable $e) {
                    $status['web_err'] = $e->getMessage();
                }

                jsonOut(['success' => true, 'status' => $status]);
            }

            // Save settings rows
            $settingsData = $input['settings'] ?? $input;
            foreach ($settingsData as $k => $v) {
                if (is_scalar($v) && !in_array($k, ['action', 'endpoint'])) {
                    Database::setSetting($k, (string)$v);
                }
            }

            jsonOut(['success' => true, 'message' => "Tənzimləmələr uğurla yadda saxlanıldı."]);
        }
    }

    jsonErr("Bilinməyən API sorğusu: {$endpoint}", 404);

} catch (Throwable $e) {
    jsonErr($e->getMessage(), 500);
}
