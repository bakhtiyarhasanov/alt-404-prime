<?php
/**
 * CRUD API for categories
 */

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode(getCategories());
    exit;
}

verifyAuth();
$input = json_decode(file_get_contents('php://input'), true) ?? [];

if ($method === 'POST') {
    $slug = slugify($input['slug'] ?? $input['label'] ?? '');
    $label = $input['label'] ?? '';
    $show_on_site = !empty($input['show_on_site']) ? 1 : 0;
    $sort_order = (int)($input['sort_order'] ?? 0);
    $parent_slug = $input['parent_slug'] ?? null;
    $meta_title = $input['meta_title'] ?? null;
    $meta_description = $input['meta_description'] ?? null;
    $curated_tags = $input['curated_tags'] ?? [];

    if (!$slug || !$label) {
        http_response_code(400);
        echo json_encode(['error' => 'Label and valid Slug are required']);
        exit;
    }

    $db = getDB();
    $stmt = $db->prepare('
        INSERT INTO categories (slug, label, show_on_site, sort_order, parent_slug, meta_title, meta_description, curated_tags)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $slug, $label, $show_on_site, $sort_order, $parent_slug,
        $meta_title, $meta_description, json_encode($curated_tags)
    ]);

    echo json_encode(['success' => true, 'slug' => $slug]);
    exit;
}

if ($method === 'PUT') {
    if (!$resourceId) {
        http_response_code(400);
        echo json_encode(['error' => 'Category slug is required']);
        exit;
    }

    $db = getDB();
    $label = $input['label'] ?? '';
    $show_on_site = !empty($input['show_on_site']) ? 1 : 0;
    $sort_order = (int)($input['sort_order'] ?? 0);
    $parent_slug = $input['parent_slug'] ?? null;
    $meta_title = $input['meta_title'] ?? null;
    $meta_description = $input['meta_description'] ?? null;
    $curated_tags = $input['curated_tags'] ?? [];

    $stmt = $db->prepare('
        UPDATE categories 
        SET label = ?, show_on_site = ?, sort_order = ?, parent_slug = ?, meta_title = ?, meta_description = ?, curated_tags = ?
        WHERE slug = ?
    ');
    $stmt->execute([
        $label, $show_on_site, $sort_order, $parent_slug,
        $meta_title, $meta_description, json_encode($curated_tags),
        $resourceId
    ]);

    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'DELETE') {
    if (!$resourceId) {
        http_response_code(400);
        echo json_encode(['error' => 'Category slug is required']);
        exit;
    }

    $db = getDB();
    $stmt = $db->prepare('DELETE FROM categories WHERE slug = ?');
    $stmt->execute([$resourceId]);

    echo json_encode(['success' => true]);
    exit;
}
