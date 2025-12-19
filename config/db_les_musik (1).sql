-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 19 Des 2025 pada 01.15
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_les_musik`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `deadline` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `assignments`
--

INSERT INTO `assignments` (`id`, `class_id`, `title`, `description`, `deadline`, `created_at`) VALUES
(1, 4, 'Latihan vocal nada rendah', 'testing', '2025-12-09 16:11:00', '2025-12-02 09:11:30'),
(2, 4, 'silahkan tes nada tinggi', 'sda', '2025-12-10 21:49:00', '2025-12-09 14:50:08'),
(3, 5, 'buat video', 'yagitu', '2025-12-27 23:59:00', '2025-12-18 18:14:13'),
(4, 5, 'as', 'as', '2025-12-19 03:22:00', '2025-12-18 18:22:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `attendances`
--

CREATE TABLE `attendances` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('Hadir','Izin','Sakit','Alpha') NOT NULL,
  `photo_proof` varchar(255) DEFAULT NULL,
  `location_lat` varchar(50) DEFAULT NULL,
  `location_long` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `attendances`
--

INSERT INTO `attendances` (`id`, `schedule_id`, `student_id`, `date`, `status`, `photo_proof`, `location_lat`, `location_long`, `created_at`) VALUES
(2, 6, 6, '2025-12-02', 'Hadir', 'absen_6_6_1764666382.jpg', '-7.6611584', '114.0195328', '2025-12-02 09:06:22'),
(3, 7, 6, '2025-12-09', 'Hadir', 'absen_7_6_1765290768.jpg', '-8.2850736', '113.5259343', '2025-12-09 14:32:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `type` enum('private','group') NOT NULL DEFAULT 'private',
  `instrument` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `classes`
--

INSERT INTO `classes` (`id`, `name`, `teacher_id`, `type`, `instrument`, `description`, `created_at`) VALUES
(4, 'Vocal', 5, 'private', 'Vokal', '', '2025-12-02 08:54:56'),
(5, 'lemon band', 11, 'group', 'Band Combo', '', '2025-12-09 14:55:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `class_members`
--

CREATE TABLE `class_members` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `class_members`
--

INSERT INTO `class_members` (`id`, `class_id`, `student_id`, `joined_at`) VALUES
(3, 4, 6, '2025-12-02 08:56:33'),
(4, 5, 7, '2025-12-09 14:55:50'),
(6, 5, 10, '2025-12-18 17:43:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `materials`
--

INSERT INTO `materials` (`id`, `class_id`, `title`, `description`, `video_url`, `created_at`) VALUES
(1, 4, 'Video latihan', 'video latihan pertemuan 1', 'https://www.youtube.com/live/ob6twGkFA18?si=ZgRkjXCt2MYrFTEL', '2025-12-02 09:10:52'),
(2, 5, 'silahkan dipelajari', '', 'https://mcgogopro.vercel.app/', '2025-12-18 18:13:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('Lunas','Belum Lunas') DEFAULT 'Belum Lunas',
  `payment_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `payments`
--

