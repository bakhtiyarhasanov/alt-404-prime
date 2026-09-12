-- ============================================================================
-- alt404 Prime — MySQL Database Schema
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `alt404` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `alt404`;

-- ---------------------------------------------------------------------------
-- 1. admin_users
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` CHAR(36) NOT NULL DEFAULT (UUID()),
  `email` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) DEFAULT '',
  `avatar_url` VARCHAR(500) DEFAULT '',
  `password` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_admin_email` (`email`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 2. articles
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `articles` (
  `id` CHAR(36) NOT NULL DEFAULT (UUID()),
  `title` TEXT NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `excerpt` TEXT,
  `content` LONGTEXT,
  `category` VARCHAR(100) NOT NULL DEFAULT 'texnologiya',
  `image_url` TEXT,
  `tags` JSON,
  `featured` TINYINT(1) DEFAULT 0,
  `published` TINYINT(1) DEFAULT 1,
  `updating` TINYINT(1) DEFAULT 0,
  `reading_time` INT DEFAULT 3,
  `start_time` DATETIME DEFAULT NULL,
  `end_time` DATETIME DEFAULT NULL,
  `views` INT UNSIGNED DEFAULT 0,
  `versions` JSON,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_article_slug` (`slug`),
  KEY `idx_article_category` (`category`),
  KEY `idx_article_created` (`created_at` DESC),
  KEY `idx_article_featured` (`featured`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 3. categories
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `slug` VARCHAR(100) NOT NULL,
  `label` VARCHAR(255) NOT NULL DEFAULT '',
  `show_on_site` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `parent_slug` VARCHAR(100) DEFAULT NULL,
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` TEXT,
  `curated_tags` JSON,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`slug`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 4. ads
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ads` (
  `id` VARCHAR(50) NOT NULL,
  `label` VARCHAR(255) NOT NULL DEFAULT '',
  `enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `image_url` TEXT,
  `link_url` TEXT,
  `width` INT NOT NULL DEFAULT 0,
  `height` INT NOT NULL DEFAULT 0,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 5. media_library
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `media_library` (
  `id` CHAR(36) NOT NULL DEFAULT (UUID()),
  `url` TEXT NOT NULL,
  `alt_text` VARCHAR(500) DEFAULT '',
  `title` VARCHAR(500) DEFAULT '',
  `file_name` VARCHAR(500) DEFAULT '',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 6. contact_submissions
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_submissions` (
  `id` CHAR(36) NOT NULL DEFAULT (UUID()),
  `ad_soyad` VARCHAR(255) NOT NULL DEFAULT '',
  `email` VARCHAR(255) NOT NULL DEFAULT '',
  `mesaj` TEXT,
  `status` VARCHAR(20) NOT NULL DEFAULT 'new',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 7. home_videos
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `home_videos` (
  `id` CHAR(36) NOT NULL DEFAULT (UUID()),
  `title` VARCHAR(500) NOT NULL DEFAULT '',
  `youtube_url` TEXT,
  `thumbnail_url` TEXT,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 8. projects (Xüsusi Layihələr)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
  `id` VARCHAR(64) NOT NULL,
  `title` VARCHAR(500) NOT NULL DEFAULT '',
  `subtitle` VARCHAR(500) DEFAULT '',
  `category` VARCHAR(100) NOT NULL DEFAULT 'EKSPERİMENT',
  `image` TEXT NOT NULL,
  `duration` VARCHAR(50) DEFAULT '15:00',
  `youtube_url` TEXT NOT NULL,
  `description` TEXT DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_projects_sort` (`sort_order`, `enabled`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 9. auth_tokens (JWT refresh / session tracking)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `auth_tokens` (
  `id` CHAR(36) NOT NULL DEFAULT (UUID()),
  `user_id` CHAR(36) NOT NULL,
  `token_hash` VARCHAR(128) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_token_user` (`user_id`),
  KEY `idx_token_hash` (`token_hash`),
  CONSTRAINT `fk_token_user` FOREIGN KEY (`user_id`) REFERENCES `admin_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 10. settings
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `key` VARCHAR(100) NOT NULL,
  `label` VARCHAR(255) NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `group_name` VARCHAR(100) NOT NULL,
  `value` LONGTEXT DEFAULT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================================
-- SEED DATA
-- ===========================================================================

-- Default categories
INSERT INTO `categories` (`slug`, `label`, `show_on_site`, `sort_order`, `meta_title`, `meta_description`, `curated_tags`) VALUES
  ('texnologiya', 'Texnologiya Xəbərləri', 1, 10, 'Texnologiya xəbərləri - Smartfonlar, Süni intellekt, Startap | alt404', 'Azərbaycanda texnologiya xəbərləri, smartfon icmalları, süni intellekt və startap yenilikləri. alt404.az', '[]'),
  ('elm-gundem', 'Elm', 1, 20, 'Elm xəbərləri | alt404', 'Elm, araşdırma və texnologiyanın elmi tərəfinə dair yeniliklər. alt404.az', '[]'),
  ('suni-intellekt', 'Süni İntellekt', 1, 30, 'Süni intellekt xəbərləri | alt404', 'Süni intellekt alqoritmləri, modellər və tətbiqlər barədə xəbərlər. alt404.az', '["AI"]'),
  ('startap', 'Startap', 1, 40, 'Startap xəbərləri | alt404', 'Yeni startaplar, investisiyalar və məhsul yenilikləri. alt404.az', '[]'),
  ('texnobloq', 'Texnobloq', 1, 50, 'Texnobloq | alt404', 'Texnologiya üzrə analiz və icmal məqalələri. alt404.az', '[]'),
  ('avtomobil', 'Avtomobil', 1, 60, 'Avtomobil xəbərləri | alt404', 'Elektromobillər, avtomobil texnologiyası və sənaye yenilikləri. alt404.az', '[]'),
  ('texnoicmal', 'Texnoicmal', 1, 65, 'Texnoicmal | alt404', 'Cihazlar və texnologiya üzrə icmallar. alt404.az', '[]'),
  ('meqaleler', 'Məqalələr', 1, 70, 'Məqalələr | alt404', 'Texnologiya və elm mövzularında məqalələr. alt404.az', '[]'),
  ('cihazlar', 'Cihazlar', 0, 5, 'Cihazlar | alt404', 'Smartfonlar və digər cihaz yenilikləri. alt404.az', '[]')
ON DUPLICATE KEY UPDATE `slug` = `slug`;

-- Default ad zones
INSERT INTO `ads` (`id`, `label`, `enabled`, `image_url`, `link_url`, `width`, `height`) VALUES
  ('leaderboard', 'Leaderboard (üst banner)', 1, 'https://images.pexels.com/photos/1779487/pexels-photo-1779487.jpeg?auto=compress&cs=tinysrgb&w=900&h=90&fit=crop', '#', 900, 90),
  ('sidebar-left', 'Sol Panel', 1, 'https://images.pexels.com/photos/3861969/pexels-photo-3861969.jpeg?auto=compress&cs=tinysrgb&w=160&h=600&fit=crop', '#', 160, 600),
  ('sidebar-right', 'Sağ Panel', 1, 'https://images.pexels.com/photos/2599244/pexels-photo-2599244.jpeg?auto=compress&cs=tinysrgb&w=160&h=600&fit=crop', '#', 160, 600),
  ('inline', 'Xəbər içi reklam', 1, 'https://images.pexels.com/photos/7974/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=728&h=90&fit=crop', '#', 728, 90)
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Default administrator (admin@alt404.com / 123456789)
INSERT INTO `admin_users` (`email`, `name`, `password`) VALUES
  ('admin@alt404.com', 'Admin', '$2y$12$0SKM/WrPjEoPYTAFNeDn6.Z9JCKm/PVVM9HIlK2RVsXyreUQdjuF2')
ON DUPLICATE KEY UPDATE `email` = `email`;

