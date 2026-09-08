<?php
/**
 * AI Writer Configuration Loader
 */

namespace AiWriter\Config;

function loadEnvFile(string $path): void {
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

// Load .env from ai_writer root or fallback to parent .env
loadEnvFile(__DIR__ . '/../.env');
loadEnvFile(__DIR__ . '/../../web/.env');

function aiEnv(string $key, string $default = ''): string {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}
