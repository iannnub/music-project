<?php
require_once '../models/LaporanModel.php';
require_once '../models/KelasModel.php';
require_once '../models/AbsensiModel.php';

class LaporanController {
    private $db;
    private $laporanModel;
    private $kelasModel;
    private $absensiModel;

    public function __construct($db) {
        $this->db = $db;
        $this->laporanModel = new LaporanModel($db);
        $this->kelasModel = new KelasModel($db);
        $this->absensiModel = new AbsensiModel($db);
    }

    public function keuangan() {
        $bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
        $tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

        $laporan = $this->laporanModel->getLaporanKeuangan($bulan, $tahun);
        $total_pemasukan = 0;
        foreach ($laporan as $row) {
            $total_pemasukan += $row['amount'];
        }

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/admin/laporan/keuangan.php';
        require_once '../views/layouts/footer.php';
    }

    public function absensi() {
        $dataKelas = $this->kelasModel->getAll();
        $class_id = isset($_GET['class_id']) ? $_GET['class_id'] : '';
        $bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('n');
        $tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

        $laporan = [];
        $nama_kelas = "";

        if ($class_id) {
            $laporan = $this->laporanModel->getLaporanAbsensi($class_id, $bulan, $tahun);
            $nama_kelas = $this->laporanModel->getNamaKelas($class_id);
        }

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/admin/laporan/absensi.php';
        require_once '../views/layouts/footer.php';
    }

    public function absensi_guru() {
        $stmt = $this->db->prepare("SELECT id, name FROM users WHERE role = 'guru' ORDER BY name ASC");
        $stmt->execute();
        $dataGuru = $stmt->fetchAll();

        $user_role = $_SESSION['user']['role'];
        if ($user_role === 'guru') {
            $teacher_id = $_SESSION['user']['id'];
        } else {
            $teacher_id = isset($_GET['teacher_id']) ? $_GET['teacher_id'] : '';
        }
        $bulan   = isset($_GET['bulan']) ? $_GET['bulan'] : date('n');
        $tahun   = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
        $tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

        $laporan = $this->laporanModel->getLaporanAbsensiGuru($teacher_id, $bulan, $tahun, $tanggal);
        $adminSchedules = ($user_role === 'admin') ? $this->absensiModel->getAllActiveSchedulesForAdmin() : [];

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/admin/laporan/absensi_guru.php';
        require_once '../views/layouts/footer.php';
    }

    // Konfirmasi Admin untuk guru terlambat / lupa absen (Gaji Penuh atau Sesuai Jam Masuk Aktual)
    public function confirm_absensi_guru() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            http_response_code(403);
            die("Akses ditolak.");
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Metode request tidak diizinkan. Gunakan POST.");
        }
        require_once '../helpers/CsrfHelper.php';
        if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token tidak valid");
        }

        $ok = $this->absensiModel->adminConfirmTeacherAttendance([
            'attendance_id' => $_POST['attendance_id'] ?? 0,
            'schedule_id'   => $_POST['schedule_id'] ?? 0,
            'date'          => $_POST['date'] ?? date('Y-m-d'),
            'check_in_time' => $_POST['check_in_time'] ?? '',
            'salary_mode'   => $_POST['salary_mode'] ?? 'full'
        ]);

        if ($ok) {
            $_SESSION['flash'] = [
                'status' => 'success',
                'title'  => 'Presensi Guru Dikonfirmasi',
                'msg'    => 'Data kehadiran dan gaji sesi guru telah diperbarui oleh Admin.'
            ];
        } else {
            $_SESSION['flash'] = [
                'status' => 'error',
                'title'  => 'Gagal Konfirmasi',
                'msg'    => 'Pastikan jadwal, tanggal, dan jam masuk guru sudah terisi dengan benar.'
            ];
        }

        $q = http_build_query(array_filter([
            'page'       => 'laporan_absensi_guru',
            'teacher_id' => $_POST['return_teacher_id'] ?? '',
            'bulan'      => $_POST['return_bulan'] ?? date('n'),
            'tahun'      => $_POST['return_tahun'] ?? date('Y'),
            'tanggal'    => $_POST['return_tanggal'] ?? ''
        ], function ($v) { return $v !== ''; }));

        header("Location: index.php?" . $q);
        exit;
    }
}
?>
