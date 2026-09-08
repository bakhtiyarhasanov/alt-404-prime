<?php
/**
 * CLI: Grab technology news from sources
 * Usage:
 *   php grab.php                  # Run all enabled sources (respecting intervals)
 *   php grab.php --force          # Force run all enabled sources
 *   php grab.php --source=cnet    # Run specific source
 */

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/GrabberInterface.php';
require_once __DIR__ . '/../core/BaseGrabber.php';
require_once __DIR__ . '/../core/DuplicateDetector.php';
require_once __DIR__ . '/../core/AiRewriter.php';
require_once __DIR__ . '/../core/Publisher.php';

// Autoload grabbers
foreach (glob(__DIR__ . '/../core/grabbers/*.php') as $grabberFile) {
    require_once $grabberFile;
}

require_once __DIR__ . '/../core/GrabberManager.php';

use AiWriter\Core\GrabberManager;

$options = getopt('', ['source:', 'force']);
$source = $options['source'] ?? null;
$force = isset($options['force']);

echo "=== AI Writer: Grabber CLI ===\n";

try {
    $manager = new GrabberManager();

    if ($source) {
        echo "Running single source: {$source}...\n";
        $res = $manager->runSource($source, true);
        echo "Done!\n";
        print_r($res);
    } else {
        echo "Running all enabled sources (force: " . ($force ? 'yes' : 'no') . ")...\n";
        $results = $manager->runAllEnabled(!$force);
        echo "Finished run:\n";
        foreach ($results as $id => $res) {
            echo " - {$id}: " . json_encode($res, JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
