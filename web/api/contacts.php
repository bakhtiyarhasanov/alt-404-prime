<?php
/**
 * API for contact submissions
 */

verifyAuth();
$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

if ($method === 'GET') {
    $stmt = $db->query('SELECT * FROM contact_submissions ORDER BY created_at DESC');
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($method === 'PUT') {
    if (!$resourceId) {
        http_response_code(400);
        echo json_encode(['error' => 'Submission ID is required']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $status = $input['status'] ?? 'reviewed';

    $stmt = $db->prepare('UPDATE contact_submissions SET status = ? WHERE id = ?');
    $stmt->execute([$status, $resourceId]);

    echo json_encode(['success' => true]);
    exit;
}
