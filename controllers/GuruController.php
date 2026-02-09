<?php
class GuruController {
    private $db;
    private $userModel;

    public function __construct($db) {
        $this->db = $db;
        require_once '../models/UserModel.php';
        $this->userModel = new UserModel($this->db);
    }
    
    public function index() {
        $teacher_id = $_SESSION['user']['id'];
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);

        $data['total_kelas']      = $guruModel->getTotalKelas($teacher_id);
        $data['total_siswa']      = $guruModel->getTotalSiswa($teacher_id);
        $data['total_validasi']   = $guruModel->getTotalPendingAbsen($teacher_id);
        $data['jadwal_mengajar']  = $guruModel->getJadwalByGuru($teacher_id);
        $data['today_attendance'] = $guruModel->countTodayAttendance($teacher_id);

        $raw_urgent = $guruModel->getUrgentTasks($teacher_id);
        $data['urgent_tasks'] = [];
        foreach($raw_urgent as $task) {
            $is_complete = ($task['total_collected'] >= $task['total_expected']);
            if (!$is_complete) {
                $data['urgent_tasks'][] = $task;
            }
        }

        $stats = $guruModel->getGlobalTaskStats($teacher_id);
        $data['global_percent'] = ($stats['total_target'] > 0) 
                                    ? round(($stats['total_setoran'] / $stats['total_target']) * 100) 
                                    : 0;

