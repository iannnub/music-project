-- =====================================================================
-- Migration v2: KakYo Lesson Client Feature Updates & Schema Enhancements
-- Compatible with MariaDB 10.4+ / MySQL (Localhost & InfinityFree)
-- =====================================================================

-- 1. Extend users table for Public Student Registration, Branch Pricing & Tata Tertib Gate
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `branch` VARCHAR(30) DEFAULT NULL AFTER `ig_link`,
  ADD COLUMN IF NOT EXISTS `spp_rate` DECIMAL(10,2) DEFAULT NULL AFTER `branch`,
  ADD COLUMN IF NOT EXISTS `gender` VARCHAR(20) DEFAULT NULL AFTER `spp_rate`,
  ADD COLUMN IF NOT EXISTS `school_or_work` VARCHAR(150) DEFAULT NULL AFTER `gender`,
  ADD COLUMN IF NOT EXISTS `age` INT(11) DEFAULT NULL AFTER `school_or_work`,
  ADD COLUMN IF NOT EXISTS `program` VARCHAR(100) DEFAULT NULL AFTER `age`,
  ADD COLUMN IF NOT EXISTS `instrument` VARCHAR(100) DEFAULT NULL AFTER `program`,
  ADD COLUMN IF NOT EXISTS `preferred_day` VARCHAR(50) DEFAULT NULL AFTER `instrument`,
  ADD COLUMN IF NOT EXISTS `preferred_time` VARCHAR(50) DEFAULT NULL AFTER `preferred_day`,
  ADD COLUMN IF NOT EXISTS `address` TEXT DEFAULT NULL AFTER `preferred_time`,
  ADD COLUMN IF NOT EXISTS `agreed_tatib_at` DATETIME DEFAULT NULL AFTER `address`;

-- 2. Application settings table (Global TikTok Link & Monthly Photo Cleanup Tracker)
CREATE TABLE IF NOT EXISTS `app_settings` (
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `app_settings` (`setting_key`, `setting_value`) VALUES
  ('tiktok_practice_url', 'https://www.tiktok.com/@kakyo_lesson'),
  ('tiktok_practice_title', 'Video Latihan & Dokumentasi Siswa KakYo Lesson'),
  ('tiktok_practice_desc', 'Pantau video latihan rutin, penampilan siswa, dan evaluasi praktik langsung dari kanal TikTok resmi KakYo Lesson.'),
  ('last_teacher_photo_cleanup_ym', '');

-- 3. Group Class -> Band Hierarchy (Band 1, Band 2, Band 3 -> Students)
CREATE TABLE IF NOT EXISTS `class_bands` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `class_id` INT(11) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `teacher_id` INT(11) NOT NULL,
  `day` ENUM('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_band_class` (`class_id`),
  KEY `idx_band_teacher` (`teacher_id`),
  CONSTRAINT `fk_band_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_band_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `class_members`
  ADD COLUMN IF NOT EXISTS `band_id` INT(11) DEFAULT NULL AFTER `student_id`;

ALTER TABLE `class_members`
  DROP INDEX IF EXISTS `uniq_class_student`,
  ADD UNIQUE INDEX IF NOT EXISTS `uniq_class_student_slot` (`class_id`, `student_id`, `day`, `start_time`),
  ADD INDEX IF NOT EXISTS `idx_cm_band_id` (`band_id`);

-- Add FK fk_cm_band safely if not present
SET @fk_exists := (
  SELECT COUNT(*)
  FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'class_members'
    AND CONSTRAINT_NAME = 'fk_cm_band'
);
SET @sql_fk := IF(@fk_exists = 0,
  'ALTER TABLE `class_members` ADD CONSTRAINT `fk_cm_band` FOREIGN KEY (`band_id`) REFERENCES `class_bands` (`id`) ON DELETE CASCADE',
  'SELECT 1'
);
PREPARE stmt_fk FROM @sql_fk;
EXECUTE stmt_fk;
DEALLOCATE PREPARE stmt_fk;

-- 4. Student Progress Curriculum: Custom Bab & Subbab per Individual Student + % Raport
CREATE TABLE IF NOT EXISTS `progress_chapters` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `class_id` INT(11) NOT NULL,
  `student_id` INT(11) NOT NULL,
  `teacher_id` INT(11) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pc_student_class` (`student_id`, `class_id`),
  KEY `idx_pc_teacher` (`teacher_id`),
  CONSTRAINT `fk_pc_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pc_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pc_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `progress_subchapters` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `chapter_id` INT(11) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
  `notes` TEXT DEFAULT NULL,
  `completed_at` DATE DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ps_chapter` (`chapter_id`),
  CONSTRAINT `fk_ps_chapter` FOREIGN KEY (`chapter_id`) REFERENCES `progress_chapters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Performance indexes on payments & attendances
ALTER TABLE `payments`
  ADD INDEX IF NOT EXISTS `idx_payments_student_period` (`student_id`, `year`, `month`),
  ADD INDEX IF NOT EXISTS `idx_payments_status_period` (`status`, `year`, `month`);
