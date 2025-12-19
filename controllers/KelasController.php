<?php
// controllers/KelasController.php

require_once '../models/KelasModel.php';
require_once '../models/UserModel.php'; 

class KelasController {
    private $kelasModel;
    private $userModel;

    public function __construct($db) {
        $this->kelasModel = new KelasModel($db);
        $this->userModel = new UserModel($db);
    }

    // --- 1. HALAMAN UTAMA (DAFTAR KELAS) ---
    public function index() {
        $kelas = $this->kelasModel->getAll();
        $dataGuru = $this->userModel->getAllByRole('guru');

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/admin/kelas/index.php';
        require_once '../views/layouts/footer.php';
    }

    // --- 2. PROSES SIMPAN KELAS BARU ---
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'name'        => $_POST['name'],
                'teacher_id'  => $_POST['teacher_id'],
                'type'        => $_POST['type'],
                'instrument'  => $_POST['instrument'],
                'description' => $_POST['description']
            ];

            if ($this->kelasModel->create($data)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Berhasil!',
                    'msg'    => 'Data kelas baru telah berhasil ditambahkan.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Simpan',
                    'msg'    => 'Terjadi kesalahan saat menyimpan data kelas.'
                ];
            }
            header("Location: index.php?page=kelas");
            exit;
        }
    }

    // --- 3. HALAMAN DETAIL KELAS (LIHAT ANGGOTA) ---
    public function detail($id) {
        $kelas = $this->kelasModel->getById($id);
        $members = $this->kelasModel->getMembers($id);
        $allSiswa = $this->userModel->getAllByRole('siswa');

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/admin/kelas/detail.php';
        require_once '../views/layouts/footer.php';
    }

    // --- 4. PROSES TAMBAH SISWA KE KELAS ---
    public function add_member() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $class_id = $_POST['class_id'];
            $student_id = $_POST['student_id'];

            if ($this->kelasModel->addMember($class_id, $student_id)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Siswa Ditambahkan',
                    'msg'    => 'Anggota kelas berhasil diperbarui.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Duplikat!',
                    'msg'    => 'Siswa tersebut sudah terdaftar di kelas ini!'
                ];
            }
            header("Location: index.php?page=kelas&action=detail&id=$class_id");
            exit;
        }
    }

    // --- 5. PROSES HAPUS SISWA DARI KELAS ---
    public function delete_member() {
        if (isset($_GET['member_id']) && isset($_GET['class_id'])) {
            $member_id = $_GET['member_id']; 
            $class_id = $_GET['class_id'];   

            if ($this->kelasModel->removeMember($member_id)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Anggota Dihapus',
                    'msg'    => 'Siswa telah dikeluarkan dari kelas ini.'
                ];
            }
            header("Location: index.php?page=kelas&action=detail&id=$class_id");
            exit;
        }
    }

    // --- 6. PROSES UPDATE KELAS ---
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            
            $data = [
                'name'        => $_POST['name'],
                'teacher_id'  => $_POST['teacher_id'],
                'type'        => $_POST['type'],
                'instrument'  => $_POST['instrument'],
                'description' => $_POST['description']
            ];

            if ($this->kelasModel->update($id, $data)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Berhasil Update',
                    'msg'    => 'Informasi kelas telah diperbarui.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Update',
                    'msg'    => 'Data kelas gagal diperbarui, cek kembali inputan.'
                ];
            }
            header("Location: index.php?page=kelas");
            exit;
        }
    }
}
?>