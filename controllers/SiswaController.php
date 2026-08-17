<?php
require_once '../models/AbsensiModel.php'; 
require_once '../helpers/ImageHelper.php'; 

class SiswaController {
    private $db;
    private $userModel;
    private $classModel;

    public function __construct($db) {
        $this->db = $db;
        require_once '../models/UserModel.php';
        require_once '../models/KelasModel.php'; 
        $this->userModel = new UserModel($this->db);
        $this->classModel = new KelasModel($this->db);
    }

    // --- 1. DASHBOARD & MANAJEMEN SISWA ---
    public function index() {
        if ($_SESSION['user']['role'] == 'admin') {
            $stmtSiswa = $this->db->prepare("SELECT * FROM users WHERE role = 'siswa' ORDER BY name ASC");
            $stmtSiswa->execute();
            $siswa = $stmtSiswa->fetchAll();

            $queryKelas = "SELECT c.*, u.name as teacher_name FROM classes c LEFT JOIN users u ON c.teacher_id = u.id ORDER BY c.name ASC";
            $stmtKelas = $this->db->prepare($queryKelas);
            $stmtKelas->execute();
            $classes = $stmtKelas->fetchAll(); 

            require_once '../views/layouts/header.php';
            require_once '../views/layouts/sidebar.php';
            require_once '../views/layouts/topbar.php';
            require_once '../views/admin/siswa/index.php';
            require_once '../views/layouts/footer.php';
        } else {
            $student_id = $_SESSION['user']['id'];
            require_once '../models/PembayaranModel.php';
            $pembayaranModel = new PembayaranModel($this->db);

            // A. Ambil Jadwal & Status Absen
            $queryJadwal = "SELECT cm.id as schedule_id, cm.day, cm.start_time, cm.end_time, 
                                   c.name as class_name, c.type, u.name as teacher_name,
                                   a.status as status_kehadiran, a.created_at as waktu_absen_masuk
                            FROM class_members cm
                            JOIN classes c ON cm.class_id = c.id
                            JOIN users u ON c.teacher_id = u.id
                            LEFT JOIN attendances a ON cm.id = a.schedule_id 
                                                   AND a.student_id = :sid_join
                                                   AND a.date = CURRENT_DATE
                            WHERE cm.student_id = :sid_where
                            ORDER BY FIELD(cm.day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), cm.start_time ASC";

            $stmt = $this->db->prepare($queryJadwal);
            $stmt->execute([':sid_join' => $student_id, ':sid_where' => $student_id]);
            $jadwal_saya = $stmt->fetchAll();

            $data['next_class'] = null;
            $hari_ini = date('l'); // English day name
            $map_hari = ['Monday'=>'Senin', 'Tuesday'=>'Selasa', 'Wednesday'=>'Rabu', 'Thursday'=>'Kamis', 'Friday'=>'Jumat', 'Saturday'=>'Sabtu', 'Sunday'=>'Minggu'];
            $hari_indo = $map_hari[$hari_ini];
            $jam_sekarang = date('H:i:s');

            foreach ($jadwal_saya as $j) {
                if ($j['day'] == $hari_indo && $j['end_time'] >= $jam_sekarang) {
                    $data['next_class'] = $j;
                    break; // Ambil yang paling dekat jamnya (yang belum selesai)
                }
            }

            // B. Progress Terakhir
            $queryProg = "SELECT * FROM progress_logs WHERE student_id = :sid ORDER BY date DESC LIMIT 1";
            $stmt2 = $this->db->prepare($queryProg);
            $stmt2->execute([':sid' => $student_id]);
            $last_progress = $stmt2->fetch();

            // C. Tugas Belum Dikerjakan (Logic Filter diperbaiki)
            $queryPending = "SELECT assignments.*, classes.name as class_name 
                             FROM assignments
                             JOIN classes ON assignments.class_id = classes.id
                             JOIN class_members ON classes.id = class_members.class_id
                             LEFT JOIN submissions ON assignments.id = submissions.assignment_id 
                                   AND submissions.student_id = :sid_join
                             WHERE (class_members.student_id = :sid_where OR assignments.student_id = :sid_privat)
                             AND submissions.id IS NULL
                             GROUP BY assignments.id
                             ORDER BY assignments.deadline ASC";
            $stmt3 = $this->db->prepare($queryPending);
            $stmt3->execute([':sid_join' => $student_id, ':sid_where' => $student_id, ':sid_privat' => $student_id]);
            $tugas_pending = $stmt3->fetchAll();

            // D. Status SPP
            $bulan_ini = date('n'); 
            $tahun_ini = date('Y');
            $data['is_lunas'] = $pembayaranModel->checkStatusSiswa($student_id, $bulan_ini, $tahun_ini);

            // Mengirim variabel ke view
            $data['jadwal_saya'] = $jadwal_saya;
            $data['last_progress'] = $last_progress;
            $data['tugas_pending'] = $tugas_pending;

            $this->renderView('siswa/dashboard', $data);
        }
    }
    public function store() {
    // 1. TANGKAP DATA JADWAL (Array)
    $class_ids   = $_POST['class_id']; 
    $days        = $_POST['day'];
    $start_times = $_POST['start_time'];
    $end_times   = $_POST['end_time'];

    try {
        $this->db->beginTransaction();

        // 2. TAHAP VALIDASI: CEK KONFLIK UNTUK SEMUA JADWAL YANG DIINPUT
        // Kita cek satu-satu sebelum melakukan INSERT apa pun ke database
        foreach ($class_ids as $key => $class_id) {
            // Ambil teacher_id untuk kelas ini
            $stmt = $this->db->prepare("SELECT teacher_id FROM classes WHERE id = ?");
            $stmt->execute([$class_id]);
            $classData = $stmt->fetch();
            $teacher_id = $classData['teacher_id'] ?? null;

            // Validasi Konflik
            if ($this->classModel->isConflict($teacher_id, $days[$key], $start_times[$key], $end_times[$key])) {
                // Jika ada salah satu yang bentrok, lempar exception untuk membatalkan semua
                throw new Exception("Jadwal di hari " . $days[$key] . " jam " . $start_times[$key] . " bentrok dengan jadwal Guru!");
            }
        }

        // 3. SIMPAN AKUN SISWA
        $userData = [
            'username' => $_POST['username'],
            'name'     => $_POST['name'],
            'email'    => $_POST['email'],
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'role'     => 'siswa',
            'phone'    => $_POST['phone'],
            'parent_name' => $_POST['parent_name'] ?? null
        ];

        // create() harus mengembalikan lastInsertId
        $newStudentId = $this->userModel->create($userData);

        if (!$newStudentId) {
            throw new Exception("Gagal membuat akun siswa. Username mungkin sudah digunakan.");
        }

        // 4. SIMPAN SEMUA JADWAL (LOOPING INSERT)
        foreach ($class_ids as $key => $class_id) {
            $memberData = [
                'student_id' => $newStudentId,
                'class_id'   => $class_id,
                'day'        => $days[$key],
                'start_time' => $start_times[$key],
                'end_time'   => $end_times[$key]
            ];

            if (!$this->classModel->addMember($memberData)) {
                throw new Exception("Gagal menyimpan plotting jadwal ke-" . ($key + 1));
            }
        }

        // Jika semua lancar, baru Commit ke database
        $this->db->commit();

        $_SESSION['flash'] = [
            'status' => 'success',
            'title'  => 'Pendaftaran Berhasil!',
            'msg'    => 'Akun siswa berhasil dibuat dengan ' . count($class_ids) . ' jadwal latihan.'
        ];

    } catch (Exception $e) {
        // Jika ada error/bentrok di tengah jalan, batalkan semua (Rollback)
        $this->db->rollBack();
        $_SESSION['flash'] = [
            'status' => 'error',
            'title'  => 'Gagal Mendaftar',
            'msg'    => $e->getMessage()
        ];
    }

    header("Location: index.php?page=siswa");
    exit();
}

// --- FITUR TAMBAH JADWAL SATUAN (Buat Siswa Lama) ---
public function add_schedule_item() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $student_id = $_POST['student_id'];
        $class_id   = $_POST['class_id'];
        $day        = $_POST['day'];
        $start_time = $_POST['start_time'];
        $end_time   = $_POST['end_time'];

