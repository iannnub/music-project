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
            if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                die("Akses Ditolak: Token Security Tidak Valid!");
            }
            
            $id = (int)$_SESSION['user']['id'];
            $userOld = $this->userModel->getById($id);

            $rawIg = trim($_POST['ig_link'] ?? '');
            $safeIg = '';
            if ($rawIg !== '') {
                if (preg_match('#^https?://#i', $rawIg) && filter_var($rawIg, FILTER_VALIDATE_URL)) {
                    $safeIg = $rawIg;
                } else {
                    $safeIg = $userOld['ig_link'] ?? '';
                }
            }

            $data = [
                'name' => trim($_POST['name'] ?? $userOld['name']),
                'email' => trim($_POST['email'] ?? $userOld['email']),
                'phone' => trim($_POST['phone'] ?? ''),
                'parent_name' => isset($_POST['parent_name']) ? trim($_POST['parent_name']) : null,
                'photo' => $userOld['photo_profile'],
                'ig_link' => $safeIg
            ];

            // Validasi & re-encode foto profil Base64 via GD (anti-polyfill / file upload spoofing)
            if (!empty($_POST['photo_base64'])) {
                $resImg = ImageHelper::saveBase64Image($_POST['photo_base64'], '../public/uploads/profil/', 'profil_' . $id, 512, 82);
                if (!empty($resImg['status'])) {
                    $data['photo'] = $resImg['fileName'];

                    $oldBase = basename($userOld['photo_profile'] ?? '');
                    if (!empty($oldBase) && !in_array($oldBase, ['default.png', 'default.svg'], true)) {
                        $oldPath = '../public/uploads/profil/' . $oldBase;
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                } else {
                    $_SESSION['flash'] = [
                        'status' => 'warning',
                        'title'  => 'Foto Profil Ditolak',
                        'msg'    => $resImg['msg'] ?? 'Format gambar tidak valid.'
                    ];
                    header("Location: index.php?page=profile");
                    exit;
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
            if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                die("Akses Ditolak: Token Security Tidak Valid!");
            }
            $id = (int)$_SESSION['user']['id'];
            $old_pass = $_POST['old_password'] ?? '';
            $new_pass = $_POST['new_password'] ?? '';
            $confirm_pass = $_POST['confirm_password'] ?? '';

            if (strlen($new_pass) < 6) {
                $_SESSION['flash'] = [
                    'status' => 'warning',
                    'title'  => 'Kata Sandi Terlalu Pendek',
                    'msg'    => 'Kata sandi baru minimal 6 karakter.'
                ];
                header("Location: index.php?page=profile");
                exit;
            }

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
                session_regenerate_id(true);
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