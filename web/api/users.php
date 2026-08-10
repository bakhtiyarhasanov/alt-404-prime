<?php
/**
 * CRUD API for admin/manager users
 */

$currentUser = verifyAuth();

// Only editor can manage users
if (($currentUser['role'] ?? '') !== 'editor') {
    http_response_code(403);
    echo json_encode(['error' => 'Giriş qadağandır. Bu əməliyyat üçün redaktor hüququ lazımdır.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

if ($method === 'GET') {
    if ($resourceId) {
        $stmt = $db->prepare('SELECT id, email, name, avatar_url, role, created_at FROM admin_users WHERE id = ?');
        $stmt->execute([$resourceId]);
        $user = $stmt->fetch();
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'İstifadəçi tapılmadı']);
            exit;
        }
        echo json_encode($user);
    } else {
        $stmt = $db->query('SELECT id, email, name, avatar_url, role, created_at FROM admin_users ORDER BY created_at DESC');
        $users = $stmt->fetchAll();
        echo json_encode($users);
    }
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

if ($method === 'POST') {
    $email = trim($input['email'] ?? '');
    $name = trim($input['name'] ?? '');
    $password = $input['password'] ?? '';
    $role = $input['role'] ?? 'reporter';
    $avatar_url = trim($input['avatar_url'] ?? '');

    if (!$email || !$name || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'E-poçt, ad və şifrə daxil edilməlidir.']);
        exit;
    }

    // Check unique email
    $stmt = $db->prepare('SELECT 1 FROM admin_users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['error' => 'Bu e-poçt ünvanı artıq istifadə olunur.']);
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $userId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

    $stmt = $db->prepare('
        INSERT INTO admin_users (id, email, name, password, role, avatar_url)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([$userId, $email, $name, $passwordHash, $role, $avatar_url]);

    echo json_encode(['success' => true, 'id' => $userId]);
    exit;
}

if ($method === 'PUT') {
    if (!$resourceId) {
        http_response_code(400);
        echo json_encode(['error' => 'İstifadəçi ID-si göstərilməlidir.']);
        exit;
    }

    $stmt = $db->prepare('SELECT * FROM admin_users WHERE id = ?');
    $stmt->execute([$resourceId]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'İstifadəçi tapılmadı']);
        exit;
    }

    $email = trim($input['email'] ?? $user['email']);
    $name = trim($input['name'] ?? $user['name']);
    $role = $input['role'] ?? $user['role'];
    $avatar_url = trim($input['avatar_url'] ?? $user['avatar_url']);
    $password = $input['password'] ?? '';

    // Check unique email
    if ($email !== $user['email']) {
        $stmt = $db->prepare('SELECT 1 FROM admin_users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $resourceId]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'Bu e-poçt ünvanı artıq istifadə olunur.']);
            exit;
        }
    }

    if ($password !== '') {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare('
            UPDATE admin_users 
            SET email = ?, name = ?, role = ?, avatar_url = ?, password = ?
            WHERE id = ?
        ');
        $stmt->execute([$email, $name, $role, $avatar_url, $passwordHash, $resourceId]);
    } else {
        $stmt = $db->prepare('
            UPDATE admin_users 
            SET email = ?, name = ?, role = ?, avatar_url = ?
            WHERE id = ?
        ');
        $stmt->execute([$email, $name, $role, $avatar_url, $resourceId]);
    }

    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'DELETE') {
    if (!$resourceId) {
        http_response_code(400);
        echo json_encode(['error' => 'İstifadəçi ID-si göstərilməlidir.']);
        exit;
    }

    if ($resourceId === $currentUser['user_id']) {
        http_response_code(400);
        echo json_encode(['error' => 'Öz hesabınızı silə bilməzsiniz.']);
        exit;
    }

    $stmt = $db->prepare('DELETE FROM admin_users WHERE id = ?');
    $stmt->execute([$resourceId]);

    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Yanlış sorğu metodu']);