        // 1. Ambil teacher_id dari kelas yang dipilih
        $stmt = $this->db->prepare("SELECT teacher_id FROM classes WHERE id = ?");
        $stmt->execute([$class_id]);
        $teacher_id = $stmt->fetch()['teacher_id'] ?? null;

        // 2. Cek Konflik Jadwal Guru
        if ($this->classModel->isConflict($teacher_id, $day, $start_time, $end_time)) {
            $_SESSION['flash'] = [
                'status' => 'warning',
                'title'  => 'Jadwal Bentrok!',
                'msg'    => 'Guru pengampu sudah memiliki jadwal lain di jam tersebut.'
            ];
            header("Location: index.php?page=siswa_manage_jadwal&id=" . $student_id);
            exit();
        }

        // 3. Eksekusi Simpan ke class_members
        $data = [
            'student_id' => $student_id,
            'class_id'   => $class_id,
            'day'        => $day,
            'start_time' => $start_time,
            'end_time'   => $end_time
        ];

        if ($this->classModel->addMember($data)) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Berhasil', 'msg' => 'Jadwal tambahan telah didaftarkan.'];
        } else {
            $_SESSION['flash'] = ['status' => 'error', 'title' => 'Gagal', 'msg' => 'Terjadi kesalahan database.'];
        }

        header("Location: index.php?page=siswa_manage_jadwal&id=" . $student_id);
        exit();
    }
}

