perubahan website pada
1. Perubahan Struktur Database (SQL)
-- 1. Mengubah nama kolom link pada tabel users
ALTER TABLE `users` CHANGE `gdrive_link` `ig_link` VARCHAR(255) NULL;

-- 2. Membuat tabel baru untuk absensi dan penggajian guru
CREATE TABLE `teacher_attendances` (
    `id` INT(11) PRIMARY KEY AUTO_INCREMENT,
    `teacher_id` INT(11) NOT NULL,
    `date` DATE NOT NULL,
    `check_in_time` TIME NOT NULL,
    `photo_proof` VARCHAR(255) NOT NULL,
    `latitude` VARCHAR(50) NOT NULL,
    `longitude` VARCHAR(50) NOT NULL,
    `base_salary` DECIMAL(10,2) DEFAULT 30000.00,
    `penalty_amount` DECIMAL(10,2) DEFAULT 0.00,
    `total_salary` DECIMAL(10,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

2. Update Model (UserModel.php)
3. Update Controller (ProfileController.php)
4. Update Tampilan Profil (views/profile/index.php)
5. Update AbsensiModel.php
6. Form Absensi Guru (views/guru/absen.php)