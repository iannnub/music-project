<?php
require_once '../models/DashboardModel.php';

class DashboardController {
    private $dashboardModel;

    public function __construct($db) {
        $this->dashboardModel = new DashboardModel($db);
    }

    public function index() {
        // Keamanan: Pastikan user sudah login
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?page=login");
            exit();
        }

        $user = $_SESSION['user'];
        $role = $user['role'];

        // 1. Persiapan Layout (Header, Sidebar, Topbar dipanggil sekali di sini)
        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';

        // 2. LOGIKA DISTRIBUSI DATA (ORCHESTRATION)
        if ($role == 'admin') {
            // Data untuk Admin (Fase 1)
            $counts = $this->dashboardModel->getCounts();
            $incomeData = $this->dashboardModel->getIncomeChart(date('Y'));
            $pieData = $this->dashboardModel->getAttendancePie();
            $recentActivities = $this->dashboardModel->getRecentActivities(5);

            require_once '../views/admin/dashboard.php';

        } elseif ($role == 'guru') {
            // Data untuk Guru (Fase 2)
            $total_validasi = $this->dashboardModel->getPendingValidation($user['id']);
            $jadwal_mengajar = $this->dashboardModel->getJadwalGuru($user['id']);
            
            // Catatan SI: Variabel lain (total_siswa, dll) bisa ditambahkan di Model dulu jika ingin ditampilkan
            require_once '../views/guru/dashboard.php';

        } elseif ($role == 'siswa') {
            // Data untuk Siswa (Fase 3 & Countdown)
            $map_hari = [
                'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 
                'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
            ];
            $hari_ini = $map_hari[date('l')];

            $is_lunas = $this->dashboardModel->checkPaymentStatus($user['id'], date('m'), date('Y'));
            $next_class = $this->dashboardModel->getNextClass($user['id'], $hari_ini);
            $jadwal_saya = $this->dashboardModel->getJadwalSiswa($user['id']);
            $last_progress = $this->dashboardModel->getLastProgress($user['id']);

            require_once '../views/siswa/dashboard.php';
        }

        // 3. Penutup Layout
        require_once '../views/layouts/footer.php';
    }
}
?>