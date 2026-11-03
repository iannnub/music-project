<?php
require_once '../models/DashboardModel.php';
require_once '../models/SettingsModel.php';

class DashboardController {
    private $db;
    private $dashboardModel;
    private $settingsModel;

    public function __construct($db) {
        $this->db = $db;
        $this->dashboardModel = new DashboardModel($db);
        $this->settingsModel = new SettingsModel($db);
    }

    public function index() {
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?page=auth");
            exit();
        }

        $user = $_SESSION['user'];
        $role = $user['role'];

        if ($role === 'guru') {
            header("Location: index.php?page=dashboard_guru");
            exit();
        } elseif ($role === 'siswa') {
            header("Location: index.php?page=dashboard_siswa");
            exit();
        }

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';

        if ($role === 'admin') {
            $counts = $this->dashboardModel->getCounts();
            $incomeData = $this->dashboardModel->getIncomeChart(date('Y'));
            $pieData = $this->dashboardModel->getAttendancePie();
            $recentActivities = $this->dashboardModel->getRecentActivities(6);
            $appSettings = $this->settingsModel->getAllMap();

            require_once '../views/admin/dashboard.php';
        }

        require_once '../views/layouts/footer.php';
    }

    // Simpan Pengaturan Link TikTok Latihan Global oleh Admin
    public function saveSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=dashboard");
            exit;
        }
        require_once '../helpers/CsrfHelper.php';
        if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token tidak valid");
        }

        $tiktokUrl   = trim($_POST['tiktok_practice_url'] ?? '');
        $tiktokTitle = trim($_POST['tiktok_practice_title'] ?? 'Video Latihan Privat Siswa KakYo Lesson');
        $tiktokDesc  = trim($_POST['tiktok_practice_desc'] ?? '');

        if ($tiktokUrl !== '') {
            if (!preg_match('#^https?://#i', $tiktokUrl)) {
                $tiktokUrl = 'https://' . $tiktokUrl;
            }
            if (!filter_var($tiktokUrl, FILTER_VALIDATE_URL)) {
                $_SESSION['flash'] = [
                    'status' => 'warning',
                    'title'  => 'Tautan Tidak Valid',
                    'msg'    => 'Masukkan URL TikTok yang valid (contoh: https://www.tiktok.com/@akun_latihan/video/...).'
                ];
                header("Location: index.php?page=dashboard");
                exit;
            }
        }

        $this->settingsModel->set('tiktok_practice_url', $tiktokUrl);
        $this->settingsModel->set('tiktok_practice_title', $tiktokTitle !== '' ? $tiktokTitle : 'Video Latihan Privat Siswa KakYo Lesson');
        $this->settingsModel->set('tiktok_practice_desc', $tiktokDesc);

        $_SESSION['flash'] = [
            'status' => 'success',
            'title'  => 'Tautan TikTok Disimpan',
            'msg'    => 'Tautan video latihan TikTok privat untuk seluruh siswa, orang tua, dan guru berhasil diperbarui.'
        ];
        header("Location: index.php?page=dashboard");
        exit;
    }
}
