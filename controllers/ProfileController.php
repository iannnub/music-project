<?php
require_once '../models/UserModel.php';
require_once '../helpers/ImageHelper.php';

class ProfileController {
    private $userModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
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
            $userOld = $this->userModel->getById($id);

            // --- PERBAIKAN FASE 2: TAMBAHKAN gdrive_link ---
            $data = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'parent_name' => $_POST['parent_name'] ?? null,
                'photo' => $userOld['photo_profile'],
                'ig_link' => $_POST['ig_link'] ?? $userOld['ig_link'] 
            ];

            // --- LOGIKA PROSES FOTO BASE64 (Sama seperti sebelumnya) ---
            if (!empty($_POST['photo_base64'])) {
                $base64Data = $_POST['photo_base64'];
                list($type, $base64Data) = explode(';', $base64Data);
                list(, $base64Data)      = explode(',', $base64Data);
                $imageData = base64_decode($base64Data);

                $fileName = "profil_" . $id . "_" . time() . ".jpg";
                $targetPath = "../public/uploads/profil/" . $fileName;

                if (file_put_contents($targetPath, $imageData)) {
                    $data['photo'] = $fileName;

                    if (!empty($userOld['photo_profile']) && $userOld['photo_profile'] != 'default.svg') {
                        $oldPath = "../public/uploads/profil/" . $userOld['photo_profile'];
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                }
            }

            // Eksekusi Update ke Database
            if ($this->userModel->updateProfile($id, $data)) {
                // REFRESH SESSION: Penting agar link GDrive terbaru langsung terbaca di seluruh sistem
                $_SESSION['user'] = $this->userModel->getById($id);

                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title' => 'Update Berhasil!',
                    'msg' => 'Profil Anda berhasil diperbarui.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Update',
                    'msg'    => 'Terjadi kesalahan saat menyimpan data.'
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