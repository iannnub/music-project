<?php
// cron/process_alpha.php
// Script CLI / Cron Job untuk kalkulasi Alpha Siswa & Guru harian + pembersihan foto absen guru setiap tanggal 1.
// Rekomendasi jadwal eksekusi: setiap pukul 00:05 WIB (5 0 * * *)

if (php_sapi_name() !== 'cli' && (!isset($_GET['key']) || !defined('CRON_TOKEN'))) {
    require_once __DIR__ . '/../config/database.php';
    if (!isset($_GET['key']) || !hash_equals(CRON_TOKEN, $_GET['key'])) {
        http_response_code(403);
        die("Akses ditolak: Token tidak valid.");
    }
} else {
    require_once __DIR__ . '/../config/database.php';
}

require_once __DIR__ . '/../models/AbsensiModel.php';

date_default_timezone_set('Asia/Jakarta');

try {
    $absensiModel = new AbsensiModel($db);
    $absensiModel->autoProcessAlpha();
    $absensiModel->autoProcessTeacherAlpha();
    $cleanedPhotos = $absensiModel->cleanupMonthlyTeacherPhotos(true);
    echo "[" . date('Y-m-d H:i:s') . "] Kalkulasi alpha selesai. Foto absen guru bulan lalu dibersihkan: {$cleanedPhotos} file.\n";
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] Terjadi kesalahan: " . $e->getMessage() . "\n";
    exit(1);
}
