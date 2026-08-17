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
        $pembayaran = $this->pembayaranModel->getAllGrouped(); 
        $siswa = $this->userModel->getAllByRole('siswa');

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/admin/pembayaran/index.php';
        require_once '../views/layouts/footer.php';
    }

    public function detail() {
        if (isset($_GET['student_id'])) {
            $student_id = $_GET['student_id'];
            $student = $this->userModel->getById($student_id);
            $history = $this->pembayaranModel->getAllByStudent($student_id); 

            require_once '../views/layouts/header.php';
            require_once '../views/layouts/sidebar.php';
            require_once '../views/layouts/topbar.php';
            require_once '../views/admin/pembayaran/detail.php';
            require_once '../views/layouts/footer.php';
        } else {
            header("Location: index.php?page=pembayaran");
        }
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
                'notes'      => $_POST['notes'] ?? ''
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
            header("Location: index.php?page=pembayaran_detail&student_id=" . $data['student_id']);
            exit();
        }
    }

    public function delete_all_by_student() {
    if (isset($_GET['student_id'])) {
        $student_id = $_GET['student_id'];
        if ($this->pembayaranModel->deleteAllByStudent($student_id)) {
            $_SESSION['flash'] = [
                'status' => 'success',
                'title'  => 'Berhasil!',
                'msg'    => 'Seluruh riwayat pembayaran siswa telah dihapus.'
            ];
        } else {
            $_SESSION['flash'] = [
                'status' => 'error',
                'title'  => 'Gagal!',
                'msg'    => 'Terjadi kesalahan saat menghapus data.'
            ];
        }
        header("Location: index.php?page=pembayaran");
        exit();
    }
}

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $rawAmount = $_POST['amount'];
            $cleanAmount = preg_replace('/[^0-9]/', '', $rawAmount);
            $student_id = $_POST['student_id'];

            $data = [
                'month'      => $_POST['month'],
                'year'       => $_POST['year'],
                'start_date' => $_POST['start_date'],
                'end_date'   => $_POST['end_date'],
                'amount'     => $cleanAmount,
                'status'     => $_POST['status'],
                'notes'      => $_POST['notes'] ?? ''
            ];

            if ($this->pembayaranModel->update($id, $data)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Update Berhasil',
                    'msg'    => 'Data transaksi pembayaran telah diperbarui.'
                ];
            }
            header("Location: index.php?page=pembayaran_detail&student_id=" . $student_id);
            exit();
        }
    }

    public function delete() {
    if (isset($_GET['id'])) {

        $payment = $this->pembayaranModel->getById($_GET['id']);
        $student_id = $payment['student_id'] ?? null;

        if ($this->pembayaranModel->delete($_GET['id'])) {
            $_SESSION['flash'] = [
                'status' => 'success',
                'title'  => 'Dihapus',
                'msg'    => 'Data transaksi dihapus.'
            ];
        }

        if ($student_id) {
            header("Location: index.php?page=pembayaran_detail&student_id=" . $student_id);
        } else {
            header("Location: index.php?page=pembayaran");
        }
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