<?php
/**
 * AI Writer Database Singleton & Schema Manager
 */

namespace AiWriter\Core;

use PDO;
use PDOException;

require_once __DIR__ . '/../config/config.php';
use function AiWriter\Config\aiEnv;

class Database {
    private static ?PDO $aiPdo = null;
    private static ?PDO $webPdo = null;

    /**
     * Get connection to the AI Writer dedicated database
     */
    public static function getAiDB(): PDO {
        if (self::$aiPdo !== null) {
            return self::$aiPdo;
        }

        $host = aiEnv('AI_DB_HOST', 'localhost');
        $port = aiEnv('AI_DB_PORT', '3306');
        $name = aiEnv('AI_DB_NAME', 'alt404_ai_writer');
        $user = aiEnv('AI_DB_USER', 'root');
        $pass = aiEnv('AI_DB_PASS', '');

        // First attempt connecting directly to database
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            self::$aiPdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            // Database might not exist yet, attempt to connect to server and create database
            try {
                $dsnServer = "mysql:host={$host};port={$port};charset=utf8mb4";
                $serverPdo = new PDO($dsnServer, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Now connect to the created DB
                $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
                self::$aiPdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $ex) {
                throw new PDOException("AI Writer DB Connection Error: " . $ex->getMessage());
            }
        }

        self::ensureSchema();
        return self::$aiPdo;
    }

    /**
     * Get connection to the Target Alt404 Web database
     */
    public static function getWebDB(): PDO {
        if (self::$webPdo !== null) {
            return self::$webPdo;
        }

        $host = aiEnv('WEB_DB_HOST', aiEnv('DB_HOST', 'localhost'));
        $port = aiEnv('WEB_DB_PORT', aiEnv('DB_PORT', '3306'));
        $name = aiEnv('WEB_DB_NAME', aiEnv('DB_NAME', 'alt404'));
        $user = aiEnv('WEB_DB_USER', aiEnv('DB_USER', 'root'));
        $pass = aiEnv('WEB_DB_PASS', aiEnv('DB_PASS', ''));

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        try {
            self::$webPdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new PDOException("Web DB Connection Error: " . $e->getMessage());
        }

        return self::$webPdo;
    }

    /**
     * Ensure tables and seed records exist in AI Writer DB
     */
    public static function ensureSchema(): void {
        if (!self::$aiPdo) return;

        // Check if sources table exists
        $check = self::$aiPdo->query("SHOW TABLES LIKE 'sources'")->fetch();
        if ($check) {
            return;
        }

        $sqlFile = __DIR__ . '/../database.sql';
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            // Split and run statements
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $stmt) {
                if (empty($stmt)) continue;
                // Skip USE statements to preserve existing selected db
                if (stripos($stmt, 'USE ') === 0) continue;
                try {
                    self::$aiPdo->exec($stmt);
                } catch (PDOException $e) {
                    // Ignore non-fatal duplicates
                }
            }
        }
    }

    /**
     * Get a setting from the settings table
     */
    public static function getSetting(string $key, string $default = ''): string {
        $db = self::getAiDB();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = :k");
        $stmt->execute(['k' => $key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (string)$val : $default;
    }

    /**
     * Set a setting in the settings table
     */
    public static function setSetting(string $key, string $value, string $desc = ''): void {
        $db = self::getAiDB();
        $stmt = $db->prepare("
            INSERT INTO settings (setting_key, setting_value, description)
            VALUES (:k, :v, :d)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute(['k' => $key, 'v' => $value, 'd' => $desc]);
    }

    /**
     * Retrieve all settings as associative array
     */
    public static function getAllSettings(): array {
        $db = self::getAiDB();
        $stmt = $db->query("SELECT setting_key, setting_value, description, updated_at FROM settings");
        $results = [];
        while ($row = $stmt->fetch()) {
            $results[$row['setting_key']] = $row;
        }

        if (!isset($results['regenerate_prompt'])) {
            $defaultPrompt = "Linkdəki xəbəri diqqətlə oxu, həqiqiliyini digər mənbələrdə araşdırdıqdan sonra Azərbaycan dilində yaz. Mətnlə bağlı tələblər belədir -\nMətnə uyğun ən uyğun, təsirli başlıq yaz.\nBütün xüsusi isimlər Azərbaycan dilində yazılması, düzgün yazılış forması saytlarda yoxlanılmalıdır.\nSəliqəli, abzaslara bölünmüş şəkildə aydın dildə yaz. Xəbərə və yaşanan hadisələrə münasibət bildirmə, sadəcə, xəbəri çatdır.\nMətndə xüsusi vurğulamaq istədiyin hissələri boldla və ya kursivlə vermə, sadə fontla ver hamısını.\nSonda xəbəri yazarkən istifadə etdiyin bütün mənbələri bu şəkildə qeyd et -\nMƏNBƏ:\nmənbə 1 link şəklində\nmənbə 2 link şəklində\nmənbə 3 link şəklində və s.\nİstinad etdiyin mənbə sayı 5-dən çox olmasın. Link uzun olduqda ixtisar et və … nöqtə ilə tamamla. Məsələn, [ https://www.kaldata.com/it-%d0%bd%d0%be%](https://www.kaldata.com/it-%25d0%25bd%25d0%25be%25)…";
            self::setSetting('regenerate_prompt', $defaultPrompt, 'AI Regenerate / Rewrite Prompt for OpenAI');
            $results['regenerate_prompt'] = [
                'setting_key' => 'regenerate_prompt',
                'setting_value' => $defaultPrompt,
                'description' => 'AI Regenerate / Rewrite Prompt for OpenAI',
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }

        return $results;
    }
}
