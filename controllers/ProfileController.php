<?php
require_once '../models/UserModel.php';
require_once '../helpers/ImageHelper.php';

class ProfileController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new UserModel($db);
    }

    public function index() {
        $user = $this->userModel->getById($_SESSION['user']['id']);
        $_SESSION['user'] = $user; 

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/profile/index.php';
        require_once '../views/layouts/footer.php';
    }

    public function update_profile() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!CsrfHelper::verifyToken($_POST['csrf_token'])) {
                die("Akses Ditolak: Token Security Tidak Valid!");
            }
            $id = $_SESSION['user']['id'];
            
            $data = [
                'name'  => $_POST['name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'photo' => ''
            ];

            // --- UPGRADE: PAKAI IMAGE HELPER ---
            if (!empty($_FILES['photo']['name'])) {
                $targetDir = "../public/uploads/profil/";
                $prefix = "profil_" . $id;

                $uploadResult = ImageHelper::uploadAndCompress($_FILES['photo'], $targetDir, $prefix);

                if ($uploadResult['status']) {
                    $data['photo'] = $uploadResult['fileName'];
                } else {
                    $_SESSION['flash'] = [
                        'status' => 'error',
                        'title'  => 'Upload Gagal',
                        'msg'    => $uploadResult['msg']
                    ];
                    header("Location: index.php?page=profile");
                    exit;
                }
            }

            if ($this->userModel->updateProfile($id, $data)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Profil Update!',
                    'msg'    => 'Data profil kamu berhasil diperbarui.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Update',
                    'msg'    => 'Terjadi kesalahan saat menyimpan perubahan profil.'
                ];
            }
            header("Location: index.php?page=profile");
            exit;
        }
    }

    public function change_password() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!CsrfHelper::verifyToken($_POST['csrf_token'])) {
                die("Akses Ditolak: Token Security Tidak Valid!");
            }
            $id = $_SESSION['user']['id'];
            $old_pass = $_POST['old_password'];
            $new_pass = $_POST['new_password'];
            $confirm_pass = $_POST['confirm_password'];

            // Validasi: Password baru & konfirmasi harus sama
            if ($new_pass !== $confirm_pass) {
                $_SESSION['flash'] = [
                    'status' => 'warning',
                    'title'  => 'Cek Kembali',
                    'msg'    => 'Konfirmasi password baru tidak cocok!'
                ];
                header("Location: index.php?page=profile");
                exit;
            }

            if ($this->userModel->changePassword($id, $old_pass, $new_pass)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Sandi Berhasil Diubah',
                    'msg'    => 'Password kamu telah diperbarui, jangan sampai lupa ya!'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Ubah Sandi',
                    'msg'    => 'Password lama yang kamu masukkan salah!'
                ];
            }
            header("Location: index.php?page=profile");
            exit;
        }
    }
}
?>