<?php
// cron/process_alpha.php
// Script CLI / Cron Job untuk kalkulasi Alpha Siswa dan Guru harian.
// Rekomendasi jadwal eksekusi: setiap pukul 00:01 WIB (0 0 * * *)

if (php_sapi_name() !== 'cli' && (!isset($_GET['key']) || !defined('CRON_TOKEN'))) {
    // Jika dipanggil via browser/HTTP, harus memiliki token rahasia
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
    echo "[" . date('Y-m-d H:i:s') . "] Kalkulasi alpha siswa dan guru selesai diproses.\n";
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] Terjadi kesalahan: " . $e->getMessage() . "\n";
    exit(1);
}
