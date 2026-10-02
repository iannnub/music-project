<?php
// config/database.php
// Konfigurasi Database PDO untuk KakYo Lesson

$appConfig = file_exists(__DIR__ . '/app.php') ? require __DIR__ . '/app.php' : [];
$appDebug  = $appConfig['app_debug'] ?? false;

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', (bool)$appDebug);
}

if (!APP_DEBUG) {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    ini_set('display_errors', 1);
}

$host     = "localhost";
$dbname   = "db_les_musik"; // Sesuaikan dengan nama database saat import di phpMyAdmin
$username = "root";          // Default XAMPP/Laragon: root
$password = "";              // Default XAMPP: kosong (""), jika ada password silakan diisi

try {
    $db = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    http_response_code(500);

    if ($appDebug === true) {
        die("Koneksi database gagal: " . htmlspecialchars($e->getMessage()) . "<br><small>Pastikan MySQL di XAMPP/Laragon sudah Start dan database sudah di-import.</small>");
    } else {
        die("Layanan sedang mengalami gangguan. Silakan coba beberapa saat lagi.");
    }
}

if (!defined('CRON_TOKEN')) {
    define('CRON_TOKEN', 'kakyo_cron_secret_2026');
}
?>