INSERT INTO `payments` (`id`, `student_id`, `admin_id`, `month`, `year`, `start_date`, `end_date`, `amount`, `status`, `payment_date`, `notes`, `created_at`) VALUES
(3, 6, 1, 12, 2025, NULL, NULL, 20000000.00, 'Lunas', '2025-12-09 15:32:22', '', '2025-12-09 14:32:22'),
(4, 7, 1, 12, 2025, NULL, NULL, 200000.00, 'Belum Lunas', '2025-12-09 15:58:21', '', '2025-12-09 14:58:21'),
(6, 10, 1, 12, 2025, '2025-12-08', '2026-01-05', 200000.00, 'Belum Lunas', '2025-12-18 18:44:02', NULL, '2025-12-18 17:44:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `progress_logs`
--

CREATE TABLE `progress_logs` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `topic` varchar(200) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `progress_logs`
--

INSERT INTO `progress_logs` (`id`, `class_id`, `student_id`, `teacher_id`, `date`, `topic`, `notes`, `created_at`) VALUES
(1, 4, 6, 5, '2025-12-02', 'Nada Rendah', 'keriting endong', '2025-12-02 09:09:34'),
(2, 4, 6, 5, '2025-12-09', 'nada tinggi', 'aasada', '2025-12-09 14:49:24'),
(4, 5, 7, 5, '2025-12-09', 'piano', 'mantap', '2025-12-09 15:03:17'),
(5, 5, 10, 11, '2025-12-18', 'sep', 'sep', '2025-12-18 18:11:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `day` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `schedules`
--

INSERT INTO `schedules` (`id`, `class_id`, `day`, `start_time`, `end_time`) VALUES
(6, 4, 'Selasa', '16:00:00', '17:00:00'),
(7, 4, 'Selasa', '21:31:00', '22:31:00'),
(8, 5, 'Rabu', '19:00:00', '20:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `file_proof` varchar(255) DEFAULT NULL,
  `link_proof` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `grade` int(11) DEFAULT 0,
  `teacher_feedback` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `submissions`
--

INSERT INTO `submissions` (`id`, `assignment_id`, `student_id`, `file_proof`, `link_proof`, `notes`, `grade`, `teacher_feedback`, `submitted_at`) VALUES
(1, 1, 6, 'https://youtu.be/c3mJxCZJruE?si=3D-jAkOyMGjHRyMP', NULL, 'pengumpulan untuk tugas 1', 0, NULL, '2025-12-02 09:52:26'),
(2, 2, 6, 'https://www.youtube.com/live/a8BrpEZPz0M?si=uoI-pczCtGDVNPgN', NULL, '', 0, NULL, '2025-12-09 14:51:48'),
(3, 3, 10, 'tugas_3_10_1766081835.png', NULL, '', 0, NULL, '2025-12-18 18:17:15'),
(4, 4, 10, 'tugas_4_10_1766082935.png', 'https://mcgogopro.vercel.app/', 'asa', 0, NULL, '2025-12-18 18:35:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('admin','guru','siswa') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `photo_profile` varchar(255) DEFAULT 'default.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `name`, `role`, `phone`, `photo_profile`, `created_at`) VALUES
(1, 'admin', 'admin@sekolahmusik.com', '$2a$12$MOfZ/xB6Ne.gd9vCfESjROG3Pr9RQZMOdoqVaNGL3NnWO9dPzgiHu', 'Super Admin', 'admin', NULL, 'default.png', '2025-11-27 13:40:33'),
(5, 'yose', 'yose@gmail.com', '$2y$10$YMIGzpK3ra8dE0XvqEDvkewPjajsBuA8lancNiJiGmR6hVsPgtC.S', 'Yose Armando', 'guru', '08716253546', 'default.png', '2025-12-02 08:53:57'),
(6, 'andhine', 'andhine@gmail.com', '$2y$10$Oaayyw0ZSBZ.HtDFFWYFKu1biThM5O4ulG5dTuuZG3oKSIBTtSiIO', 'Andhine', 'siswa', '082132175400', 'default.png', '2025-12-02 08:54:32'),
(7, 'iannnub', 'iann@gmail.com', '$2y$10$Z2WhROPPGlqtDReeEvI2fuenMBrDCnVejGiTbU5ilcQBJ5zly6oiu', 'ian', 'siswa', '0812154543', 'default.png', '2025-12-09 14:54:18'),
(10, 'iannnubb', 'iannnub@gmail.com', '$2y$10$RkdbflzOJ8TXImFjVciAI.aPI2orxaiCS5rUTUGiB/k9NAevx6pre', 'Septian Putra Rachman Hakim', 'siswa', '082132167400', 'default.png', '2025-12-18 17:43:05'),
(11, 'lingga', 'lingga@gmail.com', '$2y$10$18ZHVEz6uCgxTL6aa6v8hOQbD5pziU43PkYfQ1rFQco3/h8ACexrq', 'lingga', 'guru', '081216717641', 'default.png', '2025-12-18 18:10:50');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indeks untuk tabel `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indeks untuk tabel `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indeks untuk tabel `class_members`
--
ALTER TABLE `class_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indeks untuk tabel `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indeks untuk tabel `progress_logs`
--
ALTER TABLE `progress_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indeks untuk tabel `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indeks untuk tabel `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`assignment_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `class_members`
--
ALTER TABLE `class_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `progress_logs`
--
ALTER TABLE `progress_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendances_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `class_members`
--
ALTER TABLE `class_members`
  ADD CONSTRAINT `class_members_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_members_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `progress_logs`
--
ALTER TABLE `progress_logs`
  ADD CONSTRAINT `progress_logs_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `progress_logs_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `progress_logs_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
