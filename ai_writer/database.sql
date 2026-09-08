-- ============================================================================
-- AI Writer — MySQL Database Schema
-- Separate Database: alt404_ai_writer
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `alt404_ai_writer` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `alt404_ai_writer`;

-- ---------------------------------------------------------------------------
-- 1. sources: Website configurations and grabber parameters
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sources` (
  `id` VARCHAR(50) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `category` VARCHAR(100) NOT NULL DEFAULT 'texnologiya',
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `rewrite_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `retry_interval_minutes` INT NOT NULL DEFAULT 30,
  `grabber_class` VARCHAR(100) NOT NULL,
  `last_grabbed_at` DATETIME DEFAULT NULL,
  `last_status` VARCHAR(50) DEFAULT 'idle',
  `last_error` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 2. grabbed_news: All aggregated items with workflow statuses
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `grabbed_news` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `source_id` VARCHAR(50) NOT NULL,
  `external_id` VARCHAR(255) NOT NULL,
  `source_url` TEXT NOT NULL,
  `source_title` TEXT NOT NULL,
  `source_excerpt` TEXT DEFAULT NULL,
  `source_content` LONGTEXT DEFAULT NULL,
  `source_image_url` TEXT DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT 'texnologiya',
  `tags` JSON DEFAULT NULL,
  `status` ENUM('new', 'generating', 'posted', 'duplicate', 'error') NOT NULL DEFAULT 'new',
  `status_message` TEXT DEFAULT NULL,
  `is_duplicate` TINYINT(1) NOT NULL DEFAULT 0,
  `duplicate_of_id` INT DEFAULT NULL,
  `rewritten_title` TEXT DEFAULT NULL,
  `rewritten_excerpt` TEXT DEFAULT NULL,
  `rewritten_content` LONGTEXT DEFAULT NULL,
  `rewritten_tags` JSON DEFAULT NULL,
  `posted_article_id` CHAR(36) DEFAULT NULL,
  `posted_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_source_ext` (`source_id`, `external_id`(190)),
  KEY `idx_status` (`status`),
  KEY `idx_source` (`source_id`),
  KEY `idx_created` (`created_at` DESC),
  CONSTRAINT `fk_grabbed_source` FOREIGN KEY (`source_id`) REFERENCES `sources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 3. settings: Key-value configuration for OpenAI & duplicate detection
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT '',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 4. users: AI Writer Manager Users (single admin level, admin-created)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `password` VARCHAR(255) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 5. auth_tokens: Session tokens for AI Writer authentication
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `auth_tokens` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `user_id` INT NOT NULL,
  `token_hash` VARCHAR(64) NOT NULL UNIQUE,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_token_hash` (`token_hash`),
  CONSTRAINT `fk_auth_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===========================================================================
-- SEED DATA: 12 INITIAL SOURCES
-- ===========================================================================
INSERT INTO `sources` (`id`, `name`, `url`, `category`, `is_enabled`, `rewrite_enabled`, `retry_interval_minutes`, `grabber_class`) VALUES
  ('donanimhaber', 'DonanımHaber', 'https://www.donanimhaber.com/teknoloji-haberleri', 'texnologiya', 1, 1, 30, 'DonanimHaberGrabber'),
  ('kaldata', 'Kaldata', 'https://www.kaldata.com/it-%d0%bd%d0%be%d0%b2%d0%b8%d0%bd%d0%b8', 'texnologiya', 1, 1, 45, 'KaldataGrabber'),
  ('log', 'Log.com.tr', 'https://www.log.com.tr/teknoloji-haberleri/', 'texnologiya', 1, 1, 30, 'LogGrabber'),
  ('reuters', 'Reuters Technology', 'https://www.reuters.com/technology/', 'texnologiya', 1, 1, 60, 'ReutersGrabber'),
  ('dexerto', 'Dexerto (Latest News)', 'https://www.dexerto.com/', 'texnologiya', 1, 1, 30, 'DexertoGrabber'),
  ('cnet', 'CNET', 'https://www.cnet.com/news/', 'texnologiya', 1, 1, 45, 'CnetGrabber'),
  ('engadget', 'Engadget', 'https://www.engadget.com/latest/', 'texnologiya', 1, 1, 45, 'EngadgetGrabber'),
  ('azertag', 'Azertac (Economy/Tech)', 'https://azertag.az/bolme/economy', 'texnologiya', 1, 0, 30, 'AzertagGrabber'),
  ('idda', 'IDDA (İnnovasiya və Rəqəmsal İnkişaf)', 'https://idda.az/az/xeberler', 'texnologiya', 1, 0, 60, 'IddaGrabber'),
  ('ayna', 'AYNA (Azərbaycan Yerüstü Nəqliyyat)', 'https://ayna.gov.az/az/news', 'avtomobil', 1, 0, 60, 'AynaGrabber'),
  ('mincom', 'Rəqəmsal İnkişaf və Nəqliyyat Nazirliyi', 'https://mincom.gov.az/az/media/xeberler', 'texnologiya', 1, 0, 60, 'MincomGrabber'),
  ('cert', 'CERT.az (Kibertəhlükəsizlik)', 'https://www.cert.az/news/23', 'texnologiya', 1, 0, 60, 'CertGrabber')
ON DUPLICATE KEY UPDATE 
  `name` = VALUES(`name`),
  `url` = VALUES(`url`),
  `category` = VALUES(`category`),
  `rewrite_enabled` = VALUES(`rewrite_enabled`),
  `grabber_class` = VALUES(`grabber_class`);

-- ===========================================================================
-- SEED DATA: SETTINGS
-- ===========================================================================
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
  ('openai_api_key', '', 'OpenAI API Secret Key for Azerbaijani news rewriting'),
  ('openai_model', 'gpt-4o-mini', 'OpenAI model: gpt-4o-mini, gpt-4o, gpt-4-turbo'),
  ('duplicate_check_enabled', '1', '1 = Check and mark duplicates automatically, 0 = Disabled'),
  ('duplicate_threshold', '0.70', 'Similarity threshold for duplicate title matching (0.0 to 1.0)'),
  ('auto_publish_draft', '1', '1 = Automatically insert into web articles table as draft (published = 0)'),
  ('openai_temperature', '0.7', 'Sampling temperature for creative writing'),
  ('system_prompt', 'Sən peşəkar xəbər jurnalistisən. Xarici dildəki texnoloji və ictimai xəbərləri araşdıraraq səlis, anlaşıqlı, bitərəf və peşəkar Azərbaycan dilinə adaptasiya edib yenidən yazırsan.', 'AI System Prompt for rewriting news'),
  ('regenerate_prompt', 'Linkdəki xəbəri diqqətlə oxu, həqiqiliyini digər mənbələrdə araşdırdıqdan sonra Azərbaycan dilində yaz. Mətnlə bağlı tələblər belədir -\nMətnə uyğun ən uyğun, təsirli başlıq yaz.\nBütün xüsusi isimlər Azərbaycan dilində yazılması, düzgün yazılış forması saytlarda yoxlanılmalıdır.\nSəliqəli, abzaslara bölünmüş şəkildə aydın dildə yaz. Xəbərə və yaşanan hadisələrə münasibət bildirmə, sadəcə, xəbəri çatdır.\nMətndə xüsusi vurğulamaq istədiyin hissələri boldla və ya kursivlə vermə, sadə fontla ver hamısını.\nSonda xəbəri yazarkən istifadə etdiyin bütün mənbələri bu şəkildə qeyd et -\nMƏNBƏ:\nmənbə 1 link şəklində\nmənbə 2 link şəklində\nmənbə 3 link şəklində və s.\nİstinad etdiyin mənbə sayı 5-dən çox olmasın. Link uzun olduqda ixtisar et və … nöqtə ilə tamamla. Məsələn, [ https://www.kaldata.com/it-%d0%bd%d0%be%](https://www.kaldata.com/it-%25d0%25bd%25d0%25be%25)…', 'AI Regenerate / Rewrite Prompt for OpenAI news generation')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

-- ===========================================================================
-- SEED DATA: DEFAULT ADMIN USER (admin / admin)
-- ===========================================================================
INSERT INTO `users` (`id`, `username`, `name`, `password`) VALUES
  (1, 'admin', 'Administrator', '$2y$12$ovbj2r/orxUviYwpXiMrwOJ6ECKcNZo17LdB0OTbjjgkdFv5WtLu2')
ON DUPLICATE KEY UPDATE `username` = `username`;

