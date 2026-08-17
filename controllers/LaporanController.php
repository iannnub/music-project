<?php
require_once '../models/LaporanModel.php';
require_once '../models/KelasModel.php'; // Butuh buat dropdown kelas

class LaporanController {
    private $db;
    private $laporanModel;
    private $kelasModel;

    public function __construct($db) {
        $this->db = $db;
        $this->laporanModel = new LaporanModel($db);
        $this->kelasModel = new KelasModel($db);
    }

    // --- HALAMAN LAPORAN KEUANGAN ---
    public function keuangan() {
        // Default: Bulan ini & Tahun ini
        $bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
        $tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

        $laporan = $this->laporanModel->getLaporanKeuangan($bulan, $tahun);
        
        // Hitung Total Pemasukan
        $total_pemasukan = 0;
        foreach($laporan as $row) {
            $total_pemasukan += $row['amount'];
        }

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/admin/laporan/keuangan.php'; // View
        require_once '../views/layouts/footer.php';
    }

    // --- HALAMAN LAPORAN ABSENSI ---
    public function absensi() {
        $dataKelas = $this->kelasModel->getAll();

        // Pake date('n') biar dapet angka bulan 1-12 tanpa leading zero
        $class_id = isset($_GET['class_id']) ? $_GET['class_id'] : '';
        $bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('n'); 
        $tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

        $laporan = [];
        $nama_kelas = "";

        if ($class_id) {
            // Pastikan LaporanModel.php sudah lo update JOIN-nya ke class_members!
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
    // Ambil semua data user dengan role guru untuk dropdown filter
    $stmt = $this->db->prepare("SELECT id, name FROM users WHERE role = 'guru' ORDER BY name ASC");
    $stmt->execute();
    $dataGuru = $stmt->fetchAll();

    // Cek Role Pengguna untuk validasi hak akses data
    $user_role = $_SESSION['user']['role'];
    if ($user_role === 'guru') {
        $teacher_id = $_SESSION['user']['id'];
    } else {
        $teacher_id = isset($_GET['teacher_id']) ? $_GET['teacher_id'] : '';
    }
    $bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('n');
    $tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
    $tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

    // Selalu ambil laporan (baik untuk guru spesifik maupun semua guru)
    $laporan = $this->laporanModel->getLaporanAbsensiGuru($teacher_id, $bulan, $tahun, $tanggal);

    require_once '../views/layouts/header.php';
    require_once '../views/layouts/sidebar.php';
    require_once '../views/layouts/topbar.php';
    require_once '../views/admin/laporan/absensi_guru.php'; // View Baru
    require_once '../views/layouts/footer.php';
}

}
?>