<?php
require_once '../models/DashboardModel.php';

class DashboardController {
    private $dashboardModel;

    public function __construct($db) {
        $this->dashboardModel = new DashboardModel($db);
    }

    public function index() {
        $user = $_SESSION['user'];

        // Cek Role (Hanya Admin yang punya grafik)
        if ($user['role'] == 'admin') {
            
            // 1. Ambil Data Statistik Angka
            $counts = $this->dashboardModel->getCounts();

            // 2. Ambil Data Grafik Pemasukan (Tahun Ini)
            $incomeData = $this->dashboardModel->getIncomeChart(date('Y'));

            // 3. Ambil Data Grafik Absensi
            $pieData = $this->dashboardModel->getAttendancePie();

            // Load View Admin
            require_once '../views/layouts/header.php';
            require_once '../views/layouts/sidebar.php';
            require_once '../views/layouts/topbar.php';
            
            // Kita inject variable ke view
            require_once '../views/admin/dashboard.php';
            
            require_once '../views/layouts/footer.php';

        } else {
            // Redirect user lain ke dashboard masing-masing jika nyasar
            if($user['role'] == 'guru') header("Location: index.php?page=dashboard_guru");
            if($user['role'] == 'siswa') header("Location: index.php?page=dashboard_siswa");
        }
    }
}
?>