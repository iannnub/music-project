<?php
date_default_timezone_set('Asia/Jakarta');
session_start();

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$baseDir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

if (!defined('BASE_URL')) {
    define('BASE_URL', $protocol . $host . $baseDir);
}

require_once '../config/database.php';
require_once '../helpers/CsrfHelper.php';

// Auto-process student Alpha (Ditolak) status for missed slots
require_once '../models/AbsensiModel.php';
(new AbsensiModel($db))->autoProcessAlpha();
(new AbsensiModel($db))->autoProcessTeacherAlpha();

$timeout_duration = 1800;
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration)) {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "index.php?page=auth&timeout=true");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

CsrfHelper::generateToken();

$page = isset($_GET['page']) ? $_GET['page'] : 'auth';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Check if student has not filled parent_name or phone. If so, force redirect to profile page.
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'siswa') {
    if (empty($_SESSION['user']['parent_name']) || empty($_SESSION['user']['phone'])) {
        if ($page !== 'profile' && $page !== 'auth') {
            $_SESSION['flash'] = [
                'status' => 'warning',
                'title'  => 'Lengkapi Profil',
                'msg'    => 'Harap isi Nama Orang Tua dan Nomor WhatsApp terlebih dahulu.'
            ];
            header("Location: index.php?page=profile");
            exit;
        }
    }
}

