<?php
date_default_timezone_set('Asia/Jakarta');

// Security Headers (OWASP ASVS V14.4)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Hardened Session Configuration (OWASP ASVS V3.4)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

$protocol = $isHttps ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseDir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

if (!defined('BASE_URL')) {
    define('BASE_URL', $protocol . $host . $baseDir);
}

require_once '../config/database.php';
require_once '../helpers/CsrfHelper.php';
require_once '../models/AbsensiModel.php';
require_once '../models/SettingsModel.php';

$timeout_duration = 1800;
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration)) {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "index.php?page=auth&timeout=true");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

CsrfHelper::generateToken();

// Auto-reset foto absen guru setiap tanggal 1 (hanya dieksekusi 1x per bulan via app_settings)
$globalAbsensiCleanup = new AbsensiModel($db);
$globalAbsensiCleanup->cleanupMonthlyTeacherPhotos(false);

$page   = $_POST['page'] ?? $_GET['page'] ?? 'auth';
$action = $_POST['action'] ?? $_GET['action'] ?? 'index';

// Validasi kelengkapan profil dasar siswa lama
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'siswa') {
    if (empty($_SESSION['user']['parent_name']) || empty($_SESSION['user']['phone'])) {
        if (!in_array($page, ['profile', 'auth', 'agree_tatib'], true)) {
            $_SESSION['flash'] = [
                'status' => 'warning',
                'title'  => 'Lengkapi Profil',
                'msg'    => 'Harap isi Nama Wali dan Nomor WhatsApp terlebih dahulu.'
            ];
            header("Location: index.php?page=profile");
            exit;
        }
    }
}