        extract($data); 
        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/guru/dashboard.php'; 
        require_once '../views/layouts/footer.php';
    }

    public function indexGuruAdmin() {
        // Ambil semua user dengan role 'guru'
        $guru = $this->userModel->getAllByRole('guru');

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/admin/guru/index.php';
        require_once '../views/layouts/footer.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $data = [
                'username' => $_POST['username'],
                'name'     => $_POST['name'],
                'email'    => $_POST['email'], // Tetap dikirim, nanti di UserModel jadi NULL jika kosong
                'phone'    => $_POST['phone']
            ];

            // Password cuma diupdate kalau diisi
            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }

            if ($this->userModel->update($id, $data)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Update Berhasil',
                    'msg'    => 'Data guru telah diperbarui.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Update',
                    'msg'    => 'Mungkin username sudah digunakan.'
                ];
            }
            header("Location: index.php?page=guru");
            exit();
        }
    }

    public function delete() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if ($this->userModel->delete($id)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Dihapus',
                    'msg'    => 'Data guru berhasil dihapus dari sistem.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Hapus',
                    'msg'    => 'Data tidak bisa dihapus karena masih terkait data lain.'
                ];
            }
        }
        header("Location: index.php?page=guru");
        exit();
    }

    // --- FITUR CRUD GURU (Digunakan Admin) ---
    public function store() {
    // 1. Tangkap data
    $data = [
        'username' => $_POST['username'],
        'name'     => $_POST['name'],
        'email'    => $_POST['email'], // Email dibiarkan apa adanya, NULL dikelola di Model
        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        'role'     => 'guru',
        'phone'    => $_POST['phone']
    ];

    // 2. Eksekusi
    if ($this->userModel->create($data)) {
        $_SESSION['flash'] = [
            'status' => 'success',
            'title'  => 'Berhasil!',
            'msg'    => 'Data guru baru berhasil ditambahkan.'
        ];
    } else {
        // Jika gagal, jangan lempar status=error ke URL
        $_SESSION['flash'] = [
            'status' => 'error',
            'title'  => 'Gagal Simpan',
            'msg'    => 'Username sudah digunakan, silakan pakai yang lain.'
        ];
    }
    
    header("Location: index.php?page=guru");
    exit();
}

    // --- FITUR INPUT PROGRESS ---
    public function input_progress() {
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);
        $teacher_id = $_SESSION['user']['id'];
        $my_classes = $guruModel->getMyClasses($teacher_id);
        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/guru/progress/index.php';
        require_once '../views/layouts/footer.php';
    }

    public function input_progress_detail() {
        if (!isset($_GET['class_id'])) { header("Location: index.php?page=guru_progress"); exit; }
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);
        $class_id = $_GET['class_id'];
        $teacher_id = $_SESSION['user']['id'];
        $students = $guruModel->getStudentsInClass($class_id);
        $history = $guruModel->getProgressHistory($class_id, $teacher_id);
        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/guru/progress/detail.php';
        require_once '../views/layouts/footer.php';
    }

    public function store_progress() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            require_once '../models/GuruModel.php';
            $guruModel = new GuruModel($this->db);

            $data = [
                'class_id'   => $_POST['class_id'],
                'student_id' => $_POST['student_id'],
                'teacher_id' => $_SESSION['user']['id'],
                'date'       => $_POST['date'],
                'topic'      => $_POST['topic'],
                'notes'      => $_POST['notes']
            ];

            if ($guruModel->saveProgress($data)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Progress Dicatat!',
                    'msg'    => 'Laporan perkembangan siswa berhasil disimpan.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Simpan',
                    'msg'    => 'Terjadi kesalahan sistem saat menyimpan progress.'
                ];
            }
            header("Location: index.php?page=guru_progress_detail&class_id=".$data['class_id']);
            exit();
        }
    }

    public function progress_update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            require_once '../models/GuruModel.php';
            $guruModel = new GuruModel($this->db);

            $id = $_POST['id'];
            $class_id = $_POST['class_id']; // Kita butuh ini buat redirect balik

            $data = [
                'date'  => $_POST['date'],
                'topic' => $_POST['topic'],
                'notes' => $_POST['notes']
            ];

            if ($guruModel->updateProgress($id, $data)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Berhasil!',
                    'msg'    => 'Jurnal progress berhasil diperbarui.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Update',
                    'msg'    => 'Terjadi kesalahan saat memperbarui jurnal.'
                ];
            }
            
            header("Location: index.php?page=guru_progress_detail&class_id=" . $class_id);
            exit();
        }
    }

    public function progress_delete() {
        if (isset($_GET['id']) && isset($_GET['class_id'])) {
            require_once '../models/GuruModel.php';
            $guruModel = new GuruModel($this->db);

            $id = $_GET['id'];
            $class_id = $_GET['class_id'];

            if ($guruModel->deleteProgress($id)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Dihapus!',
                    'msg'    => 'Satu baris jurnal progress telah dibersihkan.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Hapus',
                    'msg'    => 'Data ini mungkin masih terkait dengan laporan lain.'
                ];
            }
            
            header("Location: index.php?page=guru_progress_detail&class_id=" . $class_id);
            exit();
        }
    }

    // --- MANAJEMEN MATERI ---
    public function materi() {
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);
        $teacher_id = $_SESSION['user']['id'];

        $my_classes = $guruModel->getMyClasses($teacher_id);
        $materials = $guruModel->getMaterials($teacher_id);
        $my_students = $guruModel->getMyStudents($teacher_id); // <--- TAMBAHAN: Ambil data murid

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/guru/materi/index.php';
        require_once '../views/layouts/footer.php';
    }

    public function materi_store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            require_once '../models/GuruModel.php';
            $guruModel = new GuruModel($this->db);
            
            // Logika: Jika milih 'semua', student_id jadi NULL
            $student_id = ($_POST['student_id'] == 'all') ? null : $_POST['student_id'];

            $data = [
                'class_id' => $_POST['class_id'],
                'student_id' => $student_id, // <--- TAMBAHAN
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'video_url' => $_POST['video_url']
            ];

            if ($guruModel->saveMaterial($data)) {
                $_SESSION['flash'] = ['status' => 'success', 'title' => 'Berhasil', 'msg' => 'Materi telah diterbitkan.'];
            }
            header("Location: index.php?page=guru_materi");
            exit();
        }
    }

    public function materi_update() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);
        
        $id = $_POST['id'];
        $student_id = ($_POST['student_id'] == 'all') ? null : $_POST['student_id'];

        $data = [
            'class_id' => $_POST['class_id'],
            'student_id' => $student_id,
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'video_url' => $_POST['video_url']
        ];

        if ($guruModel->updateMaterial($id, $data)) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Updated', 'msg' => 'Materi berhasil diperbarui.'];
        }
        header("Location: index.php?page=guru_materi");
        exit();
    }
}

    public function materi_delete() {
        if (isset($_GET['id'])) {
            require_once '../models/GuruModel.php';
            $guruModel = new GuruModel($this->db);
            if ($guruModel->deleteMaterial($_GET['id'])) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Terhapus',
                    'msg'    => 'Materi belajar telah berhasil dihapus.'
                ];
            }
            header("Location: index.php?page=guru_materi");
            exit();
        }
    }

    // --- MANAJEMEN TUGAS ---
    public function tugas() {
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);
        $teacher_id = $_SESSION['user']['id'];

        $my_classes = $guruModel->getMyClasses($teacher_id);
        $tasks = $guruModel->getAssignments($teacher_id); 
        $my_students = $guruModel->getMyStudents($teacher_id); 

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/guru/tugas/index.php'; 
        require_once '../views/layouts/footer.php';
    }

    public function tugas_store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            require_once '../models/GuruModel.php';
            $guruModel = new GuruModel($this->db);
            
            // Konversi 'all' menjadi NULL untuk database
            $student_id = ($_POST['student_id'] == 'all') ? null : $_POST['student_id'];

            $data = [
                'class_id'       => $_POST['class_id'],
                'student_id'     => $student_id,
                'title'          => $_POST['title'],
                'description'    => $_POST['description'],
                'deadline'       => $_POST['deadline'],
                'link_referensi' => $_POST['link_referensi'] // Menangkap link video/drive
            ];

            if ($guruModel->saveAssignment($data)) {
                $_SESSION['flash'] = ['status' => 'success', 'title' => 'Berhasil', 'msg' => 'Tugas baru telah dipublish.'];
            }
            header("Location: index.php?page=guru_tugas");
            exit();
        }
    }

    public function tugas_update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            require_once '../models/GuruModel.php';
            $guruModel = new GuruModel($this->db);
            
            $id = $_POST['id'];
            $student_id = ($_POST['student_id'] == 'all') ? null : $_POST['student_id'];

            $data = [
                'class_id'       => $_POST['class_id'],
                'student_id'     => $student_id,
                'title'          => $_POST['title'],
                'description'    => $_POST['description'],
                'deadline'       => $_POST['deadline'],
                'link_referensi' => $_POST['link_referensi']
            ];

            if ($guruModel->updateAssignment($id, $data)) {
                $_SESSION['flash'] = ['status' => 'success', 'title' => 'Tugas Diperbarui', 'msg' => 'Perubahan tugas berhasil disimpan.'];
            }
            header("Location: index.php?page=guru_tugas");
            exit();
        }
    }

    public function tugas_delete() {
        if (isset($_GET['id'])) {
            require_once '../models/GuruModel.php';
            $guruModel = new GuruModel($this->db);
            if ($guruModel->deleteAssignment($_GET['id'])) {
                $_SESSION['flash'] = ['status' => 'success', 'title' => 'Dihapus', 'msg' => 'Tugas berhasil dihapus.'];
            }
            header("Location: index.php?page=guru_tugas");
            exit();
        }
    }

    public function tugas_detail() {
        if (!isset($_GET['id'])) { header("Location: index.php?page=guru_tugas"); exit; }
        
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);
        $teacher_id = $_SESSION['user']['id'];
        
        $assignment_id = $_GET['id'];
        $tugas = $guruModel->getAssignmentById($assignment_id);
        $submissions = $guruModel->getSubmissions($assignment_id);

        // Ambil Master Link GDrive untuk tombol cek cepat di View
        $teacher_data = $this->userModel->getById($teacher_id);
        $teacher_gdrive = $teacher_data['gdrive_link'] ?? '';

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/guru/tugas/detail.php';
        require_once '../views/layouts/footer.php';
    }

    public function tugas_acc() {
        if (isset($_GET['submission_id']) && isset($_GET['assignment_id'])) {
            require_once '../models/GuruModel.php';
            $guruModel = new GuruModel($this->db);
            
            $submission_id = $_GET['submission_id'];
            $assignment_id = $_GET['assignment_id'];

            if ($guruModel->accSubmission($submission_id)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Berhasil ACC',
                    'msg'    => 'Status tugas murid telah berubah menjadi Selesai.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal ACC',
                    'msg'    => 'Terjadi kesalahan sistem saat memperbarui status.'
                ];
            }
            header("Location: index.php?page=guru_tugas_detail&id=$assignment_id");
            exit();
        }
    }

    public function tugas_nilai() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            require_once '../models/GuruModel.php';
            $guruModel = new GuruModel($this->db);
            
            $submission_id = $_POST['submission_id'];
            $assignment_id = $_POST['assignment_id'];
            $grade = $_POST['grade'];
            $feedback = $_POST['feedback'];

            // --- FASE 3: SAVE GRADE & AUTO ACC ---
            // Fungsi ini nantinya akan mengupdate status menjadi 'Selesai' di GuruModel
            if ($guruModel->saveGrade($submission_id, $grade, $feedback)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'ACC Berhasil!',
                    'msg'    => 'Tugas telah diverifikasi dan nilai berhasil diterbitkan.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Verifikasi',
                    'msg'    => 'Terjadi kendala teknis saat memproses nilai.'
                ];
            }
            header("Location: index.php?page=guru_tugas_detail&id=$assignment_id");
            exit();
        }
    }

    // --- MANAJEMEN VALIDASI ABSENSI (THE INBOX) ---
    public function validasi_absen() {
        if ($_SESSION['user']['role'] != 'guru') { header("Location: index.php"); exit; }
        
        require_once '../models/AbsensiModel.php';
        $absensiModel = new AbsensiModel($this->db);
        $teacher_id = $_SESSION['user']['id'];
        
        // Memanggil method khusus 'Pending' dari Model Fase 1 kita
        $data_absen = $absensiModel->getPendingAbsensiByGuru($teacher_id); 

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/guru/validasi_absen.php';
        require_once '../views/layouts/footer.php';
    }

    public function riwayat_absen() {
        if ($_SESSION['user']['role'] != 'guru') { header("Location: index.php"); exit; }
        
        require_once '../models/AbsensiModel.php';
        $absensiModel = new AbsensiModel($this->db);
        $teacher_id = $_SESSION['user']['id'];
        
        $list_siswa = $absensiModel->getStudentAttendanceSummary($teacher_id);

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/guru/riwayat_absen.php';
        require_once '../views/layouts/footer.php';
    }

    public function riwayat_detail() {
        if ($_SESSION['user']['role'] != 'guru' || !isset($_GET['student_id'])) { 
            header("Location: index.php?page=guru_riwayat"); exit; 
        }
        
        require_once '../models/AbsensiModel.php';
        $absensiModel = new AbsensiModel($this->db);
        $teacher_id = $_SESSION['user']['id'];
        $student_id = $_GET['student_id'];

        $history = $absensiModel->getAttendanceDetailByStudent($student_id, $teacher_id);
        
        // Ambil info nama siswa untuk label
        require_once '../models/UserModel.php';
        $siswa = (new UserModel($this->db))->getById($student_id);

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/guru/riwayat_detail.php';
        require_once '../views/layouts/footer.php';
    }

    public function proses_validasi() {
        if (isset($_GET['id']) && isset($_GET['status'])) {
            require_once '../models/AbsensiModel.php';
            $absensiModel = new AbsensiModel($this->db);
            
            if ($absensiModel->updateStatus($_GET['id'], $_GET['status'])) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Validasi Berhasil',
                    'msg'    => 'Data telah dipindahkan ke Riwayat Absensi.'
                ];
            }
            header("Location: index.php?page=guru_validasi");
            exit();
        }
    }
}