switch ($page) {

    // --------------------------- auth ---------------------------
    case 'auth':
        require_once '../controllers/AuthController.php';
        $controller = new AuthController($db);

        if ($action == 'login_process') {
            $controller->login_process();
        } elseif ($action == 'logout') {
            $controller->logout();
        } else {
            $controller->login_view();
        }
        break;

    case 'profile':
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?page=auth");
            exit;
        }

        require_once '../controllers/ProfileController.php';
        $controller = new ProfileController($db);

        if ($action == 'update') $controller->update_profile();
        elseif ($action == 'password') $controller->change_password();
        else $controller->index();
        break;

    // ... lanjut ke case dashboard_admin dll ...

    // --------------------------- dashboard ---------------------------

    case 'dashboard':
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?page=auth");
            exit;
        }

        require_once '../controllers/DashboardController.php';
        $controller = new DashboardController($db);
        $controller->index();
        break;


    // --------------------------- guru ---------------------------

    case 'guru':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }
        // UBAH: Gunakan GuruController, bukan UserController
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);

        if ($action == 'store') {
            $controller->store();
        } elseif ($action == 'update') {
            $controller->update();
        } elseif ($action == 'delete') {
            $controller->delete();
        } else {
            // Pastikan di GuruController lo ada method index() untuk list guru
            $controller->indexGuruAdmin();
        }
        break;



    // --------------------------- siswa ---------------------------

    case 'siswa':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);

        if ($action == 'store') {
            $controller->store();
        } elseif ($action == 'update') {
            $controller->update();
        } elseif ($action == 'delete') {
            $controller->delete();
        } elseif ($action == 'update_jadwal') {
            $controller->update_jadwal();
        } elseif ($action == 'add_schedule_item') { // <--- TAMBAH INI
            $controller->add_schedule_item();
        } elseif ($action == 'delete_schedule_item') { // <--- TAMBAH INI
            $controller->delete_schedule_item();
        } else {
            $controller->index();
        }
        break;

    case 'siswa_manage_jadwal': // <--- TAMBAHAN: Biar halaman kalender gak blank
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);
        $controller->manage_jadwal();
        break;


    // --------------------------- kelas ---------------------------

    case 'kelas':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }

        require_once '../controllers/KelasController.php';
        $controller = new KelasController($db);

        // Routing Actions
        if ($action == 'store') {
            $controller->store();
        } elseif ($action == 'update') { // <--- TAMBAHAN BARU
            $controller->update();
        } elseif ($action == 'detail') {
            $id = $_GET['id']; // Ambil ID Kelas dari URL
            $controller->detail($id);
        } elseif ($action == 'add_member') {
            $controller->add_member();
        } elseif ($action == 'delete_member') {
            $controller->delete_member();
        } elseif ($action == 'delete') {
            $controller->delete();
        } else {
            $controller->index();
        }
        break;

    // --------------------------- jadwal ---------------------------

    case 'jadwal':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }

        require_once '../controllers/JadwalController.php';
        $controller = new JadwalController($db);

        if ($action == 'store') {
            $controller->store();
        } elseif ($action == 'update') { // <--- TAMBAHKAN INI
            $controller->update();
        } elseif ($action == 'delete') {
            $controller->delete();
        } else {
            $controller->index();
        }
        break;

    // --------------------------- pembayaran ---------------------------

    case 'pembayaran':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/PembayaranController.php';
        $controller = new PembayaranController($db);

        if ($action == 'store') $controller->store();
        elseif ($action == 'update') $controller->update();
        elseif ($action == 'delete') $controller->delete();
        elseif ($action == 'delete_all') $controller->delete_all_by_student();
        elseif ($action == 'cetak') $controller->cetak();
        else $controller->index();
        break;

    case 'pembayaran_detail':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/PembayaranController.php';
        $controller = new PembayaranController($db);
        $controller->detail(); // Memanggil tampilan Kartu SPP Digital
        break;

    // --------------------------- dashboard_siswa ---------------------------

    case 'dashboard_siswa':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'siswa') {
            header("Location: index.php?page=auth");
            exit;
        }

        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);

        // --- Routing Action Baru ---
        if ($action == 'proses_absen') {
            $controller->proses_absen();
        } else {
            $controller->index();
        }
        break;


    // --------------------------- siswa_materi ---------------------------
    case 'siswa_materi':
        // Cek Login Siswa
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'siswa') {
            header("Location: index.php?page=auth");
            exit;
        }

        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);
        $controller->materi();
        break;


    // --------------------------- siswa_tugas ---------------------------
    case 'siswa_tugas':
        // Cek Login Siswa
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'siswa') {
            header("Location: index.php?page=auth");
            exit;
        }

        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);

        // Cek aksi: Apakah mau upload atau cuma lihat?
        if ($action == 'upload') {
            $controller->upload_tugas();
        } elseif ($action == 'delete_submission') {
            $controller->hapus_setoran();
        } else {
            $controller->tugas();
        }
        break;


    // --------------------------- siswa_progress ---------------------------
    case 'siswa_progress':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'siswa') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);
        $controller->progress();
        break;

    // --------------------------- siswa_absensi ---------------------------
    case 'siswa_absensi':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'siswa') {
            header("Location: index.php");
            exit;
        }
        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);
        $controller->riwayat_absensi();
        break;


    // --------------------------- siswa_bayar ---------------------------
    case 'siswa_bayar':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'siswa') {
            header("Location: index.php");
            exit;
        }
        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);
        $controller->pembayaran();
        break;


    // ... case dashboard_siswa selesai ...

    // --------------------------- dashboard_guru ---------------------------
    case 'dashboard_guru':
        // Cek Login & Role Guru
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }

        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        $controller->index();
        break;

    // ... routing guru lainnya ...

    case 'guru_validasi':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);

        if ($action == 'proses') {
            $controller->proses_validasi();
        } else {
            $controller->validasi_absen();
        }
        break;

    // ... case guru_validasi selesai ...


    case 'guru_riwayat':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        $controller->riwayat_absen(); // Memanggil daftar siswa unik
        break;

    case 'guru_riwayat_detail':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);

        if ($action == 'update_status') {
            $controller->proses_update_absensi();
        } else {
            $controller->riwayat_detail();
        }
        break;

    case 'guru_progress':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'guru') {
            header("Location: index.php");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        $controller->input_progress();
        break;

    case 'guru_progress_detail':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'guru') {
            header("Location: index.php");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        $controller->input_progress_detail();
        break;


    // ... case guru_progress_store selesai ...

    // ROUTING AKADEMIK GURU
    case 'guru_materi':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);

        if ($action == 'store') {
            $controller->materi_store();
        } elseif ($action == 'update') { // <--- TAMBAHKAN INI
            $controller->materi_update();
        } elseif ($action == 'delete') {
            $controller->materi_delete();
        } else {
            $controller->materi();
        }
        break;

    case 'guru_tugas':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);

        if ($action == 'store') {
            $controller->tugas_store();
        } elseif ($action == 'update') {
            $controller->tugas_update();
        } elseif ($action == 'delete') {
            $controller->tugas_delete();
        } else {
            $controller->tugas();
        }
        break;

    // ... case guru_tugas selesai ...

    case 'guru_tugas_detail':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        $controller->tugas_detail();
        break;

    case 'guru_tugas_acc':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        $controller->tugas_acc(); // Memanggil method baru di controller
        break;

    case 'guru_tugas_nilai':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        $controller->tugas_nilai();
        break;

    // --- PENAMBAHAN ROUTE UNTUK EDIT & DELETE PROGRESS ---

    case 'guru_progress_store':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'guru') {
            header("Location: index.php");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        $controller->store_progress();
        break;

    case 'guru_progress_update': // <--- TAMBAHAN UNTUK EDIT
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'guru') {
            header("Location: index.php");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        $controller->progress_update();
        break;

    case 'guru_progress_delete': // <--- TAMBAHAN UNTUK DELETE
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'guru') {
            header("Location: index.php");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);
        $controller->progress_delete();
        break;

    case 'guru_absen':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'guru') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/GuruController.php';
        $controller = new GuruController($db);

        if ($action == 'submit') {
            $controller->proses_absen_guru(); // Pastikan method ini ada di Controller
        } else {
            $controller->view_absen_guru();   // Pastikan method ini ada di Controller
        }
        break;
    // ... case pembayaran selesai ...

    // ROUTING LAPORAN
    case 'laporan_keuangan':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/LaporanController.php';
        $controller = new LaporanController($db);
        $controller->keuangan();
        break;

    case 'laporan_absensi':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/LaporanController.php';
        $controller = new LaporanController($db);
        $controller->absensi();
        break;

    case 'laporan_absensi_guru':
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'guru'])) {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/LaporanController.php';
        $controller = new LaporanController($db);
        $controller->absensi_guru();
        break;

    case 'siswa_cetak_raport':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'siswa') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../controllers/SiswaController.php';
        $controller = new SiswaController($db);
        $controller->cetak_raport();
        break;

    default:
        header("Location: index.php?page=dashboard");
        break;
}
