<?php
require_once '../models/UserModel.php';

class UserController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new UserModel($db);
    }

    // --- HALAMAN GURU ---
    public function indexGuru() {
        $guru = $this->userModel->getAllByRole('guru');
        
        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/admin/guru/index.php';
        require_once '../views/layouts/footer.php';
    }

    // --- HALAMAN SISWA ---
    public function indexSiswa() {
        $siswa = $this->userModel->getAllByRole('siswa');
        
        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/admin/siswa/index.php';
        require_once '../views/layouts/footer.php';
    }

    // --- LOGIC SIMPAN ---
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'];
            $name     = $_POST['name'];
            $email    = $_POST['email'];
            $phone    = $_POST['phone'];
            $password = $_POST['password'];
            $role     = $_POST['role']; 

            // Validasi Simple
            if (empty($username) || empty($password) || empty($email)) {
                $_SESSION['flash'] = [
                    'status' => 'warning',
                    'title'  => 'Data Belum Lengkap',
                    'msg'    => 'Username, Email, dan Password wajib diisi ya!'
                ];
                header("Location: index.php?page=" . $role);
                exit;
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $data = [
                'username' => $username,
                'name'     => $name,
                'email'    => $email,
                'phone'    => $phone,
                'password' => $hashed_password,
                'role'     => $role
            ];

            if ($this->userModel->create($data)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'User Berhasil Dibuat!',
                    'msg'    => 'Data ' . ucfirst($role) . ' baru telah aktif di sistem.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Registrasi',
                    'msg'    => 'Username atau Email sudah terpakai oleh user lain.'
                ];
            }
            header("Location: index.php?page=" . $role);
            exit;
        }
    }

    // --- LOGIC UPDATE ---
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id   = $_POST['id'];
            $role = $_POST['role']; 
            
            $data = [
                'username' => $_POST['username'],
                'name'     => $_POST['name'],
                'email'    => $_POST['email'],
                'phone'    => $_POST['phone'],
                'password' => '' 
            ];

            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }

            if ($this->userModel->update($id, $data)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Update Berhasil',
                    'msg'    => 'Informasi ' . ucfirst($role) . ' telah diperbarui.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Update Gagal',
                    'msg'    => 'Terjadi kendala saat memperbarui data user.'
                ];
            }
            header("Location: index.php?page=" . $role);
            exit;
        }
    }

    // --- LOGIC DELETE ---
    public function delete() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

            if ($this->userModel->delete($id)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Akun Dihapus',
                    'msg'    => 'Data pengguna tersebut telah ditiadakan dari sistem.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Hapus',
                    'msg'    => 'Data gagal dihapus karena kendala integritas database.'
                ];
            }
            header("Location: index.php?page=" . $page);
            exit;
        }
    }
}
?>