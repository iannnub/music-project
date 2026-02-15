<?php
require_once '../models/DashboardModel.php';

class DashboardController {
    private $dashboardModel;

    public function __construct($db) {
        $this->dashboardModel = new DashboardModel($db);
    }

    public function index() {
        // Keamanan: Pastikan user sudah login (Cek Integritas Sesi)
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?page=login");
            exit();
        }

        $user = $_SESSION['user'];
        $role = $user['role'];

        // FASE 4: CROSS-ROLE ALIGNMENT (Standardisasi Layout & Timezone)
        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';

        // FASE 3: DATA INTEGRITY CHECK (Pipa Data Berdasarkan Role)
        if ($role == 'admin') {
            // Data Admin (Sudah Fix Bug Payment Status di Fase 1 & 2)
            $counts = $this->dashboardModel->getCounts();
            $incomeData = $this->dashboardModel->getIncomeChart(date('Y'));
            $pieData = $this->dashboardModel->getAttendancePie();
            $recentActivities = $this->dashboardModel->getRecentActivities(5);

            require_once '../views/admin/dashboard.php';

        } elseif ($role == 'guru') {
            // Data Guru (Memastikan semua variabel di View terisi)
            $total_validasi = $this->dashboardModel->getPendingValidation($user['id']);
            $jadwal_mengajar = $this->dashboardModel->getJadwalGuru($user['id']);
            
            // Tambahkan default value atau panggil model untuk stats guru agar tidak error di View
            $total_kelas = count($jadwal_mengajar); 
            $total_siswa = 0; // Bisa dikembangkan dengan method getTotalSiswaGuru di Model
            $global_percent = 0; // Placeholder progres tugas
            $urgent_tasks = [];  // Placeholder notifikasi tugas

            require_once '../views/guru/dashboard.php';

        } elseif ($role == 'siswa') {
            // Data Siswa (Alignment untuk Countdown & Status Bayar)
            $map_hari = [
                'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 
                'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
            ];
            $hari_ini = $map_hari[date('l')];

            $is_lunas = $this->dashboardModel->checkPaymentStatus($user['id'], date('m'), date('Y'));
            $next_class = $this->dashboardModel->getNextClass($user['id'], $hari_ini);
            $jadwal_saya = $this->dashboardModel->getJadwalSiswa($user['id']);
            $last_progress = $this->dashboardModel->getLastProgress($user['id']);
            $tugas_pending = []; // Siapkan variabel ini agar view siswa tidak error

            require_once '../views/siswa/dashboard.php';
        }

        // Penutup Layout
        require_once '../views/layouts/footer.php';
    }
}