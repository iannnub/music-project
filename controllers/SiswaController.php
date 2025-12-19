<?php
require_once '../models/AbsensiModel.php'; 
require_once '../helpers/ImageHelper.php'; 

class SiswaController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // --- 1. DASHBOARD UTAMA SISWA ---
    public function index() {
        $student_id = $_SESSION['user']['id'];

        // A. QUERY JADWAL
        $queryJadwal = "SELECT schedules.*, classes.name as class_name, classes.type, users.name as teacher_name,
                               attendances.status as status_kehadiran,
                               attendances.created_at as waktu_absen_masuk
                  FROM schedules
                  JOIN classes ON schedules.class_id = classes.id
                  JOIN users ON classes.teacher_id = users.id
                  JOIN class_members ON classes.id = class_members.class_id
                  LEFT JOIN attendances ON schedules.id = attendances.schedule_id 
                                       AND attendances.student_id = :sid_join
                                       AND attendances.date = CURRENT_DATE
                  WHERE class_members.student_id = :sid_where
                  ORDER BY FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), start_time ASC";

        $stmt = $this->db->prepare($queryJadwal);
        $stmt->execute([':sid_join' => $student_id, ':sid_where' => $student_id]);
        $jadwal_saya = $stmt->fetchAll();

        // B. QUERY PROGRESS TERAKHIR
        $queryProg = "SELECT * FROM progress_logs WHERE student_id = :sid ORDER BY date DESC LIMIT 1";
        $stmt2 = $this->db->prepare($queryProg);
        $stmt2->execute([':sid' => $student_id]);
        $last_progress = $stmt2->fetch();

        // C. QUERY PENGINGAT TUGAS
        $queryPending = "SELECT assignments.*, classes.name as class_name 
                         FROM assignments
                         JOIN classes ON assignments.class_id = classes.id
                         JOIN class_members ON classes.id = class_members.class_id
                         LEFT JOIN submissions ON assignments.id = submissions.assignment_id 
                               AND submissions.student_id = :sid_join
                         WHERE class_members.student_id = :sid_where 
                         AND submissions.id IS NULL
                         ORDER BY assignments.deadline ASC";

        $stmt3 = $this->db->prepare($queryPending);
        $stmt3->execute([':sid_join' => $student_id, ':sid_where' => $student_id]);
        $tugas_pending = $stmt3->fetchAll();

        // D. CEK STATUS SPP BULAN INI
        $bulan_ini = date('n'); 
        $tahun_ini = date('Y');
        $querySPP = "SELECT id FROM payments WHERE student_id = ? AND month = ? AND year = ? AND status = 'Lunas'";
        $stmt4 = $this->db->prepare($querySPP);
        $stmt4->execute([$student_id, $bulan_ini, $tahun_ini]);
        $is_lunas = $stmt4->rowCount() > 0;

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/siswa/dashboard.php';
        require_once '../views/layouts/footer.php';
    }

    // --- 2. FITUR ABSENSI KAMERA & LOKASI ---
    private function hitungJarak($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000; 
        $latFrom = deg2rad($lat1); $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2); $lonTo = deg2rad($lon2);
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    public function proses_absen() {
        $absensiModel = new AbsensiModel($this->db);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $lat_sekolah = -8.28994280771604; 
            $long_sekolah = 113.52795518004542;
            $radius_max = 500; // jarak radius dari tempat sekolah

            $student_id = $_SESSION['user']['id'];
            $schedule_id = $_POST['schedule_id'];
            $foto_base64 = $_POST['foto_base64']; 
            $lat_siswa = $_POST['lat'];
            $long_siswa = $_POST['long'];
            $tanggal_hari_ini = date('Y-m-d');

            // Validasi GPS
            if (empty($lat_siswa) || empty($long_siswa)) {
                $_SESSION['flash'] = ['status' => 'error', 'title' => 'GPS Error', 'msg' => 'Gagal mendeteksi lokasi. Pastikan izin GPS aktif!'];
                header("Location: index.php?page=dashboard_siswa");
                exit;
            }

            // Validasi Jarak
            $jarak = $this->hitungJarak($lat_sekolah, $long_sekolah, $lat_siswa, $long_siswa);
            if ($jarak > $radius_max) {
                $_SESSION['flash'] = ['status' => 'error', 'title' => 'Di Luar Jangkauan', 'msg' => 'Jarak kamu ' . round($jarak) . 'm dari lokasi. Absen harus di sekolah!'];
                header("Location: index.php?page=dashboard_siswa");
                exit;
            }

            // Validasi Double Absen
            if ($absensiModel->cekSudahAbsen($student_id, $schedule_id, $tanggal_hari_ini)) {
                $_SESSION['flash'] = ['status' => 'warning', 'title' => 'Sudah Absen', 'msg' => 'Kamu sudah melakukan absensi untuk sesi ini hari ini.'];
                header("Location: index.php?page=dashboard_siswa");
                exit;
            }

            // Proses Gambar
            $img_parts = explode(";base64,", $foto_base64);
            $image_base64 = base64_decode($img_parts[1]);
            $nama_file = 'absen_' . $schedule_id . '_' . $student_id . '_' . time() . '.jpg';
            $folder_tujuan = '../public/uploads/absensi/';
            if (!is_dir($folder_tujuan)) mkdir($folder_tujuan, 0777, true);
            file_put_contents($folder_tujuan . $nama_file, $image_base64);

            $data = [
                'schedule_id' => $schedule_id, 'student_id' => $student_id, 'date' => $tanggal_hari_ini,
                'photo' => $nama_file, 'lat' => $lat_siswa, 'long' => $long_siswa
            ];

            if ($absensiModel->create($data)) {
                $_SESSION['flash'] = ['status' => 'success', 'title' => 'Absen Berhasil', 'msg' => 'Kehadiran kamu hari ini sudah tercatat. Semangat belajarnya!'];
            } else {
                $_SESSION['flash'] = ['status' => 'error', 'title' => 'Database Error', 'msg' => 'Gagal menyimpan data absensi.'];
            }
            header("Location: index.php?page=dashboard_siswa");
            exit;
        }
    }

    // --- 3. FITUR AKADEMIK: MATERI ---
    public function materi() {
        $student_id = $_SESSION['user']['id'];
        $query = "SELECT materials.*, classes.name as class_name, users.name as teacher_name
                  FROM materials JOIN classes ON materials.class_id = classes.id
                  JOIN users ON classes.teacher_id = users.id
                  JOIN class_members ON classes.id = class_members.class_id
                  WHERE class_members.student_id = :student_id ORDER BY materials.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':student_id' => $student_id]);
        $materi = $stmt->fetchAll();

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/siswa/materi.php'; 
        require_once '../views/layouts/footer.php';
    }

    // --- 4. FITUR AKADEMIK: TUGAS ---
    public function tugas() {
    $student_id = $_SESSION['user']['id'];
    $query = "SELECT assignments.*, classes.name as class_name, 
                     submissions.file_proof, 
                     submissions.link_proof, 
                     submissions.notes,
                     submissions.grade, 
                     submissions.teacher_feedback, 
                     submissions.submitted_at
              FROM assignments 
              JOIN classes ON assignments.class_id = classes.id
              JOIN class_members ON classes.id = class_members.class_id
              LEFT JOIN submissions ON assignments.id = submissions.assignment_id AND submissions.student_id = :sid_join
              WHERE class_members.student_id = :sid_where 
              ORDER BY assignments.deadline ASC";
              
    $stmt = $this->db->prepare($query);
    $stmt->execute([':sid_join' => $student_id, ':sid_where' => $student_id]);
    $tugas = $stmt->fetchAll();

    require_once '../views/layouts/header.php';
    require_once '../views/layouts/sidebar.php';
    require_once '../views/layouts/topbar.php';
    require_once '../views/siswa/tugas.php'; 
    require_once '../views/layouts/footer.php';
}

    // --- 5. FITUR AKADEMIK: UPLOAD TUGAS ---
    public function upload_tugas() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $assignment_id = $_POST['assignment_id'];
        $student_id = $_SESSION['user']['id'];
        $notes = $_POST['notes'];
        $link_proof = $_POST['link_proof']; // Menangkap input link

        $file_to_save = null; // Default kosong

        // 1. PROSES FILE (Jika ada)
        if (!empty($_FILES['file_proof']['name'])) {
            $targetDir = "../public/uploads/tugas/";
            $prefix = "tugas_" . $assignment_id . "_" . $student_id;
            $uploadResult = ImageHelper::uploadAndCompress($_FILES['file_proof'], $targetDir, $prefix);

            if ($uploadResult['status']) {
                $file_to_save = $uploadResult['fileName'];
            } else {
                $_SESSION['flash'] = ['status' => 'error', 'title' => 'Gagal', 'msg' => $uploadResult['msg']];
                header("Location: index.php?page=siswa_tugas");
                exit;
            }
        }

        // 2. VALIDASI MINIMAL SALAH SATU ISI
        if (empty($file_to_save) && empty($link_proof)) {
            $_SESSION['flash'] = ['status' => 'warning', 'title' => 'Gak Bisa!', 'msg' => 'Wajib upload file ATAU isi link!'];
            header("Location: index.php?page=siswa_tugas");
            exit;
        }

        // 3. SIMPAN KE DATABASE (Update jika sudah pernah kumpul)
        $del = $this->db->prepare("DELETE FROM submissions WHERE assignment_id=? AND student_id=?");
        $del->execute([$assignment_id, $student_id]);

        $stmt = $this->db->prepare("INSERT INTO submissions (assignment_id, student_id, file_proof, link_proof, notes) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$assignment_id, $student_id, $file_to_save, $link_proof, $notes])) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Mantap!', 'msg' => 'Tugas berhasil dikirim!'];
        } else {
            $_SESSION['flash'] = ['status' => 'error', 'title' => 'Error', 'msg' => 'Database bermasalah.'];
        }
        header("Location: index.php?page=siswa_tugas");
        exit;
    }
}

    // --- 6. FITUR AKADEMIK: PROGRESS REPORT ---
    public function progress() {
        $student_id = $_SESSION['user']['id'];
        $query = "SELECT progress_logs.*, classes.name as class_name, users.name as teacher_name 
                  FROM progress_logs JOIN classes ON progress_logs.class_id = classes.id
                  JOIN users ON progress_logs.teacher_id = users.id
                  WHERE progress_logs.student_id = :student_id ORDER BY progress_logs.date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':student_id' => $student_id]);
        $progress = $stmt->fetchAll();

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/siswa/progress.php';
        require_once '../views/layouts/footer.php';
    }

    // --- 7. FITUR: RIWAYAT ABSENSI ---
    public function riwayat_absensi() {
        $student_id = $_SESSION['user']['id'];
        $query = "SELECT attendances.*, classes.name as class_name, schedules.start_time, schedules.end_time
                  FROM attendances JOIN schedules ON attendances.schedule_id = schedules.id
                  JOIN classes ON schedules.class_id = classes.id
                  WHERE attendances.student_id = :student_id ORDER BY attendances.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':student_id' => $student_id]);
        $riwayat = $stmt->fetchAll();

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/siswa/riwayat_absensi.php';
        require_once '../views/layouts/footer.php';
    }

    // --- 8. FITUR: INFO PEMBAYARAN ---
    public function pembayaran() {
        $student_id = $_SESSION['user']['id'];
        $query = "SELECT * FROM payments WHERE student_id = :student_id ORDER BY year DESC, month DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':student_id' => $student_id]);
        $pembayaran = $stmt->fetchAll();

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/siswa/pembayaran.php';
        require_once '../views/layouts/footer.php';
    }

    // --- 9. CETAK RAPORT ---
    public function cetak_raport() {
        $student_id = $_SESSION['user']['id'];
        $query = "SELECT progress_logs.*, classes.name as class_name, users.name as teacher_name 
                  FROM progress_logs JOIN classes ON progress_logs.class_id = classes.id
                  JOIN users ON progress_logs.teacher_id = users.id
                  WHERE progress_logs.student_id = :student_id ORDER BY progress_logs.date ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':student_id' => $student_id]);
        $progress = $stmt->fetchAll();
        require_once '../views/siswa/cetak_raport.php';
    }
}
?>