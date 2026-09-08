<?php
/**
 * AI Writer Entry Point
 * Serves the built Vue.js Manager UI or redirects to manager
 */

$distFile = __DIR__ . '/manager/dist/index.html';

if (file_exists($distFile)) {
    // Read and output built frontend
    $content = file_get_contents($distFile);
    
    // Adjust asset paths if served from subfolder /ai_writer/
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
    if ($basePath && $basePath !== '/') {
        $content = str_replace('href="/assets/', 'href="' . $basePath . '/manager/dist/assets/', $content);
        $content = str_replace('src="/assets/', 'src="' . $basePath . '/manager/dist/assets/', $content);
    } else {
        $content = str_replace('href="/assets/', 'href="/manager/dist/assets/', $content);
        $content = str_replace('src="/assets/', 'src="/manager/dist/assets/', $content);
    }

    echo $content;
    exit;
}

// Fallback informative page
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="az">
<head>
  <meta charset="UTF-8">
  <title>AI Writer | Alt404 Prime</title>
  <style>
    body { font-family: sans-serif; background: #0b0f19; color: #fff; padding: 50px; text-align: center; }
    h1 { color: #FCDB56; }
    a { color: #00F0FF; text-decoration: none; }
  </style>
</head>
<body>
  <h1>AI Writer Sistemi</h1>
  <p>Manager UI-nı işə salmaq üçün <code>cd ai_writer/manager && npm run dev</code> əmrini icra edin.</p>
  <p>Və ya API endpoints üçün: <a href="api/index.php?endpoint=stats">/ai_writer/api/index.php?endpoint=stats</a></p>
</body>
</html>
