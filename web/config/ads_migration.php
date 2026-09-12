<?php
/**
 * Advertisements Migration Script
 * Ensures standard advertisement zones exist and removes obsolete sidebar zones.
 */

require_once __DIR__ . '/database.php';

try {
    $db = getDB();

    // 1. Ensure ads table exists
    $db->exec("
        CREATE TABLE IF NOT EXISTS `ads` (
          `id` VARCHAR(50) NOT NULL,
          `label` VARCHAR(100) NOT NULL,
          `enabled` TINYINT(1) NOT NULL DEFAULT 1,
          `image_url` TEXT,
          `link_url` TEXT,
          `width` INT NOT NULL DEFAULT 300,
          `height` INT NOT NULL DEFAULT 250,
          `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. Standard ad zones
    $standardAds = [
        [
            'id' => 'spotlight',
            'label' => 'Spotlight Banner (Ana Səhifə 4-cü Kart)',
            'enabled' => 1,
            'image_url' => 'https://images.pexels.com/photos/1779487/pexels-photo-1779487.jpeg?auto=compress&cs=tinysrgb&w=600&h=750&fit=crop',
            'link_url' => '#',
            'width' => 400,
            'height' => 500,
        ],
        [
            'id' => 'leaderboard',
            'label' => '1-ci Əsas Banner (Ana Səhifə - Üst)',
            'enabled' => 1,
            'image_url' => 'https://images.pexels.com/photos/3861969/pexels-photo-3861969.jpeg?auto=compress&cs=tinysrgb&w=1200&h=200&fit=crop',
            'link_url' => '#',
            'width' => 1200,
            'height' => 200,
        ],
        [
            'id' => 'home-promo-2',
            'label' => '2-ci Promo Banner (Ana Səhifə - Bölmələrarası)',
            'enabled' => 1,
            'image_url' => 'https://images.pexels.com/photos/2599244/pexels-photo-2599244.jpeg?auto=compress&cs=tinysrgb&w=1200&h=200&fit=crop',
            'link_url' => '#',
            'width' => 1200,
            'height' => 200,
        ],
        [
            'id' => 'inline',
            'label' => 'Məqalə İçi Reklam (Məqalə Səhifəsi)',
            'enabled' => 1,
            'image_url' => 'https://images.pexels.com/photos/7974/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=800&h=160&fit=crop',
            'link_url' => '#',
            'width' => 800,
            'height' => 160,
        ],
        [
            'id' => 'category-banner',
            'label' => 'Kateqoriya Səhifəsi Banneri',
            'enabled' => 1,
            'image_url' => 'https://images.pexels.com/photos/3861969/pexels-photo-3861969.jpeg?auto=compress&cs=tinysrgb&w=1200&h=200&fit=crop',
            'link_url' => '#',
            'width' => 1200,
            'height' => 200,
        ]
    ];

    $checkStmt = $db->prepare("SELECT COUNT(*) FROM `ads` WHERE `id` = ?");
    $insertStmt = $db->prepare("
        INSERT INTO `ads` (`id`, `label`, `enabled`, `image_url`, `link_url`, `width`, `height`)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $updateMetaStmt = $db->prepare("
        UPDATE `ads` SET `label` = ?, `width` = ?, `height` = ? WHERE `id` = ?
    ");

    foreach ($standardAds as $ad) {
        $checkStmt->execute([$ad['id']]);
        if ((int)$checkStmt->fetchColumn() === 0) {
            $insertStmt->execute([
                $ad['id'],
                $ad['label'],
                $ad['enabled'],
                $ad['image_url'],
                $ad['link_url'],
                $ad['width'],
                $ad['height']
            ]);
            echo "Inserted ad zone: {$ad['id']}\n";
        } else {
            // Update labels and dimensions to match modern design without overwriting user images/links
            $updateMetaStmt->execute([
                $ad['label'],
                $ad['width'],
                $ad['height'],
                $ad['id']
            ]);
        }
    }

    // 3. Remove obsolete sidebars if they still exist
    $db->exec("DELETE FROM `ads` WHERE `id` IN ('sidebar-left', 'sidebar-right')");

    echo "Ads migration completed successfully.\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