// --- FITUR HAPUS JADWAL SATUAN ---
public function delete_schedule_item() {
    $id = $_GET['id']; // ID dari tabel class_members
    $student_id = $_GET['student_id']; // Buat redirect balik

    // Eksekusi hapus baris di class_members
    $stmt = $this->db->prepare("DELETE FROM class_members WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['flash'] = ['status' => 'success', 'title' => 'Terhapus', 'msg' => 'Sesi latihan berhasil dihapus dari daftar.'];
    } else {
        $_SESSION['flash'] = ['status' => 'error', 'title' => 'Gagal', 'msg' => 'Gagal menghapus data.'];
    }

    header("Location: index.php?page=siswa_manage_jadwal&id=" . $student_id);
    exit();
}

    public function update() {
        $id = $_POST['id'];
        $data = [
            'username' => $_POST['username'],
            'name'     => $_POST['name'],
            'email'    => $_POST['email'],
            'phone'    => $_POST['phone'],
            'parent_name' => $_POST['parent_name'] ?? null
        ];
        
        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        if ($this->userModel->update($id, $data)) {
            $_SESSION['flash'] = [
                'status' => 'success',
                'title'  => 'Updated!',
                'msg'    => 'Data siswa berhasil diperbarui.'
            ];
        } else {
            $_SESSION['flash'] = [
                'status' => 'error',
                'title'  => 'Update Gagal',
                'msg'    => 'Cek kembali data yang diinput (Username mungkin sudah ada).'
            ];
        }
        header("Location: index.php?page=siswa");
        exit();
    }

    public function manage_jadwal() {
    $id_siswa = $_GET['id'];
    
    // 1. Ambil info nama siswa dari UserModel
    $student = $this->userModel->getById($id_siswa); 

    // 2. Ambil jadwal aktif siswa tersebut dari UserModel
    $jadwal_aktif = $this->userModel->getStudentSchedule($id_siswa);

    // 3. Ambil daftar semua kelas untuk pilihan dropdown (Pake KelasModel)
    require_once '../models/KelasModel.php';
    $kelasModel = new KelasModel($this->db);
    $all_classes = $kelasModel->getAll();

    // 4. Render View
    require_once '../views/layouts/header.php';
    require_once '../views/layouts/sidebar.php';
    require_once '../views/layouts/topbar.php';
    require_once '../views/admin/siswa/manage_jadwal.php'; 
    require_once '../views/layouts/footer.php';
}