switch ($page) {

    // --------------------------- auth & registrasi siswa baru ---------------------------
    case 'auth':
        require_once '../controllers/AuthController.php';
        $controller = new AuthController($db);

        if ($action === 'login_process') {
            $controller->login_process();
        } elseif ($action === 'logout') {
            $controller->logout();
        } else {
            $controller->login_view();
        }
        break;

    case 'register':
        require_once '../controllers/AuthController.php';
        $controller = new AuthController($db);

        if ($action === 'register_process') {
            $controller->register_process();
        } else {
            $controller->register_view();
        }
        break;

    case 'agree_tatib':
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/AuthController.php';
        $controller = new AuthController($db);
        $controller->agree_tatib();
        break;

    // --------------------------- profile ---------------------------
    case 'profile':
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/ProfileController.php';
        $controller = new ProfileController($db);

        if ($action === 'update') $controller->update_profile();
        elseif ($action === 'password') $controller->change_password();
        else $controller->index();
        break;

    // --------------------------- dashboard & admin settings ---------------------------
    case 'dashboard':
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/DashboardController.php';
        $controller = new DashboardController($db);
        $controller->index();
        break;

    case 'admin_settings':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/DashboardController.php';
        $controller = new DashboardController($db);
        $controller->saveSettings();
        break;

    // --------------------------- guru (admin CRUD) ---------------------------
    case 'guru':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);

        if ($action === 'store') {
            $controller->store();
        } elseif ($action === 'update') {
            $controller->update();
        } elseif ($action === 'delete') {
            $controller->delete();
        } else {
            $controller->indexGuruAdmin();
        }
        break;

    // --------------------------- siswa (admin CRUD) ---------------------------
    case 'siswa':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);

        if ($action === 'store') {
            $controller->store();
        } elseif ($action === 'update') {
            $controller->update();
        } elseif ($action === 'delete') {
            $controller->delete();
        } elseif ($action === 'update_jadwal') {
            $controller->update_jadwal();
        } elseif ($action === 'add_schedule_item') {
            $controller->add_schedule_item();
        } elseif ($action === 'delete_schedule_item') {
            $controller->delete_schedule_item();
        } else {
            $controller->index();
        }
        break;

    case 'siswa_manage_jadwal':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);
        $controller->manage_jadwal();
        break;

    // --------------------------- kelas & manajemen band ---------------------------
    case 'kelas':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }

        require_once '../controllers/KelasController.php';
        $controller = new KelasController($db);

        if ($action === 'store') {
            $controller->store();
        } elseif ($action === 'update') {
            $controller->update();
        } elseif ($action === 'detail') {
            $id = (int)($_GET['id'] ?? 0);
            $controller->detail($id);
        } elseif ($action === 'add_member') {
            $controller->add_member();
        } elseif ($action === 'delete_member') {
            $controller->delete_member();
        } elseif ($action === 'store_band') {
            $controller->store_band();
        } elseif ($action === 'update_band') {
            $controller->update_band();
        } elseif ($action === 'delete_band') {
            $controller->delete_band();
        } elseif ($action === 'add_band_member') {
            $controller->add_band_member();
        } elseif ($action === 'delete') {
            $controller->delete();
        } else {
            $controller->index();
        }
        break;

    // --------------------------- jadwal ---------------------------
    case 'jadwal':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/JadwalController.php';
        $controller = new JadwalController($db);

        if ($action === 'store') {
            $controller->store();
        } elseif ($action === 'update') {
            $controller->update();
        } elseif ($action === 'delete') {
            $controller->delete();
        } else {
            $controller->index();
        }
        break;

    // --------------------------- pembayaran ---------------------------
    case 'pembayaran':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/PembayaranController.php';
        $controller = new PembayaranController($db);

        if ($action === 'store') $controller->store();
        elseif ($action === 'update') $controller->update();
        elseif ($action === 'delete') $controller->delete();
        elseif ($action === 'delete_all') $controller->delete_all_by_student();
        elseif ($action === 'cetak') $controller->cetak();
        else $controller->index();
        break;

    case 'pembayaran_detail':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/PembayaranController.php';
        $controller = new PembayaranController($db);
        $controller->detail();
        break;

    // --------------------------- area siswa ---------------------------
    case 'dashboard_siswa':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);

        if ($action === 'proses_absen') {
            $controller->proses_absen();
        } else {
            $controller->index();
        }
        break;

    case 'siswa_materi':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);
        $controller->materi();
        break;

    // Tugas lama telah diganti dengan Video Latihan TikTok
    case 'siswa_tugas':
    case 'siswa_tiktok':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);
        $controller->tiktok();
        break;

    case 'siswa_tatib':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);
        $controller->tata_tertib();
        break;

    case 'siswa_progress':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);
        $controller->progress();
        break;

    case 'siswa_absensi':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);
        $controller->riwayat_absensi();
        break;

    case 'siswa_bayar':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);
        $controller->pembayaran();
        break;

    case 'siswa_cetak_raport':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);
        $controller->cetak_raport();
        break;

    // --------------------------- area guru ---------------------------
    case 'dashboard_guru':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        $controller->index();
        break;

    case 'guru_validasi':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);

        if ($action === 'proses') {
            $controller->proses_validasi();
        } else {
            $controller->validasi_absen();
        }
        break;

    case 'guru_riwayat':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        $controller->riwayat_absen();
        break;

    case 'guru_riwayat_detail':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);

        if ($action === 'update_status') {
            $controller->proses_update_absensi();
        } else {
            $controller->riwayat_detail();
        }
        break;

    case 'guru_progress':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        $controller->input_progress();
        break;

    case 'guru_progress_detail':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        $controller->input_progress_detail();
        break;

    case 'guru_bab':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        if ($action === 'store_chapter') $controller->store_chapter();
        elseif ($action === 'update_chapter') $controller->update_chapter();
        elseif ($action === 'delete_chapter') $controller->delete_chapter();
        elseif ($action === 'store_subchapter') $controller->store_subchapter();
        elseif ($action === 'toggle_subchapter') $controller->toggle_subchapter();
        elseif ($action === 'update_subchapter') $controller->update_subchapter();
        elseif ($action === 'delete_subchapter') $controller->delete_subchapter();
        else header("Location: index.php?page=guru_progress");
        break;

    case 'guru_progress_store':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        $controller->store_progress();
        break;

    case 'guru_progress_update':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        $controller->progress_update();
        break;

    case 'guru_progress_delete':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        $controller->progress_delete();
        break;

    case 'guru_materi':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);

        if ($action === 'store') {
            $controller->materi_store();
        } elseif ($action === 'update') {
            $controller->materi_update();
        } elseif ($action === 'delete') {
            $controller->materi_delete();
        } else {
            $controller->materi();
        }
        break;

    // Route tugas guru dinonaktifkan dan dialihkan karena diganti dengan link TikTok Admin
    case 'guru_tugas':
    case 'guru_tugas_detail':
    case 'guru_tugas_acc':
    case 'guru_tugas_nilai':
        header("Location: index.php?page=dashboard_guru");
        exit;

    case 'guru_tiktok':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        $controller->tiktok();
        break;

    case 'guru_absen':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);

        if ($action === 'submit') {
            $controller->proses_absen_guru();
        } else {
            $controller->view_absen_guru();
        }
        break;

    // --------------------------- laporan ---------------------------
    case 'laporan_keuangan':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/LaporanController.php';
        $controller = new LaporanController($db);
        $controller->keuangan();
        break;

    case 'laporan_absensi':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/LaporanController.php';
        $controller = new LaporanController($db);
        $controller->absensi();
        break;

    case 'laporan_absensi_guru':
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'guru'], true)) {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/LaporanController.php';
        $controller = new LaporanController($db);
        if ($action === 'confirm_attendance') {
            $controller->confirm_absensi_guru();
        } else {
            $controller->absensi_guru();
        }
        break;

    case 'admin_process_alpha':
    case 'process-alpha':
        $is_admin = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
        $cron_key = $_GET['key'] ?? '';
        $valid_cron = (!empty($cron_key) && defined('CRON_TOKEN') && hash_equals(CRON_TOKEN, $cron_key));

        if (!$is_admin && !$valid_cron) {
            header("Location: index.php?page=auth");
            exit;
        }

        $absensiModel = new AbsensiModel($db);
        $absensiModel->autoProcessAlpha();
        $absensiModel->autoProcessTeacherAlpha();
        $cleaned = $absensiModel->cleanupMonthlyTeacherPhotos(false);

        if ($is_admin) {
            $_SESSION['flash'] = [
                'status' => 'success',
                'title'  => 'Proses Selesai',
                'msg'    => 'Kalkulasi alpha siswa & guru selesai dijalankan.'
            ];
            header("Location: index.php?page=dashboard");
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'cleaned_photos' => $cleaned, 'message' => 'Kalkulasi alpha selesai.']);
            exit;
        }
        break;

    default:
        header("Location: index.php?page=dashboard");
        break;
}
