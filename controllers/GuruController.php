<?php
class GuruController {
    private $db;
    private $userModel;

    public function __construct($db) {
        $this->db = $db;
        require_once '../models/UserModel.php';
        $this->userModel = new UserModel($this->db);
    }

    private function requirePostAndCsrf() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Metode request tidak diizinkan. Gunakan POST.");
        }
        require_once '../helpers/CsrfHelper.php';
        if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token tidak valid");
        }
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

        $today = date('Y-m-d');
        $stmtChecked = $this->db->prepare("SELECT schedule_id FROM teacher_attendances WHERE teacher_id = ? AND date = ?");
        $stmtChecked->execute([$teacher_id, $today]);
        $data['checked_schedule_ids'] = $stmtChecked->fetchAll(PDO::FETCH_COLUMN);

        extract($data);
        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/guru/dashboard.php';
        require_once '../views/layouts/footer.php';
    }

    public function indexGuruAdmin() {
        $guru = $this->userModel->getAllByRole('guru');

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/admin/guru/index.php';
        require_once '../views/layouts/footer.php';
    }

    public function update() {
        $this->requirePostAndCsrf();
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'username' => trim($_POST['username'] ?? ''),
            'name'     => trim($_POST['name'] ?? ''),
            'email'    => trim($_POST['email'] ?? ''),
            'phone'    => trim($_POST['phone'] ?? '')
        ];

        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        if ($this->userModel->update($id, $data)) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Data Diperbarui', 'msg' => 'Data guru telah diperbarui.'];
        } else {
            $_SESSION['flash'] = ['status' => 'error', 'title' => 'Gagal Update', 'msg' => 'Username mungkin sudah digunakan.'];
        }
        header("Location: index.php?page=guru");
        exit();
    }

    public function delete() {
        $this->requirePostAndCsrf();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            if ($this->userModel->delete($id)) {
                $_SESSION['flash'] = ['status' => 'success', 'title' => 'Dihapus', 'msg' => 'Data guru berhasil dihapus dari sistem.'];
            } else {
                $_SESSION['flash'] = ['status' => 'error', 'title' => 'Gagal Hapus', 'msg' => 'Guru masih terkait dengan kelas aktif. Pindahkan pengajar kelas terlebih dahulu.'];
            }
        }
        header("Location: index.php?page=guru");
        exit();
    }

    public function store() {
        $this->requirePostAndCsrf();
        $data = [
            'username' => trim($_POST['username'] ?? ''),
            'name'     => trim($_POST['name'] ?? ''),
            'email'    => trim($_POST['email'] ?? ''),
            'password' => password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT),
            'role'     => 'guru',
            'phone'    => trim($_POST['phone'] ?? '')
        ];

        if ($this->userModel->create($data)) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Guru Ditambahkan', 'msg' => 'Data guru baru berhasil ditambahkan.'];
        } else {
            $_SESSION['flash'] = ['status' => 'error', 'title' => 'Gagal Simpan', 'msg' => 'Username sudah digunakan, silakan pilih yang lain.'];
        }
        header("Location: index.php?page=guru");
        exit();
    }

    // Presensi Guru dengan Kamera & Lokasi Perangkat Pengguna (Tanpa Blokir Radius Titik Tunggal)
    public function proses_absen_guru() {
        $this->requirePostAndCsrf();

        require_once '../models/AbsensiModel.php';
        $absensiModel = new AbsensiModel($this->db);

        $teacher_id = $_SESSION['user']['id'];
        $photo_base64 = $_POST['photo_base64'] ?? '';
        $req_schedule_id = isset($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : (int)($_POST['schedule_id'] ?? 0);

        if (empty($photo_base64) || strpos($photo_base64, 'data:image/') !== 0) {
            $_SESSION['flash'] = [
                'status' => 'warning',
                'title'  => 'Foto Belum Diambil',
                'msg'    => 'Silakan ambil foto bukti kehadiran melalui kamera terlebih dahulu.'
            ];
            header("Location: index.php?page=guru_absen&schedule_id=" . $req_schedule_id);
            exit;
        }

        $img = preg_replace('#^data:image/\w+;base64,#i', '', $photo_base64);
        $img = str_replace(' ', '+', $img);
        $dataFoto = base64_decode($img, true);

        $uploadDir = "../public/uploads/absen_guru/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = "absen_guru_" . $teacher_id . "_" . time() . ".jpg";
        $path = $uploadDir . $fileName;

        if ($dataFoto !== false && file_put_contents($path, $dataFoto)) {
            $data = [
                'teacher_id'    => $teacher_id,
                'schedule_id'   => $req_schedule_id,
                'date'          => date('Y-m-d'),
                'check_in_time' => date('H:i:s'),
                'photo'         => $fileName,
                'latitude'      => $_POST['latitude'] ?? '0',
                'longitude'     => $_POST['longitude'] ?? '0'
            ];

            $result = $absensiModel->submitTeacherAttendance($data);

            if ($result === "sudah_absen") {
                @unlink($path);
                $_SESSION['flash'] = ['status' => 'warning', 'title' => 'Sudah Presensi', 'msg' => 'Anda sudah melakukan presensi untuk sesi kelas ini hari ini.'];
            } elseif ($result === "tidak_ada_jadwal") {
                @unlink($path);
                $_SESSION['flash'] = ['status' => 'warning', 'title' => 'Tidak Ada Jadwal', 'msg' => 'Anda tidak memiliki jadwal mengajar hari ini.'];
            } elseif ($result === "absen_ditutup") {
                @unlink($path);
                $_SESSION['flash'] = ['status' => 'error', 'title' => 'Sesi Berakhir', 'msg' => 'Jam mengajar sesi ini telah selesai. Hubungi Admin untuk konfirmasi presensi susulan.'];
            } elseif ($result === "absen_belum_dibuka") {
                @unlink($path);
                $_SESSION['flash'] = ['status' => 'warning', 'title' => 'Belum Dibuka', 'msg' => 'Presensi dibuka mulai 5 menit sebelum jadwal kelas dimulai.'];
            } elseif ($result) {
                $_SESSION['flash'] = ['status' => 'success', 'title' => 'Presensi Berhasil', 'msg' => 'Kehadiran, foto bukti, dan titik lokasi mengajar Anda telah tercatat.'];
            } else {
                @unlink($path);
                $_SESSION['flash'] = ['status' => 'error', 'title' => 'Gagal Menyimpan', 'msg' => 'Terjadi kendala saat menyimpan presensi. Silakan coba lagi.'];
            }
        } else {
            $_SESSION['flash'] = ['status' => 'error', 'title' => 'Gagal Menyimpan Foto', 'msg' => 'Sistem tidak dapat memproses foto kamera.'];
        }

        header("Location: index.php?page=guru_absen&schedule_id=" . $req_schedule_id);
        exit;
    }

    // --- FITUR INPUT PROGRESS KURIKULUM BAB & SUBBAB PER SISWA ---

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
        if (!isset($_GET['class_id'])) {
            header("Location: index.php?page=guru_progress");
            exit;
        }
        require_once '../models/GuruModel.php';
        require_once '../models/KelasModel.php';
        $guruModel  = new GuruModel($this->db);
        $kelasModel = new KelasModel($this->db);

        $class_id   = (int)$_GET['class_id'];
        $teacher_id = (int)$_SESSION['user']['id'];

        if (!$guruModel->canTeacherAccessClass($teacher_id, $class_id)) {
            http_response_code(403);
            die("Akses ditolak: Anda tidak mengampu kelas ini.");
        }

        $kelasInfo = $kelasModel->getById($class_id);
        $students  = $guruModel->getStudentsInClass($class_id, $teacher_id);

        $selected_student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
        $selected_student = null;
        foreach ($students as $st) {
            if ($selected_student_id === 0 || (int)$st['id'] === $selected_student_id) {
                $selected_student = $st;
                $selected_student_id = (int)$st['id'];
                break;
            }
        }

        $curriculum = $selected_student_id > 0
            ? $guruModel->getChaptersWithSubchapters($selected_student_id, $class_id)
            : ['chapters' => [], 'total_chapters' => 0, 'overall_total' => 0, 'overall_completed' => 0, 'overall_percent' => 0];

        $history = $guruModel->getProgressHistory($class_id, $teacher_id, $selected_student_id > 0 ? $selected_student_id : null);

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/guru/progress/detail.php';
        require_once '../views/layouts/footer.php';
    }

    public function store_chapter() {
        $this->requirePostAndCsrf();
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);

        $class_id   = (int)($_POST['class_id'] ?? 0);
        $student_id = (int)($_POST['student_id'] ?? 0);
        $teacher_id = (int)$_SESSION['user']['id'];
        $title      = trim($_POST['title'] ?? '');

        if (!$guruModel->canTeacherAccessClass($teacher_id, $class_id)) {
            http_response_code(403);
            die("Akses ditolak.");
        }

        if ($title !== '' && $student_id > 0 && $guruModel->createChapter([
            'class_id'   => $class_id,
            'student_id' => $student_id,
            'teacher_id' => $teacher_id,
            'title'      => $title
        ])) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Bab Ditambahkan', 'msg' => 'Bab materi baru berhasil dibuat untuk siswa ini.'];
        } else {
            $_SESSION['flash'] = ['status' => 'warning', 'title' => 'Gagal Simpan', 'msg' => 'Judul Bab tidak boleh kosong.'];
        }
        header("Location: index.php?page=guru_progress_detail&class_id={$class_id}&student_id={$student_id}");
        exit;
    }

    public function update_chapter() {
        $this->requirePostAndCsrf();
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);

        $chapter_id = (int)($_POST['chapter_id'] ?? 0);
        $class_id   = (int)($_POST['class_id'] ?? 0);
        $student_id = (int)($_POST['student_id'] ?? 0);
        $title      = trim($_POST['title'] ?? '');

        if ($title !== '' && $guruModel->updateChapter($chapter_id, $_SESSION['user']['id'], $title)) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Bab Diperbarui', 'msg' => 'Nama Bab berhasil diperbarui.'];
        }
        header("Location: index.php?page=guru_progress_detail&class_id={$class_id}&student_id={$student_id}");
        exit;
    }

    public function delete_chapter() {
        $this->requirePostAndCsrf();
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);

        $chapter_id = (int)($_POST['chapter_id'] ?? 0);
        $class_id   = (int)($_POST['class_id'] ?? 0);
        $student_id = (int)($_POST['student_id'] ?? 0);

        if ($guruModel->deleteChapter($chapter_id, $_SESSION['user']['id'])) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Bab Dihapus', 'msg' => 'Bab beserta seluruh Subbab di dalamnya telah dihapus.'];
        }
        header("Location: index.php?page=guru_progress_detail&class_id={$class_id}&student_id={$student_id}");
        exit;
    }

    public function store_subchapter() {
        $this->requirePostAndCsrf();
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);

        $chapter_id   = (int)($_POST['chapter_id'] ?? 0);
        $class_id     = (int)($_POST['class_id'] ?? 0);
        $student_id   = (int)($_POST['student_id'] ?? 0);
        $title        = trim($_POST['title'] ?? '');
        $notes        = trim($_POST['notes'] ?? '');
        $is_completed = isset($_POST['is_completed']) ? 1 : 0;

        if ($title !== '' && $guruModel->createSubchapter($chapter_id, $_SESSION['user']['id'], $title, $notes, $is_completed)) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Subbab Ditambahkan', 'msg' => 'Subbab materi telah ditambahkan dan persentase raport diperbarui.'];
        } else {
            $_SESSION['flash'] = ['status' => 'warning', 'title' => 'Gagal Simpan', 'msg' => 'Pastikan nama Subbab telah diisi.'];
        }
        header("Location: index.php?page=guru_progress_detail&class_id={$class_id}&student_id={$student_id}");
        exit;
    }

    public function toggle_subchapter() {
        $this->requirePostAndCsrf();
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);

        $subchapter_id = (int)($_POST['subchapter_id'] ?? 0);
        $class_id      = (int)($_POST['class_id'] ?? 0);
        $student_id    = (int)($_POST['student_id'] ?? 0);
        $is_completed  = (int)($_POST['is_completed'] ?? 0);
        $notes         = isset($_POST['notes']) ? $_POST['notes'] : null;

        if ($guruModel->toggleSubchapter($subchapter_id, $_SESSION['user']['id'], $is_completed, $notes)) {
            $_SESSION['flash'] = [
                'status' => 'success',
                'title'  => $is_completed ? 'Subbab Tuntas' : 'Status Diubah',
                'msg'    => 'Persentase capaian Bab dan Raport siswa otomatis diperbarui.'
            ];
        }
        header("Location: index.php?page=guru_progress_detail&class_id={$class_id}&student_id={$student_id}");
        exit;
    }

    public function update_subchapter() {
        $this->requirePostAndCsrf();
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);

        $subchapter_id = (int)($_POST['subchapter_id'] ?? 0);
        $class_id      = (int)($_POST['class_id'] ?? 0);
        $student_id    = (int)($_POST['student_id'] ?? 0);
        $title         = trim($_POST['title'] ?? '');
        $notes         = $_POST['notes'] ?? '';
        $is_completed  = isset($_POST['is_completed']) ? 1 : 0;

        if ($title !== '' && $guruModel->updateSubchapter($subchapter_id, $_SESSION['user']['id'], $title, $notes, $is_completed)) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Subbab Diperbarui', 'msg' => 'Detail Subbab dan catatan evaluasi tersimpan.'];
        }
        header("Location: index.php?page=guru_progress_detail&class_id={$class_id}&student_id={$student_id}");
        exit;
    }

    public function delete_subchapter() {
        $this->requirePostAndCsrf();
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);

        $subchapter_id = (int)($_POST['subchapter_id'] ?? 0);
        $class_id      = (int)($_POST['class_id'] ?? 0);
        $student_id    = (int)($_POST['student_id'] ?? 0);

        if ($guruModel->deleteSubchapter($subchapter_id, $_SESSION['user']['id'])) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Subbab Dihapus', 'msg' => 'Subbab telah dihapus dan persentase dihitung ulang.'];
        }
        header("Location: index.php?page=guru_progress_detail&class_id={$class_id}&student_id={$student_id}");
        exit;
    }

    // --- CATATAN JURNAL PERTEMUAN (LEGACY LOGS) ---

    public function store_progress() {
        $this->requirePostAndCsrf();
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);

        $class_id   = (int)($_POST['class_id'] ?? 0);
        $student_id = (int)($_POST['student_id'] ?? 0);
        $teacher_id = (int)$_SESSION['user']['id'];

        if (!$guruModel->canTeacherAccessClass($teacher_id, $class_id)) {
            http_response_code(403);
            die("Akses ditolak.");
        }

        $data = [
            'class_id'   => $class_id,
            'student_id' => $student_id,
            'teacher_id' => $teacher_id,
            'date'       => $_POST['date'] ?? date('Y-m-d'),
            'topic'      => trim($_POST['topic'] ?? ''),
            'notes'      => trim($_POST['notes'] ?? '')
        ];

        if ($guruModel->saveProgress($data)) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Catatan Tersimpan', 'msg' => 'Jurnal pertemuan siswa berhasil disimpan.'];
        } else {
            $_SESSION['flash'] = ['status' => 'error', 'title' => 'Gagal Simpan', 'msg' => 'Terjadi kesalahan saat menyimpan catatan.'];
        }
        header("Location: index.php?page=guru_progress_detail&class_id={$class_id}&student_id={$student_id}");
        exit();
    }

    public function progress_update() {
        $this->requirePostAndCsrf();
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);

        $id       = (int)($_POST['id'] ?? 0);
        $class_id = (int)($_POST['class_id'] ?? 0);

        $progress = $guruModel->getProgressById($id);
        if (!$progress || (int)$progress['teacher_id'] !== (int)$_SESSION['user']['id']) {
            http_response_code(403);
            die("Akses ditolak: Data bukan milik Anda.");
        }

        $data = [
            'date'  => $_POST['date'] ?? date('Y-m-d'),
            'topic' => trim($_POST['topic'] ?? ''),
            'notes' => trim($_POST['notes'] ?? '')
        ];

        if ($guruModel->updateProgress($id, $data)) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Jurnal Diperbarui', 'msg' => 'Catatan pertemuan berhasil diperbarui.'];
        }
        header("Location: index.php?page=guru_progress_detail&class_id=" . $class_id . "&student_id=" . (int)$progress['student_id']);
        exit();
    }

    public function progress_delete() {
        $this->requirePostAndCsrf();
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);

        $id       = (int)($_POST['id'] ?? 0);
        $class_id = (int)($_POST['class_id'] ?? 0);

        $progress = $guruModel->getProgressById($id);
        if (!$progress || (int)$progress['teacher_id'] !== (int)$_SESSION['user']['id']) {
            http_response_code(403);
            die("Akses ditolak: Data bukan milik Anda.");
        }

        if ($guruModel->deleteProgress($id)) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Dihapus', 'msg' => 'Catatan pertemuan telah dihapus.'];
        }
        $redirectUrl = $class_id ? "index.php?page=guru_progress_detail&class_id=" . $class_id . "&student_id=" . (int)$progress['student_id'] : "index.php?page=guru_progress";
        header("Location: " . $redirectUrl);
        exit();
    }

    // --- MANAJEMEN MATERI ---
    public function materi() {
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);
        $teacher_id = $_SESSION['user']['id'];

        $my_classes  = $guruModel->getMyClasses($teacher_id);
        $materials   = $guruModel->getMaterials($teacher_id);
        $my_students = $guruModel->getMyStudents($teacher_id);

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/guru/materi/index.php';
        require_once '../views/layouts/footer.php';
    }

    public function materi_store() {
        $this->requirePostAndCsrf();
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);

        $class_id = (int)($_POST['class_id'] ?? 0);
        if (!$guruModel->canTeacherAccessClass($_SESSION['user']['id'], $class_id)) {
            http_response_code(403);
            die("Akses ditolak: Anda tidak mengampu kelas ini.");
        }

        $student_id = (($_POST['student_id'] ?? 'all') === 'all') ? null : (int)$_POST['student_id'];
        $data = [
            'class_id'    => $class_id,
            'student_id'  => $student_id,
            'title'       => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'video_url'   => trim($_POST['video_url'] ?? '')
        ];

        if ($guruModel->saveMaterial($data)) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Materi Diterbitkan', 'msg' => 'Materi pembelajaran berhasil disimpan.'];
        }
        header("Location: index.php?page=guru_materi");
        exit();
    }

    public function materi_update() {
        $this->requirePostAndCsrf();
        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);

        $id     = (int)($_POST['id'] ?? 0);
        $materi = $guruModel->getMaterialById($id);
        if (!$materi || !$guruModel->canTeacherAccessClass($_SESSION['user']['id'], $materi['class_id'])) {
            http_response_code(403);
            die("Akses ditolak: Materi bukan milik kelas Anda.");
        }

        $class_id = (int)($_POST['class_id'] ?? 0);
        if (!$guruModel->canTeacherAccessClass($_SESSION['user']['id'], $class_id)) {
            http_response_code(403);
            die("Akses ditolak.");
        }

        $student_id = (($_POST['student_id'] ?? 'all') === 'all') ? null : (int)$_POST['student_id'];
        $data = [
            'class_id'    => $class_id,
            'student_id'  => $student_id,
            'title'       => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'video_url'   => trim($_POST['video_url'] ?? '')
        ];

        if ($guruModel->updateMaterial($id, $data)) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Materi Diperbarui', 'msg' => 'Perubahan materi berhasil disimpan.'];
        }
        header("Location: index.php?page=guru_materi");
        exit();
    }

    public function materi_delete() {
        $this->requirePostAndCsrf();
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            header("Location: index.php?page=guru_materi");
            exit;
        }

        require_once '../models/GuruModel.php';
        $guruModel = new GuruModel($this->db);
        $materi = $guruModel->getMaterialById($id);

        if (!$materi || !$guruModel->canTeacherAccessClass($_SESSION['user']['id'], $materi['class_id'])) {
            http_response_code(403);
            die("Akses ditolak: Data bukan milik Anda.");
        }

        if ($guruModel->deleteMaterial($id)) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Terhapus', 'msg' => 'Materi belajar telah berhasil dihapus.'];
        }
        header("Location: index.php?page=guru_materi");
        exit;
    }

    // --- VALIDASI & RIWAYAT ABSENSI SISWA ---

    public function validasi_absen() {
        if ($_SESSION['user']['role'] !== 'guru') { header("Location: index.php"); exit; }

        require_once '../models/AbsensiModel.php';
        $absensiModel = new AbsensiModel($this->db);
        $teacher_id = $_SESSION['user']['id'];
        $data_absen = $absensiModel->getPendingAbsensiByGuru($teacher_id);

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/guru/validasi_absen.php';
        require_once '../views/layouts/footer.php';
    }

    public function riwayat_absen() {
        if ($_SESSION['user']['role'] !== 'guru') { header("Location: index.php"); exit; }

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
        if ($_SESSION['user']['role'] !== 'guru' || !isset($_GET['student_id'])) {
            header("Location: index.php?page=guru_riwayat");
            exit;
        }

        require_once '../models/AbsensiModel.php';
        $absensiModel = new AbsensiModel($this->db);
        $teacher_id = $_SESSION['user']['id'];
        $student_id = (int)$_GET['student_id'];

        $history = $absensiModel->getAttendanceDetailByStudent($student_id, $teacher_id);
        $siswa   = $this->userModel->getById($student_id);

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/guru/riwayat_detail.php';
        require_once '../views/layouts/footer.php';
    }

    public function proses_update_absensi() {
        $this->requirePostAndCsrf();

        $id_absensi  = (int)($_POST['id_absensi'] ?? 0);
        $status_baru = $_POST['status'] ?? '';
        $student_id  = (int)($_POST['student_id'] ?? 0);

        require_once '../models/AbsensiModel.php';
        $absensiModel = new AbsensiModel($this->db);

        $att = $absensiModel->getAttendanceByIdWithTeacher($id_absensi);
        if (!$att || (int)$att['teacher_id'] !== (int)$_SESSION['user']['id']) {
            http_response_code(403);
            die("Akses ditolak: Presensi ini bukan milik kelas Anda.");
        }

        if ($absensiModel->updateStatus($id_absensi, $status_baru)) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Status Diperbarui', 'msg' => 'Status kehadiran siswa telah diperbarui.'];
        }
        header("Location: index.php?page=guru_riwayat_detail&student_id=" . $student_id);
        exit();
    }

    public function proses_validasi() {
        $this->requirePostAndCsrf();

        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        require_once '../models/AbsensiModel.php';
        $absensiModel = new AbsensiModel($this->db);

        $att = $absensiModel->getAttendanceByIdWithTeacher($id);
        if (!$att || (int)$att['teacher_id'] !== (int)$_SESSION['user']['id']) {
            http_response_code(403);
            die("Akses ditolak: Presensi ini bukan milik kelas Anda.");
        }

        if ($absensiModel->updateStatus($id, $status)) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Validasi Berhasil', 'msg' => 'Presensi telah divalidasi dan masuk ke Riwayat Absensi.'];
        }
        header("Location: index.php?page=guru_validasi");
        exit();
    }

    public function view_absen_guru() {
        $teacher_id = $_SESSION['user']['id'];
        $daftar_hari = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $hari_indo = $daftar_hari[date('l')];
        $current_time = date('H:i:s');

        $req_schedule_id = isset($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : 0;

        $queryCekJadwal = "SELECT COUNT(*)
                           FROM class_members cm
                           JOIN classes c ON cm.class_id = c.id
                           LEFT JOIN class_bands cb ON cm.band_id = cb.id
                           WHERE COALESCE(cb.teacher_id, c.teacher_id) = :tid AND cm.day = :day";
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
                $queryJadwal = "SELECT cm.id as schedule_id, cm.band_id, cm.start_time, cm.end_time, c.name as class_name, cb.name as band_name
                                FROM class_members cm
                                JOIN classes c ON cm.class_id = c.id
                                LEFT JOIN class_bands cb ON cm.band_id = cb.id
                                WHERE cm.id = :sid AND COALESCE(cb.teacher_id, c.teacher_id) = :tid AND cm.day = :day";
                $stmtJadwal = $this->db->prepare($queryJadwal);
                $stmtJadwal->execute([':sid' => $req_schedule_id, ':tid' => $teacher_id, ':day' => $hari_indo]);
                $active_schedule = $stmtJadwal->fetch(PDO::FETCH_ASSOC);

                if ($active_schedule) {
                    $start_absen_time = strtotime($active_schedule['start_time']) - 300;
                    $end_absen_time   = strtotime($active_schedule['end_time']);
                    $time_now         = strtotime($current_time);

                    if ($time_now < $start_absen_time) {
                        $absen_belum_dibuka = true;
                        $jam_mulai = date('H:i', strtotime($active_schedule['start_time']));
                        $jam_buka  = date('H:i', $start_absen_time);
                        $active_schedule = null;
                    } elseif ($time_now > $end_absen_time) {
                        $absen_closed = true;
                        $active_schedule = null;
                    }
                } else {
                    $no_schedule = true;
                }
            } else {
                $queryJadwal = "SELECT cm.id as schedule_id, cm.band_id, cm.start_time, cm.end_time, c.name as class_name, cb.name as band_name
                                FROM class_members cm
                                JOIN classes c ON cm.class_id = c.id
                                LEFT JOIN class_bands cb ON cm.band_id = cb.id
                                WHERE COALESCE(cb.teacher_id, c.teacher_id) = :tid AND cm.day = :day 
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
                $active_schedule = $stmtJadwal->fetch(PDO::FETCH_ASSOC);
            }

            if ($active_schedule) {
                $canonicalSid = (int)$active_schedule['schedule_id'];
                if (!empty($active_schedule['band_id'])) {
                    $stmtMin = $this->db->prepare("SELECT MIN(id) FROM class_members WHERE band_id = ?");
                    $stmtMin->execute([(int)$active_schedule['band_id']]);
                    $minId = (int)$stmtMin->fetchColumn();
                    if ($minId > 0) {
                        $canonicalSid = $minId;
                        $active_schedule['schedule_id'] = $canonicalSid;
                    }
                }
                $stmtCekAbsen = $this->db->prepare("SELECT COUNT(*) FROM teacher_attendances WHERE teacher_id = :tid AND schedule_id = :sid AND date = :date");
                $stmtCekAbsen->execute([':tid' => $teacher_id, ':sid' => $canonicalSid, ':date' => date('Y-m-d')]);
                $already_checked_in = $stmtCekAbsen->fetchColumn() > 0;
            } elseif (!$absen_belum_dibuka && !$absen_closed) {
                $queryFuture = "SELECT cm.start_time
                                FROM class_members cm
                                JOIN classes c ON cm.class_id = c.id
                                LEFT JOIN class_bands cb ON cm.band_id = cb.id
                                WHERE COALESCE(cb.teacher_id, c.teacher_id) = :tid AND cm.day = :day 
                                AND SUBTIME(cm.start_time, '00:05:00') > :current_time
                                ORDER BY cm.start_time ASC LIMIT 1";
                $stmtFuture = $this->db->prepare($queryFuture);
                $stmtFuture->execute([':tid' => $teacher_id, ':day' => $hari_indo, ':current_time' => $current_time]);
                $future_class = $stmtFuture->fetch();

                if ($future_class) {
                    $absen_belum_dibuka = true;
                    $jam_mulai = date('H:i', strtotime($future_class['start_time']));
                    $jam_buka  = date('H:i', strtotime($future_class['start_time']) - 300);
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