public function update_jadwal() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_member = $_POST['id_member'];
        $student_id = $_POST['student_id'];
        
        $data = [
            'class_id'   => $_POST['class_id'],
            'day'        => $_POST['day'],
            'start_time' => $_POST['start_time'],
            'end_time'   => $_POST['end_time']
        ];

        if ($this->userModel->updateJadwalSiswa($id_member, $data)) {
            $_SESSION['flash'] = [
                'status' => 'success',
                'title'  => 'Jadwal Diperbarui',
                'msg'    => 'Plotting kelas siswa telah berhasil diupdate.'
            ];
        } else {
            $_SESSION['flash'] = [
                'status' => 'error',
                'title'  => 'Gagal Update',
                'msg'    => 'Terjadi kesalahan sistem.'
            ];
        }
        // Redirect kembali ke halaman manage agar admin bisa cek hasilnya
        header("Location: index.php?page=siswa_manage_jadwal&id=" . $student_id);
        exit();
    }
}

    // --- FITUR HAPUS SISWA (ADMIN) ---
    public function delete() {
        if (isset($_GET['id'])) {
            if ($this->userModel->delete($_GET['id'])) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Terhapus',
                    'msg'    => 'Siswa dan seluruh datanya telah dibersihkan.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Hapus',
                    'msg'    => 'Data gagal dihapus dari sistem.'
                ];
            }
        }
        header("Location: index.php?page=siswa");
        exit();
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
            
            $student_id = $_SESSION['user']['id'];
            $schedule_id = $_POST['schedule_id'];
            $foto_base64 = $_POST['foto_base64']; 
            
            // Koordinat tetap ditangkap sebagai 0 agar tidak merusak struktur database
            $lat_siswa = $_POST['lat'] ?? 0;
            $long_siswa = $_POST['long'] ?? 0;
            $tanggal_hari_ini = date('Y-m-d');

            // 1. Validasi Double Absen (Tetap Dipertahankan demi Integritas Data)
            if ($absensiModel->cekSudahAbsen($student_id, $schedule_id, $tanggal_hari_ini)) {
                $_SESSION['flash'] = [
                    'status' => 'warning', 
                    'title' => 'Sudah Absen', 
                    'msg' => 'Kamu sudah melakukan presensi hari ini.'
                ];
                header("Location: index.php?page=dashboard_siswa"); exit;
            }

            // 2. Proses Penyimpanan Gambar (Photo Proof)
            try {
                $img_parts = explode(";base64,", $foto_base64);
                $image_base64 = base64_decode($img_parts[1]);
                $nama_file = 'absen_' . $schedule_id . '_' . $student_id . '_' . time() . '.jpg';
                $folder_tujuan = '../public/uploads/absensi/';
                
                if (!is_dir($folder_tujuan)) mkdir($folder_tujuan, 0777, true);
                file_put_contents($folder_tujuan . $nama_file, $image_base64);

                // 3. Eksekusi Simpan Data ke Database
                $data = [
                    'schedule_id' => $schedule_id, 
                    'student_id'  => $student_id, 
                    'date'         => $tanggal_hari_ini,
                    'photo'        => $nama_file, 
                    'lat'          => $lat_siswa, 
                    'long'         => $long_siswa
                ];

                if ($absensiModel->create($data)) {
                    $_SESSION['flash'] = [
                        'status' => 'success', 
                        'title' => 'Berhasil!', 
                        'msg' => 'Presensi kamu telah tercatat di sistem.'
                    ];
                } else {
                    throw new Exception("Gagal menyimpan data ke database.");
                }

            } catch (Exception $e) {
                $_SESSION['flash'] = [
                    'status' => 'error', 
                    'title' => 'Gagal Absen', 
                    'msg' => 'Terjadi kesalahan teknis saat memproses foto.'
                ];
            }

            header("Location: index.php?page=dashboard_siswa"); exit;
        }
    }

    // --- 3. FITUR AKADEMIK: MATERI ---
    public function materi() {
    $student_id = $_SESSION['user']['id'];
    $query = "SELECT materials.*, classes.name as class_name, users.name as teacher_name
              FROM materials 
              JOIN classes ON materials.class_id = classes.id
              JOIN users ON classes.teacher_id = users.id
              JOIN class_members ON classes.id = class_members.class_id
              WHERE class_members.student_id = :student_id 
              AND (materials.student_id IS NULL OR materials.student_id = :student_id_privat)
              GROUP BY materials.id -- KUNCI: Biar gak duplikat kalau murid punya banyak jadwal
              ORDER BY materials.created_at DESC";
              
    $stmt = $this->db->prepare($query);
    $stmt->execute([
        ':student_id' => $student_id,
        ':student_id_privat' => $student_id
    ]);
    $materi = $stmt->fetchAll();

    $this->renderView('siswa/materi', ['materi' => $materi]);
}

    // --- 4. FITUR AKADEMIK: TUGAS ---
    public function tugas() {
        $student_id = $_SESSION['user']['id'];
        
        // UPGRADE QUERY: Join ke tabel users (Teacher) untuk ambil GDrive Link
        $query = "SELECT 
                    assignments.*, 
                    classes.name as class_name, 
                    teacher.name as teacher_name,
                    teacher.ig_link as teacher_ig, -- AMBIL LINK MASTER GURU
                    submissions.status as submission_status, -- STATUS BARU (PENDING/VERIFIED)
                    submissions.grade, 
                    submissions.teacher_feedback, 
                    submissions.submitted_at
                  FROM assignments 
                  JOIN classes ON assignments.class_id = classes.id
                  JOIN users as teacher ON classes.teacher_id = teacher.id -- JOIN KE GURU
                  LEFT JOIN class_members ON classes.id = class_members.class_id
                  LEFT JOIN submissions ON assignments.id = submissions.assignment_id 
                                        AND submissions.student_id = :sid_join
                  WHERE (class_members.student_id = :sid_where OR assignments.student_id = :sid_where_privat)
                  GROUP BY assignments.id
                  ORDER BY assignments.deadline ASC";
                  
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':sid_join' => $student_id, 
            ':sid_where' => $student_id, 
            ':sid_where_privat' => $student_id
        ]);
        $tugas = $stmt->fetchAll();

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/siswa/tugas.php'; 
        require_once '../views/layouts/footer.php';
    }

    // --- FITUR BARU: HAPUS SETORAN TUGAS ---
    public function hapus_setoran() {
        if (isset($_GET['id'])) {
            $assignment_id = $_GET['id'];
            $student_id = $_SESSION['user']['id'];

            // 1. Validasi: Cek apakah tugas sudah dinilai?
            // Kita tidak boleh menghapus data yang sudah masuk tahap evaluasi (Integritas Data)
            $queryCheck = "SELECT file_proof, grade FROM submissions WHERE assignment_id = ? AND student_id = ?";
            $stmtCheck = $this->db->prepare($queryCheck);
            $stmtCheck->execute([$assignment_id, $student_id]);
            $submission = $stmtCheck->fetch();

            if (!$submission) {
                $_SESSION['flash'] = ['status' => 'error', 'title' => 'Gagal', 'msg' => 'Data setoran tidak ditemukan.'];
                header("Location: index.php?page=siswa_tugas"); exit;
            }

            if (!empty($submission['grade'])) {
                $_SESSION['flash'] = ['status' => 'warning', 'title' => 'Ditolak', 'msg' => 'Tugas sudah dinilai guru, tidak bisa dihapus!'];
                header("Location: index.php?page=siswa_tugas"); exit;
            }

            // 2. Hapus File Fisik (Jika ada)
            if (!empty($submission['file_proof'])) {
                $filePath = "../public/uploads/tugas/" . $submission['file_proof'];
                if (file_exists($filePath)) {
                    unlink($filePath); // Menghapus file dari server untuk menghemat storage
                }
            }

            // 3. Eksekusi Hapus Data dari Database
            $queryDel = "DELETE FROM submissions WHERE assignment_id = ? AND student_id = ?";
            $stmtDel = $this->db->prepare($queryDel);
            
            if ($stmtDel->execute([$assignment_id, $student_id])) {
                $_SESSION['flash'] = ['status' => 'success', 'title' => 'Terhapus', 'msg' => 'Setoran tugas berhasil dibatalkan.'];
            } else {
                $_SESSION['flash'] = ['status' => 'error', 'title' => 'Gagal', 'msg' => 'Terjadi kesalahan database.'];
            }

            header("Location: index.php?page=siswa_tugas");
            exit;
        }
    }

    // --- 5. FITUR AKADEMIK: UPLOAD TUGAS ---
    public function upload_tugas() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $assignment_id = $_POST['assignment_id'];
            $student_id = $_SESSION['user']['id'];
            $notes = $_POST['notes'] ?? '';

            // 1. Validasi: Cek apakah tugas ini sudah di-ACC (Selesai) oleh Guru?
            $check = $this->db->prepare("SELECT status FROM submissions WHERE assignment_id = ? AND student_id = ?");
            $check->execute([$assignment_id, $student_id]);
            $existing = $check->fetch();

            if ($existing && $existing['status'] == 'Selesai') {
                $_SESSION['flash'] = [
                    'status' => 'warning', 
                    'title' => 'Terkunci', 
                    'msg' => 'Tugas sudah di-ACC Guru dan tidak bisa diubah!'
                ];
                header("Location: index.php?page=siswa_tugas"); exit;
            }

            // 2. Logika Konfirmasi: Upsert (Update atau Insert) data submission
            // Kita set status menjadi 'Menunggu Verifikasi'
            if ($existing) {
                // Jika sudah pernah konfirmasi tapi mau update catatan
                $stmt = $this->db->prepare("UPDATE submissions SET 
                                            notes = ?, 
                                            status = 'Menunggu Verifikasi', 
                                            submitted_at = NOW() 
                                            WHERE assignment_id = ? AND student_id = ?");
                $result = $stmt->execute([$notes, $assignment_id, $student_id]);
            } else {
                // Jika pertama kali konfirmasi
                $stmt = $this->db->prepare("INSERT INTO submissions (assignment_id, student_id, status, notes, submitted_at) 
                                            VALUES (?, ?, 'Menunggu Verifikasi', ?, NOW())");
                $result = $stmt->execute([$assignment_id, $student_id, $notes]);
            }

            if ($result) {
                $_SESSION['flash'] = [
                    'status' => 'success', 
                    'title' => 'Berhasil Konfirmasi', 
                    'msg' => 'Status berubah: Menunggu Verifikasi Guru.'
                ];
            } else {
                $_SESSION['flash'] = ['status' => 'error', 'title' => 'Gagal', 'msg' => 'Terjadi kesalahan sistem.'];
            }
            
            header("Location: index.php?page=siswa_tugas"); exit;
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
        $this->renderView('siswa/progress', ['progress' => $progress]);
    }

    private function renderView($viewPath, $data = []) {
        extract($data);
        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/' . $viewPath . '.php';
        require_once '../views/layouts/footer.php';
    }


    // --- 7. FITUR: RIWAYAT ABSENSI ---
    public function riwayat_absensi() {
        $student_id = $_SESSION['user']['id'];
        $query = "SELECT a.*, c.name as class_name, cm.start_time, cm.end_time
                  FROM attendances a 
                  JOIN class_members cm ON a.schedule_id = cm.id
                  JOIN classes c ON cm.class_id = c.id
                  WHERE a.student_id = :student_id ORDER BY a.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':student_id' => $student_id]);
        $riwayat = $stmt->fetchAll();
        $this->renderView('siswa/riwayat_absensi', ['riwayat' => $riwayat]);
    }

    // --- 8. FITUR: INFO PEMBAYARAN ---
    public function pembayaran() {
        $student_id = $_SESSION['user']['id'];
        $query = "SELECT p.*, a.name as admin_name FROM payments p LEFT JOIN users a ON p.admin_id = a.id
                  WHERE p.student_id = :sid ORDER BY p.year DESC, p.month DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':sid' => $student_id]);
        $pembayaran = $stmt->fetchAll();
        $this->renderView('siswa/pembayaran', ['pembayaran' => $pembayaran]);
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
