<?php
/**
 * All-in-one cron job runner
 * Run this periodically via crontab (e.g. every 10 or 15 minutes):
 *   * /15 * * * * php /path/to/ai_writer/cli/cron.php >> /path/to/ai_writer/cron.log 2>&1
 */

echo "[" . date('Y-m-d H:i:s') . "] Starting AI Writer Cron Job...\n";

// 1. Run grabbers
passthru("php " . escapeshellarg(__DIR__ . '/grab.php'));

// 2. Process pending items
passthru("php " . escapeshellarg(__DIR__ . '/process.php') . " --limit=5");

echo "[" . date('Y-m-d H:i:s') . "] AI Writer Cron Job Finished.\n";
