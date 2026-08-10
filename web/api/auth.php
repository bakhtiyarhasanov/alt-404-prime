<?php
/**
 * OAuth Login / Token Verification
 */

$action = $segments[1] ?? '';

if ($action === 'login') {
    // Parse JSON inputs
    $input = json_decode(file_get_contents('php://input'), true);
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'E-poçt və şifrə daxil edilməlidir.']);
        exit;
    }

    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM admin_users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || empty($user['password']) || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['error' => 'E-poçt və ya şifrə yanlışdır.']);
        exit;
    }

    // Create session token
    $sessionToken = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $sessionToken);
    $expires = date('Y-m-d H:i:s', strtotime('+7 days'));

    $stmt = $db->prepare('INSERT INTO auth_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)');
    $stmt->execute([$user['id'], $tokenHash, $expires]);

    echo json_encode([
        'token' => $sessionToken,
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'avatar_url' => $user['avatar_url'],
            'role' => $user['role'] ?? 'editor'
        ]
    ]);
    exit;
}

if ($action === 'verify') {
    $session = verifyAuth();
    echo json_encode([
        'user' => [
            'id' => $session['user_id'],
            'email' => $session['email'],
            'name' => $session['name'],
            'avatar_url' => $session['avatar_url'],
            'role' => $session['role'] ?? 'editor'
        ]
    ]);
    exit;
}

if ($action === 'logout') {
    $token = getBearerToken();
    if ($token) {
        $db = getDB();
        $tokenHash = hash('sha256', $token);
        $stmt = $db->prepare('DELETE FROM auth_tokens WHERE token_hash = ?');
        $stmt->execute([$tokenHash]);
    }
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid auth action']);
