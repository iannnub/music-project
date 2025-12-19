<?php
require_once '../models/PembayaranModel.php';
require_once '../models/UserModel.php'; 

class PembayaranController {
    private $pembayaranModel;
    private $userModel;

    public function __construct($db) {
        $this->pembayaranModel = new PembayaranModel($db);
        $this->userModel = new UserModel($db);
    }

    public function index() {
        $pembayaran = $this->pembayaranModel->getAll();
        $siswa = $this->userModel->getAllByRole('siswa');

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/admin/pembayaran/index.php';
        require_once '../views/layouts/footer.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Logic membersihkan nominal: Menghapus semua karakter non-digit (titik, Rp, spasi)
            $rawAmount = $_POST['amount'];
            $cleanAmount = preg_replace('/[^0-9]/', '', $rawAmount);

            $data = [
                'student_id' => $_POST['student_id'],
                'admin_id'   => $_SESSION['user']['id'],
                'month'      => $_POST['month'],
                'year'       => $_POST['year'],
                'start_date' => $_POST['start_date'],
                'end_date'   => $_POST['end_date'],
                'amount'     => $cleanAmount,
                'status'     => $_POST['status'],
                'notes'      => $_POST['notes']
            ];

            if ($this->pembayaranModel->create($data)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Transaksi Berhasil!',
                    'msg'    => 'Data pembayaran siswa telah berhasil dicatat.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Simpan',
                    'msg'    => 'Terjadi kesalahan sistem saat memproses pembayaran.'
                ];
            }
            header("Location: index.php?page=pembayaran");
            exit();
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            
            // Logic pembersihan yang sama untuk update
            $rawAmount = $_POST['amount'];
            $cleanAmount = preg_replace('/[^0-9]/', '', $rawAmount);

            $data = [
                'month'      => $_POST['month'],
                'year'       => $_POST['year'],
                'start_date' => $_POST['start_date'],
                'end_date'   => $_POST['end_date'],
                'amount'     => $cleanAmount,
                'status'     => $_POST['status'],
                'notes'      => $_POST['notes']
            ];

            if ($this->pembayaranModel->update($id, $data)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Update Berhasil',
                    'msg'    => 'Data transaksi pembayaran telah diperbarui.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Update Gagal',
                    'msg'    => 'Gagal memperbarui data transaksi pembayaran.'
                ];
            }
            header("Location: index.php?page=pembayaran");
            exit();
        }
    }

    public function delete() {
        if (isset($_GET['id'])) {
            if ($this->pembayaranModel->delete($_GET['id'])) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Data Dihapus',
                    'msg'    => 'Catatan transaksi pembayaran telah dihapus dari sistem.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Hapus',
                    'msg'    => 'Data gagal dihapus karena kendala teknis.'
                ];
            }
            header("Location: index.php?page=pembayaran");
            exit();
        }
    }

    public function cetak() {
        if (isset($_GET['id'])) {
            $data = $this->pembayaranModel->getById($_GET['id']);
            require_once '../views/admin/pembayaran/cetak.php';
        }
    }
}
?>