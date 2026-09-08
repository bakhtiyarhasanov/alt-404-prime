<?php
/**
 * CLI: Process pending news (AI Rewrite & Post as Web Draft)
 * Usage:
 *   php process.php            # Process up to 5 pending items
 *   php process.php --limit=10 # Process up to 10 pending items
 *   php process.php --id=42    # Process specific item
 */

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

$options = getopt('', ['limit:', 'id:']);
$limit = isset($options['limit']) ? (int)$options['limit'] : 5;
$specificId = isset($options['id']) ? (int)$options['id'] : null;

echo "=== AI Writer: Processing Engine (AI Rewrite -> Post Draft) ===\n";

try {
    $db = Database::getAiDB();
    $manager = new GrabberManager();

    if ($specificId) {
        echo "Processing single news ID: #{$specificId}...\n";
        $res = $manager->processNewsItem($specificId);
        echo "SUCCESS: Article posted as draft! Slug: {$res['slug']}\n";
    } else {
        $stmt = $db->prepare("
            SELECT id, source_title FROM grabbed_news 
            WHERE status = 'new' AND is_duplicate = 0
            ORDER BY id ASC 
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $pending = $stmt->fetchAll();

        echo "Found " . count($pending) . " pending articles to process.\n";

        foreach ($pending as $row) {
            echo "Processing #{$row['id']}: {$row['source_title']}...\n";
            try {
                $res = $manager->processNewsItem((int)$row['id']);
                echo " -> OK! Draft created. Slug: {$res['slug']}\n";
            } catch (Throwable $err) {
                echo " -> FAILED: " . $err->getMessage() . "\n";
            }
        }
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
