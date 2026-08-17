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

        // Ambil ID Jadwal (class_members) yang sudah diabsen hari ini oleh guru ini
        $today = date('Y-m-d');
        $stmtChecked = $this->db->prepare("SELECT schedule_id FROM teacher_attendances WHERE teacher_id = ? AND date = ?");
        $stmtChecked->execute([$teacher_id, $today]);
        $data['checked_schedule_ids'] = $stmtChecked->fetchAll(PDO::FETCH_COLUMN);

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

public function proses_absen_guru() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // 1. Validasi Keamanan CSRF
        if (!CsrfHelper::verifyToken($_POST['csrf_token'])) {
            die("Akses Ditolak: Token Security Tidak Valid!");
        }

        require_once '../models/AbsensiModel.php';
        $absensiModel = new AbsensiModel($this->db);

        $teacher_id = $_SESSION['user']['id'];
        $photo_base64 = $_POST['photo_base64'];
        $req_schedule_id = isset($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : 0;
        
        // 2. Olah Foto Base64 menjadi file .jpg
        $img = str_replace('data:image/jpeg;base64,', '', $photo_base64);
        $img = str_replace(' ', '+', $img);
        $dataFoto = base64_decode($img);
        
        $fileName = "absen_guru_" . $teacher_id . "_" . time() . ".jpg";
        $path = "../public/uploads/absen_guru/" . $fileName;

        // Simpan foto ke folder server
        if (file_put_contents($path, $dataFoto)) {
            $data = [
                'teacher_id' => $teacher_id,
                'schedule_id' => $req_schedule_id,
                'date' => date('Y-m-d'),
                'check_in_time' => date('H:i:s'),
                'photo' => $fileName,
                'latitude' => $_POST['latitude'],
                'longitude' => $_POST['longitude']
            ];

            // 3. Eksekusi Absensi & Tangkap Hasilnya
            $result = $absensiModel->submitTeacherAttendance($data);

            if ($result === "diluar_radius") {
                unlink($path); 
                $_SESSION['flash'] = [
                    'status' => 'danger',
                    'title'  => 'Absen Ditolak!',
                    'msg'    => 'Posisi Anda terlalu jauh dari lokasi les. Silakan mendekat!'
                ];
            } elseif ($result === "sudah_absen") {
                unlink($path);
                $_SESSION['flash'] = [
                    'status' => 'warning',
                    'title'  => 'Absen Ditolak',
                    'msg'    => 'Anda sudah melakukan absensi hari ini!'
                ];
            } elseif ($result === "tidak_ada_jadwal") {
                unlink($path);
                $_SESSION['flash'] = [
                    'status' => 'warning',
                    'title'  => 'Absen Ditolak',
                    'msg'    => 'Anda tidak memiliki jadwal mengajar hari ini!'
                ];
            } elseif ($result === "absen_ditutup") {
                unlink($path);
                $_SESSION['flash'] = [
                    'status' => 'danger',
                    'title'  => 'Absen Ditutup!',
                    'msg'    => 'Jam mengajar Anda untuk hari ini sudah selesai. Batas absen telah lewat!'
                ];
            } elseif ($result === "absen_belum_dibuka") {
                unlink($path);
                $_SESSION['flash'] = [
                    'status' => 'warning',
                    'title'  => 'Absen Belum Dibuka',
                    'msg'    => 'Waktu absen belum dibuka. Anda baru bisa melakukan absen 5 menit sebelum jam kelas dimulai!'
                ];
            } elseif ($result) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Berhasil!',
                    'msg'    => 'Kehadiran Anda telah tercatat dengan gaji sesi ini.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'warning',
                    'title'  => 'Sistem Sibuk',
                    'msg'    => 'Gagal menyimpan data ke database. Coba lagi.'
                ];
            }
        } else {
            $_SESSION['flash'] = [
                'status' => 'danger',
                'title'  => 'Gagal Foto',
                'msg'    => 'Sistem gagal menyimpan foto bukti. Pastikan folder uploads tersedia.'
            ];
        }
        
        header("Location: index.php?page=guru_absen&schedule_id=" . $req_schedule_id);
        exit;
    }
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

    public function proses_update_absensi() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // 1. Verifikasi Keamanan CSRF
        if (!CsrfHelper::verifyToken($_POST['csrf_token'])) {
            die("Invalid CSRF Token");
        }

        // 2. Tangkap Data dari Form
        $id_absensi = $_POST['id_absensi'];
        $status_baru = $_POST['status'];
        $student_id = $_POST['student_id']; // Mengambil student_id dari input hidden

        // 3. Panggil Model
        require_once '../models/AbsensiModel.php';
        $absensiModel = new AbsensiModel($this->db);

        // 4. Eksekusi Update
        if ($absensiModel->updateStatus($id_absensi, $status_baru)) {
            $_SESSION['flash'] = [
                'status' => 'success',
                'title'  => 'Berhasil!',
                'msg'    => 'Status kehadiran telah diperbarui.'
            ];
        }
        header("Location: index.php?page=guru_riwayat_detail&student_id=" . $student_id);
        exit();
    }
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
    public function view_absen_guru() {
        $teacher_id = $_SESSION['user']['id'];
        $hari_ini = date('l');
        $daftar_hari = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $hari_indo = $daftar_hari[$hari_ini];
        $current_time = date('H:i:s');

        // Check if schedule_id is passed in URL
        $req_schedule_id = isset($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : 0;

        // Cek apakah punya jadwal hari ini
        $queryCekJadwal = "SELECT COUNT(*) FROM class_members cm JOIN classes c ON cm.class_id = c.id WHERE c.teacher_id = :tid AND cm.day = :day";
        $stmtCek = $this->db->prepare($queryCekJadwal);
        $stmtCek->execute([':tid' => $teacher_id, ':day' => $hari_indo]);
        $has_schedule_today = $stmtCek->fetchColumn() > 0;

        $absen_closed = false;
        $no_schedule = false;
        $absen_belum_dibuka = false;
        $already_checked_in = false;
        $jam_mulai = "";
        $jam_buka = "";
        $active_schedule = null;

        if (!$has_schedule_today) {
            $no_schedule = true;
        } else {
            if ($req_schedule_id > 0) {
                // If a specific schedule_id is requested, fetch it and verify it belongs to this teacher on this day
                $queryJadwal = "SELECT cm.id as schedule_id, cm.start_time, cm.end_time 
                                FROM class_members cm JOIN classes c ON cm.class_id = c.id 
                                WHERE cm.id = :sid AND c.teacher_id = :tid AND cm.day = :day";
                $stmtJadwal = $this->db->prepare($queryJadwal);
                $stmtJadwal->execute([
                    ':sid' => $req_schedule_id,
                    ':tid' => $teacher_id,
                    ':day' => $hari_indo
                ]);
                $active_schedule = $stmtJadwal->fetch();
                
                if ($active_schedule) {
                    // Check time window for this specific schedule
                    $start_absen_time = strtotime($active_schedule['start_time']) - 300;
                    $end_absen_time = strtotime($active_schedule['end_time']);
                    $time_now = strtotime($current_time);

                    if ($time_now < $start_absen_time) {
                        $absen_belum_dibuka = true;
                        $jam_mulai = date('H:i', strtotime($active_schedule['start_time']));
                        $jam_buka = date('H:i', $start_absen_time);
                        $active_schedule = null;
                    } elseif ($time_now > $end_absen_time) {
                        $absen_closed = true;
                        $active_schedule = null;
                    }
                } else {
                    $no_schedule = true;
                }
            } else {
                // Fallback: Cek apakah ada kelas hari ini yang sedang aktif jendela absensinya (dari 5 menit sebelum start_time s/d end_time)
                $queryJadwal = "SELECT cm.id as schedule_id, cm.start_time, cm.end_time 
                                FROM class_members cm JOIN classes c ON cm.class_id = c.id 
                                WHERE c.teacher_id = :tid AND cm.day = :day 
                                AND :current_time1 >= SUBTIME(cm.start_time, '00:05:00')
                                AND :current_time2 <= cm.end_time
                                ORDER BY cm.start_time ASC, cm.id ASC LIMIT 1";
                $stmtJadwal = $this->db->prepare($queryJadwal);
                $stmtJadwal->execute([
                    ':tid' => $teacher_id, 
                    ':day' => $hari_indo, 
                    ':current_time1' => $current_time, 
                    ':current_time2' => $current_time
                ]);
                $active_schedule = $stmtJadwal->fetch();
            }

            if ($active_schedule) {
                // Cek apakah guru sudah absen untuk jadwal aktif ini hari ini (Prevent Double Absen)
                $queryCekAbsen = "SELECT COUNT(*) FROM teacher_attendances 
                                  WHERE teacher_id = :tid AND schedule_id = :sid AND date = :date";
                $stmtCekAbsen = $this->db->prepare($queryCekAbsen);
                $stmtCekAbsen->execute([
                    ':tid' => $teacher_id, 
                    ':sid' => $active_schedule['schedule_id'], 
                    ':date' => date('Y-m-d')
                ]);
                $already_checked_in = $stmtCekAbsen->fetchColumn() > 0;
            } elseif (!$absen_belum_dibuka && !$absen_closed) {
                // Fallback to check future classes
                $queryFuture = "SELECT cm.start_time FROM class_members cm JOIN classes c ON cm.class_id = c.id 
                                WHERE c.teacher_id = :tid AND cm.day = :day 
                                AND SUBTIME(cm.start_time, '00:05:00') > :current_time
                                ORDER BY cm.start_time ASC LIMIT 1";
                $stmtFuture = $this->db->prepare($queryFuture);
                $stmtFuture->execute([':tid' => $teacher_id, ':day' => $hari_indo, ':current_time' => $current_time]);
                $future_class = $stmtFuture->fetch();

                if ($future_class) {
                    $absen_belum_dibuka = true;
                    $jam_mulai = date('H:i', strtotime($future_class['start_time']));
                    $jam_buka = date('H:i', strtotime($future_class['start_time']) - 300); // 5 menit sebelum
                } else {
                    $absen_closed = true;
                }
            }
        }

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/guru/absen.php';
        require_once '../views/layouts/footer.php';
    }
}