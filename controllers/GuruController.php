<?php
class GuruController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function index() {
        $teacher_id = $_SESSION['user']['id'];

        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);

        // 1. AMBIL JADWAL MENGAJAR
        $query = "SELECT schedules.*, classes.name as class_name, classes.type, 
                         (SELECT COUNT(*) FROM class_members WHERE class_members.class_id = classes.id) as total_murid
                  FROM schedules
                  JOIN classes ON schedules.class_id = classes.id
                  WHERE classes.teacher_id = :teacher_id
                  ORDER BY FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), start_time ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':teacher_id' => $teacher_id]);
        $jadwal_mengajar = $stmt->fetchAll();

        // 2. HITUNG TOTAL KELAS
        $stmt2 = $this->db->prepare("SELECT COUNT(*) as total FROM classes WHERE teacher_id = ?");
        $stmt2->execute([$teacher_id]);
        $total_kelas = $stmt2->fetch()['total'];

        // 3. HITUNG ABSENSI HARI INI
        $total_validasi = $guruModel->countTodayAttendance($teacher_id);

        // 4. LOAD VIEWS
        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/guru/dashboard.php'; 
        require_once '../views/layouts/footer.php';
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

    // --- MANAJEMEN MATERI ---

    public function materi() {
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);
        $teacher_id = $_SESSION['user']['id'];

        $my_classes = $guruModel->getMyClasses($teacher_id);
        $materials = $guruModel->getMaterials($teacher_id);

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
            
            $data = [
                'class_id' => $_POST['class_id'],
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'video_url' => $_POST['video_url']
            ];

            if ($guruModel->saveMaterial($data)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Materi Terupload',
                    'msg'    => 'Materi belajar baru sudah tersedia untuk siswa.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Upload',
                    'msg'    => 'Gagal mengunggah materi, silakan cek inputan.'
                ];
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
        $assignments = $guruModel->getAssignments($teacher_id);

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
            
            $data = [
                'class_id' => $_POST['class_id'],
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'deadline' => $_POST['deadline']
            ];

            if ($guruModel->saveAssignment($data)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Tugas Dibuat',
                    'msg'    => 'Tugas baru berhasil dipublish ke siswa.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Simpan',
                    'msg'    => 'Gagal membuat tugas baru.'
                ];
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
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Tugas Dihapus',
                    'msg'    => 'Data tugas telah dibersihkan dari sistem.'
                ];
            }
            header("Location: index.php?page=guru_tugas");
            exit();
        }
    }

    public function tugas_detail() {
        if (!isset($_GET['id'])) { header("Location: index.php?page=guru_tugas"); exit; }
        
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);
        
        $assignment_id = $_GET['id'];
        $tugas = $guruModel->getAssignmentById($assignment_id);
        $submissions = $guruModel->getSubmissions($assignment_id);

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/guru/tugas/detail.php';
        require_once '../views/layouts/footer.php';
    }

    public function tugas_nilai() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            require_once '../models/GuruModel.php';
            $guruModel = new GuruModel($this->db);
            
            $submission_id = $_POST['submission_id'];
            $assignment_id = $_POST['assignment_id'];
            $grade = $_POST['grade'];
            $feedback = $_POST['feedback'];

            if ($guruModel->saveGrade($submission_id, $grade, $feedback)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Nilai Tersimpan',
                    'msg'    => 'Penilaian tugas siswa berhasil diperbarui.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Menilai',
                    'msg'    => 'Terjadi kendala saat menginput nilai.'
                ];
            }
            header("Location: index.php?page=guru_tugas_detail&id=$assignment_id");
            exit();
        }
    }

    // --- MANAJEMEN VALIDASI ABSENSI ---

    public function validasi_absen() {
        if ($_SESSION['user']['role'] != 'guru') { header("Location: index.php"); exit; }
        
        require_once '../models/AbsensiModel.php';
        $absensiModel = new AbsensiModel($this->db);
        $teacher_id = $_SESSION['user']['id'];
        
        $data_absen = $absensiModel->getAbsensiByGuru($teacher_id);

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/guru/validasi_absen.php';
        require_once '../views/layouts/footer.php';
    }

    public function proses_validasi() {
        if (isset($_GET['id']) && isset($_GET['status'])) {
            require_once '../models/AbsensiModel.php';
            $absensiModel = new AbsensiModel($this->db);
            
            $id = $_GET['id'];
            $status = $_GET['status'];

            if ($absensiModel->updateStatus($id, $status)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Absensi Update',
                    'msg'    => 'Status kehadiran siswa berhasil divalidasi.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Validasi Gagal',
                    'msg'    => 'Gagal memperbarui status absensi.'
                ];
            }
            header("Location: index.php?page=guru_validasi");
            exit();
        }
    }
}
?>