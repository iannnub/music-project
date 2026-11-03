<?php
require_once '../models/AbsensiModel.php';
require_once '../models/GuruModel.php';
require_once '../models/SettingsModel.php';
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

    public function index() {
        if ($_SESSION['user']['role'] === 'admin') {
            $stmtSiswa = $this->db->prepare("SELECT * FROM users WHERE role = 'siswa' ORDER BY created_at DESC, name ASC");
            $stmtSiswa->execute();
            $siswa = $stmtSiswa->fetchAll();

            $stmtKelas = $this->db->prepare("SELECT c.*, u.name as teacher_name FROM classes c LEFT JOIN users u ON c.teacher_id = u.id ORDER BY c.name ASC");
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
            $guruModel       = new GuruModel($this->db);
            $settingsModel   = new SettingsModel($this->db);

            // A. Jadwal & Status Presensi Hari Ini (mendukung Private & Band Group)
            $queryJadwal = "SELECT cm.id as schedule_id, cm.day, cm.start_time, cm.end_time, 
                                   c.name as class_name, c.type, cb.name as band_name,
                                   COALESCE(ut_band.name, u.name) as teacher_name,
                                   a.status as status_kehadiran, a.created_at as waktu_absen_masuk
                            FROM class_members cm
                            JOIN classes c ON cm.class_id = c.id
                            LEFT JOIN class_bands cb ON cm.band_id = cb.id
                            JOIN users u ON c.teacher_id = u.id
                            LEFT JOIN users ut_band ON cb.teacher_id = ut_band.id
                            LEFT JOIN attendances a ON cm.id = a.schedule_id 
                                                   AND a.student_id = :sid_join
                                                   AND a.date = CURRENT_DATE
                            WHERE cm.student_id = :sid_where
                            ORDER BY FIELD(cm.day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), cm.start_time ASC";

            $stmt = $this->db->prepare($queryJadwal);
            $stmt->execute([':sid_join' => $student_id, ':sid_where' => $student_id]);
            $jadwal_saya = $stmt->fetchAll();

            $data['next_class'] = null;
            $map_hari = ['Monday'=>'Senin', 'Tuesday'=>'Selasa', 'Wednesday'=>'Rabu', 'Thursday'=>'Kamis', 'Friday'=>'Jumat', 'Saturday'=>'Sabtu', 'Sunday'=>'Minggu'];
            $hari_indo = $map_hari[date('l')];
            $jam_sekarang = date('H:i:s');

            foreach ($jadwal_saya as $j) {
                if ($j['day'] === $hari_indo && $j['end_time'] >= $jam_sekarang) {
                    $data['next_class'] = $j;
                    break;
                }
            }

            // B. Kurikulum Bab & Subbab + Catatan Progress Terakhir
            $curriculum = $guruModel->getChaptersWithSubchapters($student_id);
            $stmt2 = $this->db->prepare("SELECT * FROM progress_logs WHERE student_id = :sid ORDER BY date DESC LIMIT 1");
            $stmt2->execute([':sid' => $student_id]);
            $last_progress = $stmt2->fetch();

            // C. Link TikTok Latihan Privat dari Admin (Hanya yang les yang tahu)
            $tiktok_url   = $settingsModel->get('tiktok_practice_url', '');
            $tiktok_title = $settingsModel->get('tiktok_practice_title', 'Video Latihan Privat Siswa KakYo Lesson');
            $tiktok_desc  = $settingsModel->get('tiktok_practice_desc', 'Pantau video latihan dan evaluasi praktik privat siswa les KakYo Lesson di TikTok.');

            // D. Status SPP & Tagihan Aktif
            $bulan_ini = (int)date('n');
            $tahun_ini = (int)date('Y');
            $data['is_lunas']      = $pembayaranModel->checkStatusSiswa($student_id, $bulan_ini, $tahun_ini);
            $data['all_payments']  = $pembayaranModel->getAllByStudent($student_id);
            $data['jadwal_saya']   = $jadwal_saya;
            $data['curriculum']    = $curriculum;
            $data['last_progress'] = $last_progress;
            $data['tiktok_url']    = $tiktok_url;
            $data['tiktok_title']  = $tiktok_title;
            $data['tiktok_desc']   = $tiktok_desc;

            $this->renderView('siswa/dashboard', $data);
        }
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Metode request tidak diizinkan. Gunakan POST.");
        }
        require_once '../helpers/CsrfHelper.php';
        if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token tidak valid");
        }

        $class_ids   = array_filter($_POST['class_id'] ?? []);
        $days        = $_POST['day'] ?? [];
        $start_times = $_POST['start_time'] ?? [];
        $end_times   = $_POST['end_time'] ?? [];
        $branch      = $_POST['branch'] ?? null;
        $spp_rate    = ($branch === 'Jember') ? 350000 : (($branch === 'Balung') ? 250000 : null);

        try {
            $this->db->beginTransaction();

            $stmtTeacher = $this->db->prepare("SELECT teacher_id FROM classes WHERE id = ?");
            foreach ($class_ids as $key => $class_id) {
                $stmtTeacher->execute([$class_id]);
                $classData = $stmtTeacher->fetch();
                $teacher_id = $classData['teacher_id'] ?? null;

                if ($start_times[$key] >= $end_times[$key]) {
                    throw new Exception("Jam selesai harus lebih besar dari jam mulai pada baris ke-" . ($key + 1));
                }

                for ($k = 0; $k < $key; $k++) {
                    if ($days[$k] === $days[$key] && !($end_times[$k] <= $start_times[$key] || $start_times[$key] >= $end_times[$key])) {
                        throw new Exception("Jadwal input ke-" . ($key + 1) . " bentrok dengan jadwal input ke-" . ($k + 1) . ".");
                    }
                }

                if ($teacher_id && $this->classModel->isConflict($teacher_id, $days[$key], $start_times[$key], $end_times[$key])) {
                    throw new Exception("Jadwal di hari " . $days[$key] . " jam " . $start_times[$key] . " bentrok dengan jadwal Guru.");
                }
            }

            $userData = [
                'username'    => trim($_POST['username'] ?? ''),
                'name'        => trim($_POST['name'] ?? ''),
                'email'       => trim($_POST['email'] ?? ''),
                'password'    => password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT),
                'role'        => 'siswa',
                'phone'       => trim($_POST['phone'] ?? ''),
                'parent_name' => trim($_POST['parent_name'] ?? ''),
                'branch'      => $branch,
                'spp_rate'    => $spp_rate
            ];

            $newStudentId = $this->userModel->create($userData);
            if (!$newStudentId) {
                throw new Exception("Gagal membuat akun siswa. Username mungkin sudah digunakan.");
            }

            if (!empty($class_ids)) {
                $stmtInsertMember = $this->db->prepare("
                    INSERT INTO class_members (student_id, class_id, day, start_time, end_time) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                foreach ($class_ids as $key => $class_id) {
                    if (!$stmtInsertMember->execute([$newStudentId, $class_id, $days[$key], $start_times[$key], $end_times[$key]])) {
                        throw new Exception("Gagal menyimpan plotting jadwal ke-" . ($key + 1));
                    }
                }
            }

            // Jika Admin memilih cabang saat input manual, terbitkan juga tagihan perdana otomatis
            if (in_array($branch, ['Balung', 'Jember'], true)) {
                require_once '../models/PembayaranModel.php';
                $pembayaranModel = new PembayaranModel($this->db);
                $totalInitial = ($branch === 'Jember') ? 400000 : 300000;
                $labelBranch  = ($branch === 'Jember') ? 'Studio 2 Jember Kota' : 'Studio 1 Balung';
                $pembayaranModel->create([
                    'student_id' => $newStudentId,
                    'admin_id'   => $_SESSION['user']['id'],
                    'month'      => (int)date('n'),
                    'year'       => (int)date('Y'),
                    'start_date' => date('Y-m-d'),
                    'end_date'   => date('Y-m-d', strtotime('+7 days')),
                    'amount'     => $totalInitial,
                    'status'     => 'Belum Lunas',
                    'notes'      => "Tagihan Perdana {$labelBranch}: SPP Rp " . number_format($spp_rate, 0, ',', '.') . " + Pendaftaran Rp 50.000"
                ]);
            }

            $this->db->commit();

            $_SESSION['flash'] = [
                'status' => 'success',
                'title'  => 'Siswa Ditambahkan',
                'msg'    => 'Akun siswa berhasil dibuat.'
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $_SESSION['flash'] = [
                'status' => 'error',
                'title'  => 'Gagal Menyimpan',
                'msg'    => $e->getMessage()
            ];
        }

        header("Location: index.php?page=siswa");
        exit();
    }

    public function add_schedule_item() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Metode request tidak diizinkan. Gunakan POST.");
        }
        require_once '../helpers/CsrfHelper.php';
        if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token tidak valid");
        }

        $student_id = (int)($_POST['student_id'] ?? 0);
        $class_id   = (int)($_POST['class_id'] ?? 0);
        $day        = $_POST['day'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time   = $_POST['end_time'] ?? '';

        if ($start_time >= $end_time) {
            $_SESSION['flash'] = ['status' => 'warning', 'title' => 'Jam Tidak Valid', 'msg' => 'Jam selesai harus lebih besar dari jam mulai.'];
            header("Location: index.php?page=siswa_manage_jadwal&id=" . $student_id);
            exit();
        }

        $stmt = $this->db->prepare("SELECT teacher_id FROM classes WHERE id = ?");
        $stmt->execute([$class_id]);
        $teacher_id = $stmt->fetch()['teacher_id'] ?? null;

        if ($teacher_id && $this->classModel->isConflict($teacher_id, $day, $start_time, $end_time)) {
            $_SESSION['flash'] = ['status' => 'warning', 'title' => 'Jadwal Guru Bentrok', 'msg' => 'Guru pengampu sudah memiliki jadwal lain di jam tersebut.'];
            header("Location: index.php?page=siswa_manage_jadwal&id=" . $student_id);
            exit();
        }

        if ($this->classModel->isStudentConflict($student_id, $day, $start_time, $end_time)) {
            $_SESSION['flash'] = ['status' => 'warning', 'title' => 'Jadwal Siswa Bentrok', 'msg' => 'Jadwal siswa bentrok dengan sesi lain di hari dan jam yang sama.'];
            header("Location: index.php?page=siswa_manage_jadwal&id=" . $student_id);
            exit();
        }

        if ($this->classModel->addMember([
            'student_id' => $student_id,
            'class_id'   => $class_id,
            'day'        => $day,
            'start_time' => $start_time,
            'end_time'   => $end_time
        ])) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Jadwal Ditambahkan', 'msg' => 'Sesi latihan berhasil didaftarkan.'];
        } else {
            $_SESSION['flash'] = ['status' => 'warning', 'title' => 'Duplikat Slot', 'msg' => 'Slot jadwal tersebut sudah terdaftar.'];
        }

        header("Location: index.php?page=siswa_manage_jadwal&id=" . $student_id);
        exit();
    }

    public function delete_schedule_item() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Metode request tidak diizinkan. Gunakan POST.");
        }
        require_once '../helpers/CsrfHelper.php';
        if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token tidak valid");
        }
        $id = (int)($_POST['id'] ?? 0);
        $student_id = (int)($_POST['student_id'] ?? 0);

        $stmt = $this->db->prepare("DELETE FROM class_members WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Terhapus', 'msg' => 'Sesi latihan berhasil dihapus dari daftar.'];
        }
        header("Location: index.php?page=siswa_manage_jadwal&id=" . $student_id);
        exit();
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Metode request tidak diizinkan. Gunakan POST.");
        }
        require_once '../helpers/CsrfHelper.php';
        if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token tidak valid");
        }
        $id     = (int)($_POST['id'] ?? 0);
        $branch = $_POST['branch'] ?? '';
        $spp    = ($branch === 'Jember') ? 350000 : (($branch === 'Balung') ? 250000 : null);

        $data = [
            'username'    => trim($_POST['username'] ?? ''),
            'name'        => trim($_POST['name'] ?? ''),
            'email'       => trim($_POST['email'] ?? ''),
            'phone'       => trim($_POST['phone'] ?? ''),
            'parent_name' => trim($_POST['parent_name'] ?? ''),
            'branch'      => $branch,
            'spp_rate'    => $spp
        ];
        if (isset($_POST['address'])) {
            $data['address'] = trim($_POST['address']);
        }
        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        if ($this->userModel->update($id, $data)) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Data Siswa Diperbarui', 'msg' => 'Profil dan cabang siswa berhasil disimpan.'];
        } else {
            $_SESSION['flash'] = ['status' => 'error', 'title' => 'Gagal Update', 'msg' => 'Periksa kembali username (mungkin sudah dipakai akun lain).'];
        }
        header("Location: index.php?page=siswa");
        exit();
    }

    public function manage_jadwal() {
        $id_siswa = (int)($_GET['id'] ?? 0);
        $student = $this->userModel->getById($id_siswa);
        $jadwal_aktif = $this->userModel->getStudentSchedule($id_siswa);
        $all_classes = $this->classModel->getAll();

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/admin/siswa/manage_jadwal.php';
        require_once '../views/layouts/footer.php';
    }

    // Perbaikan P0 Fatal Error: Ambil teacher_id dari classes sebelum memanggil isConflict
    public function update_jadwal() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once '../helpers/CsrfHelper.php';
            if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token tidak valid");
            }
            $id_member  = (int)($_POST['id_member'] ?? 0);
            $student_id = (int)($_POST['student_id'] ?? 0);

            $data = [
                'class_id'   => (int)($_POST['class_id'] ?? 0),
                'day'        => $_POST['day'] ?? '',
                'start_time' => $_POST['start_time'] ?? '',
                'end_time'   => $_POST['end_time'] ?? ''
            ];

            if ($data['start_time'] >= $data['end_time']) {
                $_SESSION['flash'] = ['status' => 'warning', 'title' => 'Jam Tidak Valid', 'msg' => 'Jam selesai harus lebih besar dari jam mulai.'];
                header("Location: index.php?page=siswa_manage_jadwal&id=" . $student_id);
                exit();
            }

            $stmtCls = $this->db->prepare("SELECT teacher_id FROM classes WHERE id = ?");
            $stmtCls->execute([$data['class_id']]);
            $teacher_id = (int)($stmtCls->fetchColumn() ?: 0);

            if ($teacher_id > 0 && $this->classModel->isConflict($teacher_id, $data['day'], $data['start_time'], $data['end_time'], $id_member)) {
                $_SESSION['flash'] = ['status' => 'warning', 'title' => 'Jadwal Guru Bentrok', 'msg' => 'Guru pengajar sudah memiliki kelas lain di hari dan rentang jam tersebut.'];
                header("Location: index.php?page=siswa_manage_jadwal&id=" . $student_id);
                exit();
            }

            if ($this->classModel->isStudentConflict($student_id, $data['day'], $data['start_time'], $data['end_time'], $id_member)) {
                $_SESSION['flash'] = ['status' => 'warning', 'title' => 'Jadwal Siswa Bentrok', 'msg' => 'Jadwal siswa bentrok dengan sesi lain di hari dan jam yang sama.'];
                header("Location: index.php?page=siswa_manage_jadwal&id=" . $student_id);
                exit();
            }

            if ($this->userModel->updateJadwalSiswa($id_member, $data)) {
                $_SESSION['flash'] = ['status' => 'success', 'title' => 'Jadwal Diperbarui', 'msg' => 'Plotting jadwal latihan siswa berhasil diperbarui.'];
            } else {
                $_SESSION['flash'] = ['status' => 'error', 'title' => 'Gagal Update', 'msg' => 'Terjadi kesalahan saat menyimpan jadwal.'];
            }
            header("Location: index.php?page=siswa_manage_jadwal&id=" . $student_id);
            exit();
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Metode request tidak diizinkan. Gunakan POST.");
        }
        require_once '../helpers/CsrfHelper.php';
        if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token tidak valid");
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0 && $this->userModel->delete($id)) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Terhapus', 'msg' => 'Siswa dan seluruh datanya telah dihapus.'];
        }
        header("Location: index.php?page=siswa");
        exit();
    }

    // Presensi Siswa Multi-Lokasi (Merekam lokasi dari perangkat pengguna + validasi kepemilikan jadwal)
    public function proses_absen() {
        $absensiModel = new AbsensiModel($this->db);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once '../helpers/CsrfHelper.php';
            if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token tidak valid");
            }
            $student_id  = (int)$_SESSION['user']['id'];
            $schedule_id = (int)($_POST['schedule_id'] ?? 0);
            $lat_siswa   = $_POST['lat'] ?? '0';
            $long_siswa  = $_POST['long'] ?? '0';
            $tanggal_hari_ini = date('Y-m-d');

            // Validasi anti-IDOR: Pastikan schedule_id benar-benar milik siswa yang login
            $stmtOwn = $this->db->prepare("SELECT id FROM class_members WHERE id = ? AND student_id = ? LIMIT 1");
            $stmtOwn->execute([$schedule_id, $student_id]);
            if (!$stmtOwn->fetch()) {
                http_response_code(403);
                die("Akses ditolak: Jadwal tidak valid.");
            }

            if ($absensiModel->cekSudahAbsen($student_id, $schedule_id, $tanggal_hari_ini)) {
                $_SESSION['flash'] = ['status' => 'warning', 'title' => 'Sudah Presensi', 'msg' => 'Kamu sudah melakukan presensi untuk kelas ini hari ini.'];
                header("Location: index.php?page=dashboard_siswa");
                exit;
            }

            try {
                if ($absensiModel->create([
                    'schedule_id' => $schedule_id,
                    'student_id'  => $student_id,
                    'date'        => $tanggal_hari_ini,
                    'photo'       => null,
                    'lat'         => $lat_siswa,
                    'long'        => $long_siswa
                ])) {
                    $_SESSION['flash'] = ['status' => 'success', 'title' => 'Presensi Tercatat', 'msg' => 'Kehadiran dan titik lokasi belajar kamu berhasil dicatat.'];
                } else {
                    throw new Exception("Gagal menyimpan data.");
                }
            } catch (Exception $e) {
                $_SESSION['flash'] = ['status' => 'error', 'title' => 'Gagal Presensi', 'msg' => 'Terjadi kendala saat menyimpan presensi.'];
            }

            header("Location: index.php?page=dashboard_siswa");
            exit;
        }
    }

    public function materi() {
        $student_id = $_SESSION['user']['id'];
        $query = "SELECT materials.*, classes.name as class_name, users.name as teacher_name
                  FROM materials 
                  JOIN classes ON materials.class_id = classes.id
                  JOIN users ON classes.teacher_id = users.id
                  JOIN class_members ON classes.id = class_members.class_id
                  WHERE class_members.student_id = :student_id 
                  AND (materials.student_id IS NULL OR materials.student_id = :student_id_privat)
                  GROUP BY materials.id
                  ORDER BY materials.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':student_id' => $student_id, ':student_id_privat' => $student_id]);
        $materi = $stmt->fetchAll();

        $this->renderView('siswa/materi', ['materi' => $materi]);
    }

    // Halaman Video Latihan TikTok (Menggantikan menu Tugas lama)
    public function tiktok() {
        $settingsModel = new SettingsModel($this->db);
        $this->renderView('siswa/tiktok', [
            'tiktok_url'   => $settingsModel->get('tiktok_practice_url', ''),
            'tiktok_title' => $settingsModel->get('tiktok_practice_title', 'Video Latihan Privat Siswa KakYo Lesson'),
            'tiktok_desc'  => $settingsModel->get('tiktok_practice_desc', 'Pantau video latihan dan evaluasi praktik privat siswa les KakYo Lesson di TikTok.')
        ]);
    }

    // Halaman Tata Tertib Siswa & Wali Murid + Unduh PDF Resmi
    public function tata_tertib() {
        $student_profile = $this->userModel->getById($_SESSION['user']['id']);
        $this->renderView('siswa/tata_tertib', ['student_profile' => $student_profile]);
    }

    public function progress() {
        $student_id = $_SESSION['user']['id'];
        $guruModel = new GuruModel($this->db);
        $curriculum = $guruModel->getChaptersWithSubchapters($student_id);

        $query = "SELECT progress_logs.*, classes.name as class_name, users.name as teacher_name 
                  FROM progress_logs
                  JOIN classes ON progress_logs.class_id = classes.id
                  JOIN users ON progress_logs.teacher_id = users.id
                  WHERE progress_logs.student_id = :student_id
                  ORDER BY progress_logs.date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':student_id' => $student_id]);
        $progress = $stmt->fetchAll();

        $this->renderView('siswa/progress', [
            'curriculum' => $curriculum,
            'progress'   => $progress
        ]);
    }

    private function renderView($viewPath, $data = []) {
        extract($data);
        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/' . $viewPath . '.php';
        require_once '../views/layouts/footer.php';
    }

    public function riwayat_absensi() {
        $student_id = $_SESSION['user']['id'];
        $query = "SELECT a.*, c.name as class_name, cb.name as band_name, cm.start_time, cm.end_time
                  FROM attendances a 
                  JOIN class_members cm ON a.schedule_id = cm.id
                  JOIN classes c ON cm.class_id = c.id
                  LEFT JOIN class_bands cb ON cm.band_id = cb.id
                  WHERE a.student_id = :student_id
                  ORDER BY a.date DESC, a.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':student_id' => $student_id]);
        $riwayat = $stmt->fetchAll();
        $this->renderView('siswa/riwayat_absensi', ['riwayat' => $riwayat]);
    }

    public function pembayaran() {
        $student_id = $_SESSION['user']['id'];
        $student_profile = $this->userModel->getById($student_id);
        $query = "SELECT p.*, a.name as admin_name
                  FROM payments p
                  LEFT JOIN users a ON p.admin_id = a.id
                  WHERE p.student_id = :sid
                  ORDER BY p.year DESC, p.month DESC, p.id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':sid' => $student_id]);
        $pembayaran = $stmt->fetchAll();
        $this->renderView('siswa/pembayaran', [
            'pembayaran'      => $pembayaran,
            'student_profile' => $student_profile
        ]);
    }

    public function cetak_raport() {
        $student_id = $_SESSION['user']['id'];
        $guruModel  = new GuruModel($this->db);
        $curriculum = $guruModel->getChaptersWithSubchapters($student_id);
        $student_profile = $this->userModel->getById($student_id);

        $query = "SELECT progress_logs.*, classes.name as class_name, users.name as teacher_name 
                  FROM progress_logs
                  JOIN classes ON progress_logs.class_id = classes.id
                  JOIN users ON progress_logs.teacher_id = users.id
                  WHERE progress_logs.student_id = :student_id
                  ORDER BY progress_logs.date ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':student_id' => $student_id]);
        $progress = $stmt->fetchAll();

        require_once '../views/siswa/cetak_raport.php';
    }
}
?>
