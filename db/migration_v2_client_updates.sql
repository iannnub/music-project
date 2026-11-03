-- =====================================================================
-- Migration v2: KakYo Lesson Complete Production Upgrade
-- Compatible with MariaDB 10.4+ / 11.4+ (XAMPP Localhost & InfinityFree)
-- Safe & 100% Idempotent: Preserves all existing production records
-- =====================================================================

-- 1. Brute-Force Login Rate Limiting Table (Required by AuthController)
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `attempted_at` DATETIME NOT NULL,
  `success` TINYINT(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_ip_time` (`username`, `ip_address`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Extend users table for Public Student Registration, Branch Pricing & Tata Tertib Gate
ALTER TABLE `users`
  MODIFY COLUMN `phone` VARCHAR(30) DEFAULT NULL,
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

-- Clean invisible WhatsApp directional/non-breaking Unicode characters in phone numbers
UPDATE `users`
SET `phone` = TRIM(
  REPLACE(
    REPLACE(
      REPLACE(
        REPLACE(`phone`, UNHEX('E280AA'), ''),
      UNHEX('E280AC'), ''),
    UNHEX('C2A0'), ' '),
  UNHEX('E28091'), '-')
)
WHERE `phone` IS NOT NULL;

-- 3. Application Settings Table (Separate Promotional vs Private Student/Teacher Practice Video Link)
CREATE TABLE IF NOT EXISTS `app_settings` (
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `app_settings` (`setting_key`, `setting_value`) VALUES
  ('tiktok_practice_url', ''),
  ('tiktok_practice_title', 'Video Latihan Privat Siswa KakYo Lesson'),
  ('tiktok_practice_desc', 'Tautan khusus video latihan dan evaluasi praktik privat untuk siswa, orang tua, dan guru KakYo Lesson.'),
  ('last_teacher_photo_cleanup_ym', '');

-- 4. Group Class -> Band Hierarchy (Band 1, Band 2, Band 3 -> Students)
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
  ADD INDEX IF NOT EXISTS `idx_cm_band_id` (`band_id`),
  ADD INDEX IF NOT EXISTS `idx_member_day_time` (`student_id`, `day`, `start_time`, `end_time`),
  ADD INDEX IF NOT EXISTS `idx_teacher_day_time` (`class_id`, `day`, `start_time`, `end_time`);

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

-- Migrate legacy Group Class Band placeholder slots (e.g. 'band 1'..'band 7' in class 'Reguler') into class_bands
INSERT INTO `class_bands` (`class_id`, `name`, `teacher_id`, `day`, `start_time`, `end_time`)
SELECT
  cm.class_id,
  CONCAT('Band ', TRIM(SUBSTRING(u.name, 5))) AS band_title,
  c.teacher_id,
  cm.day,
  cm.start_time,
  cm.end_time
FROM `class_members` cm
JOIN `classes` c ON cm.class_id = c.id
JOIN `users` u ON cm.student_id = u.id
WHERE c.type = 'group'
  AND cm.band_id IS NULL
  AND LOWER(TRIM(u.name)) REGEXP '^band[[:space:]]+[0-9]+$'
  AND NOT EXISTS (
    SELECT 1 FROM `class_bands` cb
    WHERE cb.class_id = cm.class_id
      AND LOWER(cb.name) = LOWER(CONCAT('Band ', TRIM(SUBSTRING(u.name, 5))))
  )
ORDER BY CAST(TRIM(SUBSTRING(u.name, 5)) AS UNSIGNED) ASC;

UPDATE `class_members` cm
JOIN `classes` c ON cm.class_id = c.id
JOIN `users` u ON cm.student_id = u.id
JOIN `class_bands` cb
  ON cb.class_id = cm.class_id
 AND LOWER(cb.name) = LOWER(CONCAT('Band ', TRIM(SUBSTRING(u.name, 5))))
SET cm.band_id = cb.id
WHERE c.type = 'group'
  AND cm.band_id IS NULL
  AND LOWER(TRIM(u.name)) REGEXP '^band[[:space:]]+[0-9]+$';

-- 5. Student Progress Curriculum: Custom Bab & Subbab per Individual Student + % Raport
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

-- 6. Clean duplicate payments FK/index & add composite performance indexes
ALTER TABLE `payments`
  DROP FOREIGN KEY IF EXISTS `payments_ibfk_2`,
  DROP INDEX IF EXISTS `admin_id_2`;

ALTER TABLE `payments`
  ADD INDEX IF NOT EXISTS `idx_payments_student_period` (`student_id`, `year`, `month`),
  ADD INDEX IF NOT EXISTS `idx_payments_status_period` (`status`, `year`, `month`);

ALTER TABLE `attendances`
  ADD INDEX IF NOT EXISTS `idx_att_student_date` (`student_id`, `date`),
  ADD INDEX IF NOT EXISTS `idx_att_sched_date` (`schedule_id`, `date`),
  ADD INDEX IF NOT EXISTS `idx_att_status_date` (`status`, `date`);

ALTER TABLE `teacher_attendances`
  ADD INDEX IF NOT EXISTS `idx_tatt_teacher_date` (`teacher_id`, `date`),
  ADD INDEX IF NOT EXISTS `idx_tatt_sched_date` (`schedule_id`, `date`);
