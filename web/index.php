<?php
/**
 * alt404 Prime — Front Controller
 *
 * Routes all incoming requests to the appropriate page or API handler.
 */

// Parse the request URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// ── API routes ───────────────────────────────────────────────────────────────
if (strpos($uri, '/api/') === 0) {
    require_once __DIR__ . '/api/index.php';
    exit;
}

require_once __DIR__ . '/helpers/functions.php';

// ── Static routes ────────────────────────────────────────────────────────────
$staticRoutes = [
    '/'                  => 'pages/home.php',
    '/xeberler'          => 'pages/news.php',
    '/axtar'             => 'pages/search.php',
    '/haqqimizda'        => 'pages/about.php',
    '/elaqe'             => 'pages/contact.php',
    '/istifade-sertleri' => 'pages/terms.php',
    '/cerezler'          => 'pages/cookies.php',
];

if (isset($staticRoutes[$uri])) {
    require_once __DIR__ . '/' . $staticRoutes[$uri];
    exit;
}

// ── Legacy redirects ─────────────────────────────────────────────────────────
// /kateqoriya/:slug → /:slug
if (preg_match('#^/kateqoriya/([^/]+)$#', $uri, $m)) {
    header('Location: /' . $m[1], true, 301);
    exit;
}

// /xeber/:category/:slug → /:category/:slug
if (preg_match('#^/xeber/([^/]+)/([^/]+)$#', $uri, $m)) {
    header('Location: /' . $m[1] . '/' . $m[2], true, 301);
    exit;
}

// /xeber/:slug → find the article and redirect to /:category/:slug
if (preg_match('#^/xeber/([^/]+)$#', $uri, $m)) {
    $article = getArticleBySlug($m[1]);
    if ($article) {
        header('Location: /' . $article['category'] . '/' . $article['slug'], true, 301);
    } else {
        header('Location: /', true, 302);
    }
    exit;
}

// ── Dynamic routes ───────────────────────────────────────────────────────────
$segments = explode('/', trim($uri, '/'));

// /:categorySlug/:postSlug → Article page
if (count($segments) === 2) {
    $categorySlug = $segments[0];
    $postSlug = $segments[1];
    require_once __DIR__ . '/pages/article.php';
    exit;
}

// /:categorySlug → Category page (validate against DB)
if (count($segments) === 1) {
    $categorySlug = $segments[0];
    if (isValidCategory($categorySlug)) {
        require_once __DIR__ . '/pages/category.php';
        exit;
    }
}

// ── 404 ──────────────────────────────────────────────────────────────────────
http_response_code(404);
require_once __DIR__ . '/pages/404.php';
