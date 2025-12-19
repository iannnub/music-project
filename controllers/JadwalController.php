<?php
require_once '../models/JadwalModel.php';
require_once '../models/KelasModel.php';

class JadwalController {
    private $jadwalModel;
    private $kelasModel;

    public function __construct($db) {
        $this->jadwalModel = new JadwalModel($db);
        $this->kelasModel = new KelasModel($db);
    }

    public function index() {
        $jadwal = $this->jadwalModel->getAll();
        $dataKelas = $this->kelasModel->getAll();

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/admin/jadwal/index.php';
        require_once '../views/layouts/footer.php';
    }

    // --- STORE MULTIPLE SCHEDULES ---
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $class_id = $_POST['class_id'];
            
            $days        = $_POST['day'];        
            $start_times = $_POST['start_time']; 
            $end_times   = $_POST['end_time'];   

            $successCount = 0;

            // Looping data array yang dikirim dari form
            for ($i = 0; $i < count($days); $i++) {
                if (!empty($days[$i]) && !empty($start_times[$i])) {
                    $data = [
                        'class_id'   => $class_id,
                        'day'        => $days[$i],
                        'start_time' => $start_times[$i],
                        'end_time'   => $end_times[$i]
                    ];
                    
                    if ($this->jadwalModel->create($data)) {
                        $successCount++;
                    }
                }
            }

            if ($successCount > 0) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Jadwal Tersimpan!',
                    'msg'    => $successCount . ' sesi jadwal baru berhasil ditambahkan.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Simpan',
                    'msg'    => 'Tidak ada jadwal yang berhasil disimpan. Cek kembali inputan Anda.'
                ];
            }
            header("Location: index.php?page=jadwal");
            exit;
        }
    }

    public function delete() {
        if (isset($_GET['id'])) {
            if ($this->jadwalModel->delete($_GET['id'])) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Terhapus!',
                    'msg'    => 'Jadwal tersebut telah dihapus dari sistem.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Hapus',
                    'msg'    => 'Jadwal gagal dihapus karena kendala sistem.'
                ];
            }
            header("Location: index.php?page=jadwal");
            exit;
        }
    }

    // --- UPDATE JADWAL ---
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            
            $data = [
                'day'        => $_POST['day'],
                'start_time' => $_POST['start_time'],
                'end_time'   => $_POST['end_time']
            ];

            if ($this->jadwalModel->update($id, $data)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Update Berhasil',
                    'msg'    => 'Perubahan jadwal mengajar telah disimpan.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Update Gagal',
                    'msg'    => 'Gagal memperbarui data jadwal.'
                ];
            }
            header("Location: index.php?page=jadwal");
            exit;
        }
    }
}
?>