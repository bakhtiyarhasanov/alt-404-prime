<?php
require_once __DIR__ . '/helpers/functions.php';

try {
    $db = getDB();
    
    // 1. Create article_versions table if not exists
    $db->exec("
        CREATE TABLE IF NOT EXISTS `article_versions` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `article_id` CHAR(36) NOT NULL,
          `version` INT NOT NULL,
          `title` TEXT NOT NULL,
          `content` LONGTEXT,
          `author` VARCHAR(255) DEFAULT '',
          `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
          CONSTRAINT `fk_av_article` FOREIGN KEY (`article_id`) REFERENCES `articles`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");
    
    echo "Table article_versions created or already exists.\n";
    
    // Check if versions column exists
    $checkStmt = $db->query("SHOW COLUMNS FROM `articles` LIKE 'versions'");
    if ($checkStmt->rowCount() > 0) {
        // 2. Fetch all articles and migrate their versions
        $articles = $db->query("SELECT id, versions FROM articles WHERE versions IS NOT NULL")->fetchAll();
        
        $insertStmt = $db->prepare("
            INSERT INTO article_versions (article_id, version, title, content, author, created_at)
            VALUES (:article_id, :version, :title, :content, :author, :created_at)
        ");
        
        $migratedCount = 0;
        foreach ($articles as $article) {
            $versions = json_decode($article['versions'], true);
            if (is_array($versions)) {
                foreach ($versions as $v) {
                    $insertStmt->execute([
                        'article_id' => $article['id'],
                        'version' => $v['version'] ?? 1,
                        'title' => $v['title'] ?? '',
                        'content' => $v['content'] ?? '',
                        'author' => $v['author'] ?? '',
                        'created_at' => $v['created_at'] ?? date('Y-m-d H:i:s')
                    ]);
                    $migratedCount++;
                }
            }
        }
        
        echo "Successfully migrated $migratedCount versions.\n";
        
        // 3. Drop the versions column
        $db->exec("ALTER TABLE `articles` DROP COLUMN `versions`");
        echo "Dropped 'versions' column from 'articles' table.\n";
    } else {
        echo "'versions' column does not exist in 'articles' table. Migration might have already run.\n";
    }
    
    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}
