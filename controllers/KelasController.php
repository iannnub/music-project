<?php
require_once '../models/KelasModel.php';
require_once '../models/UserModel.php';

class KelasController {
    private $kelasModel;
    private $userModel;

    public function __construct($db) {
        $this->kelasModel = new KelasModel($db);
        $this->userModel = new UserModel($db);
    }

    public function index() {
        $kelas = $this->kelasModel->getAll();
        $dataGuru = $this->userModel->getAllByRole('guru');

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/admin/kelas/index.php';
        require_once '../views/layouts/footer.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once '../helpers/CsrfHelper.php';
            if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token tidak valid");
            }
            $data = [
                'name'        => trim($_POST['name'] ?? ''),
                'teacher_id'  => (int)($_POST['teacher_id'] ?? 0),
                'type'        => ($_POST['type'] ?? 'private') === 'group' ? 'group' : 'private',
                'instrument'  => trim($_POST['instrument'] ?? ''),
                'description' => trim($_POST['description'] ?? '')
            ];

            if ($this->kelasModel->create($data)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Kelas Dibuat',
                    'msg'    => 'Data kelas baru berhasil ditambahkan.'
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

    public function detail($id) {
        $kelas = $this->kelasModel->getById($id);
        if (!$kelas) {
            header("Location: index.php?page=kelas");
            exit;
        }
        $members  = $this->kelasModel->getMembers($id);
        $bands    = ($kelas['type'] === 'group') ? $this->kelasModel->getBandsByClass($id) : [];
        $allSiswa = $this->userModel->getAllByRole('siswa');
        $dataGuru = $this->userModel->getAllByRole('guru');

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/admin/kelas/detail.php';
        require_once '../views/layouts/footer.php';
    }

    // Tambah siswa pada kelas Private (Perbaikan bug undefined $teacher_id)
    public function add_member() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once '../helpers/CsrfHelper.php';
            if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token tidak valid");
            }
            $class_id   = (int)($_POST['class_id'] ?? 0);
            $student_id = (int)($_POST['student_id'] ?? 0);
            $day        = $_POST['day'] ?? '';
            $start_time = $_POST['start_time'] ?? '';
            $end_time   = $_POST['end_time'] ?? '';

            $kelasInfo  = $this->kelasModel->getById($class_id);
            $teacher_id = $kelasInfo ? (int)$kelasInfo['teacher_id'] : 0;

            if ($start_time >= $end_time) {
                $_SESSION['flash'] = [
                    'status' => 'warning',
                    'title'  => 'Jam Tidak Valid',
                    'msg'    => 'Jam selesai harus lebih besar dari jam mulai.'
                ];
                header("Location: index.php?page=kelas&action=detail&id=$class_id");
                exit;
            }

            if ($teacher_id > 0 && $this->kelasModel->isConflict($teacher_id, $day, $start_time, $end_time)) {
                $_SESSION['flash'] = [
                    'status' => 'warning',
                    'title'  => 'Jadwal Guru Bentrok',
                    'msg'    => 'Guru pengampu sudah memiliki jadwal lain di hari dan jam tersebut.'
                ];
                header("Location: index.php?page=kelas&action=detail&id=$class_id");
                exit;
            }

            if ($this->kelasModel->isStudentConflict($student_id, $day, $start_time, $end_time)) {
                $_SESSION['flash'] = [
                    'status' => 'warning',
                    'title'  => 'Jadwal Siswa Bentrok',
                    'msg'    => 'Jadwal siswa bentrok dengan kelas lain di hari dan jam yang sama.'
                ];
                header("Location: index.php?page=kelas&action=detail&id=$class_id");
                exit;
            }

            $data = [
                'student_id' => $student_id,
                'class_id'   => $class_id,
                'day'        => $day,
                'start_time' => $start_time,
                'end_time'   => $end_time
            ];

            if ($this->kelasModel->addMember($data)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Siswa Ditambahkan',
                    'msg'    => 'Anggota kelas dan jadwal berhasil disimpan.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'warning',
                    'title'  => 'Slot Sudah Terdaftar',
                    'msg'    => 'Siswa tersebut sudah terdaftar pada slot jadwal ini.'
                ];
            }
            header("Location: index.php?page=kelas&action=detail&id=$class_id");
            exit;
        }
    }

    // --- MANAJEMEN BAND PADA KELAS GROUP (Band 1, Band 2, dst -> Anggota Siswa) ---

    public function store_band() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=kelas");
            exit;
        }
        require_once '../helpers/CsrfHelper.php';
        if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token tidak valid");
        }

        $class_id   = (int)($_POST['class_id'] ?? 0);
        $name       = trim($_POST['name'] ?? '');
        $teacher_id = (int)($_POST['teacher_id'] ?? 0);
        $day        = $_POST['day'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time   = $_POST['end_time'] ?? '';

        if ($name === '' || $start_time >= $end_time) {
            $_SESSION['flash'] = [
                'status' => 'warning',
                'title'  => 'Input Belum Valid',
                'msg'    => 'Nama Band wajib diisi dan jam selesai harus lebih besar dari jam mulai.'
            ];
            header("Location: index.php?page=kelas&action=detail&id=$class_id");
            exit;
        }

        if ($this->kelasModel->isConflict($teacher_id, $day, $start_time, $end_time)) {
            $_SESSION['flash'] = [
                'status' => 'warning',
                'title'  => 'Jadwal Guru Bentrok',
                'msg'    => 'Guru yang dipilih sudah memiliki jadwal mengajar lain pada hari dan jam tersebut.'
            ];
            header("Location: index.php?page=kelas&action=detail&id=$class_id");
            exit;
        }

        if ($this->kelasModel->createBand([
            'class_id'   => $class_id,
            'name'       => $name,
            'teacher_id' => $teacher_id,
            'day'        => $day,
            'start_time' => $start_time,
            'end_time'   => $end_time
        ])) {
            $_SESSION['flash'] = [
                'status' => 'success',
                'title'  => 'Band Dibuat',
                'msg'    => htmlspecialchars($name) . ' berhasil ditambahkan. Silakan masukkan daftar siswa ke dalam band.'
            ];
        } else {
            $_SESSION['flash'] = [
                'status' => 'error',
                'title'  => 'Gagal Membuat Band',
                'msg'    => 'Terjadi kesalahan saat menyimpan data band.'
            ];
        }
        header("Location: index.php?page=kelas&action=detail&id=$class_id");
        exit;
    }

    public function update_band() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=kelas");
            exit;
        }
        require_once '../helpers/CsrfHelper.php';
        if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token tidak valid");
        }

        $band_id    = (int)($_POST['band_id'] ?? 0);
        $class_id   = (int)($_POST['class_id'] ?? 0);
        $name       = trim($_POST['name'] ?? '');
        $teacher_id = (int)($_POST['teacher_id'] ?? 0);
        $day        = $_POST['day'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time   = $_POST['end_time'] ?? '';

        if ($start_time >= $end_time) {
            $_SESSION['flash'] = [
                'status' => 'warning',
                'title'  => 'Jam Tidak Valid',
                'msg'    => 'Jam selesai harus lebih besar dari jam mulai.'
            ];
            header("Location: index.php?page=kelas&action=detail&id=$class_id");
            exit;
        }

        if ($this->kelasModel->isConflict($teacher_id, $day, $start_time, $end_time, null, $band_id)) {
            $_SESSION['flash'] = [
                'status' => 'warning',
                'title'  => 'Jadwal Guru Bentrok',
                'msg'    => 'Guru sudah memiliki jadwal kelas/band lain pada waktu tersebut.'
            ];
            header("Location: index.php?page=kelas&action=detail&id=$class_id");
            exit;
        }

        if ($this->kelasModel->updateBand($band_id, [
            'name'       => $name,
            'teacher_id' => $teacher_id,
            'day'        => $day,
            'start_time' => $start_time,
            'end_time'   => $end_time
        ])) {
            $_SESSION['flash'] = [
                'status' => 'success',
                'title'  => 'Band Diperbarui',
                'msg'    => 'Data band dan jadwal seluruh anggotanya telah diperbarui.'
            ];
        }
        header("Location: index.php?page=kelas&action=detail&id=$class_id");
        exit;
    }

    public function delete_band() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Metode request tidak diizinkan. Gunakan POST.");
        }
        require_once '../helpers/CsrfHelper.php';
        if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token tidak valid");
        }

        $band_id  = (int)($_POST['band_id'] ?? 0);
        $class_id = (int)($_POST['class_id'] ?? 0);
        if ($band_id > 0 && $this->kelasModel->deleteBand($band_id)) {
            $_SESSION['flash'] = [
                'status' => 'success',
                'title'  => 'Band Dihapus',
                'msg'    => 'Band beserta daftar anggota di dalamnya berhasil dihapus.'
            ];
        }
        header("Location: index.php?page=kelas&action=detail&id=$class_id");
        exit;
    }

    public function add_band_member() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=kelas");
            exit;
        }
        require_once '../helpers/CsrfHelper.php';
        if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token tidak valid");
        }

        $band_id     = (int)($_POST['band_id'] ?? 0);
        $class_id    = (int)($_POST['class_id'] ?? 0);
        $student_ids = $_POST['student_ids'] ?? [];
        if (!is_array($student_ids) && !empty($_POST['student_id'])) {
            $student_ids = [(int)$_POST['student_id']];
        }

        $band = $this->kelasModel->getBandById($band_id);
        if (!$band || (int)$band['class_id'] !== $class_id) {
            header("Location: index.php?page=kelas&action=detail&id=$class_id");
            exit;
        }

        $added = 0;
        $skipped = [];
        foreach ($student_ids as $sid) {
            $sid = (int)$sid;
            if ($sid <= 0) continue;

            if ($this->kelasModel->isBandMember($band_id, $sid)) {
                $skipped[] = 'sudah ada di band ini';
                continue;
            }
            if ($this->kelasModel->isStudentConflict($sid, $band['day'], $band['start_time'], $band['end_time'])) {
                $skipped[] = 'jadwal siswa bentrok';
                continue;
            }
            if ($this->kelasModel->addMember([
                'student_id' => $sid,
                'band_id'    => $band_id,
                'class_id'   => $class_id,
                'day'        => $band['day'],
                'start_time' => $band['start_time'],
                'end_time'   => $band['end_time']
            ])) {
                $added++;
            }
        }

        if ($added > 0) {
            $_SESSION['flash'] = [
                'status' => 'success',
                'title'  => 'Anggota Band Ditambahkan',
                'msg'    => $added . ' siswa berhasil dimasukkan ke ' . $band['name'] . '.'
            ];
        } else {
            $_SESSION['flash'] = [
                'status' => 'warning',
                'title'  => 'Tidak Ada Siswa Ditambahkan',
                'msg'    => !empty($skipped) ? 'Siswa dilewati karena ' . implode(', ', array_unique($skipped)) . '.' : 'Pilih minimal satu siswa.'
            ];
        }
        header("Location: index.php?page=kelas&action=detail&id=$class_id");
        exit;
    }

    public function delete_member() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Metode request tidak diizinkan. Gunakan POST.");
        }
        require_once '../helpers/CsrfHelper.php';
        if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token tidak valid");
        }
        $member_id = (int)($_POST['member_id'] ?? 0);
        $class_id  = (int)($_POST['class_id'] ?? 0);

        if ($member_id > 0 && $this->kelasModel->removeMember($member_id)) {
            $_SESSION['flash'] = [
                'status' => 'success',
                'title'  => 'Anggota Dihapus',
                'msg'    => 'Siswa telah dikeluarkan dari daftar.'
            ];
        }
        header("Location: index.php?page=kelas&action=detail&id=$class_id");
        exit;
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once '../helpers/CsrfHelper.php';
            if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token tidak valid");
            }
            $id = (int)($_POST['id'] ?? 0);
            $data = [
                'name'        => trim($_POST['name'] ?? ''),
                'teacher_id'  => (int)($_POST['teacher_id'] ?? 0),
                'type'        => ($_POST['type'] ?? 'private') === 'group' ? 'group' : 'private',
                'instrument'  => trim($_POST['instrument'] ?? ''),
                'description' => trim($_POST['description'] ?? '')
            ];

            if ($this->kelasModel->update($id, $data)) {
                $_SESSION['flash'] = [
                    'status' => 'success',
                    'title'  => 'Kelas Diperbarui',
                    'msg'    => 'Informasi kelas telah diperbarui.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'status' => 'error',
                    'title'  => 'Gagal Update',
                    'msg'    => 'Data kelas gagal diperbarui.'
                ];
            }
            header("Location: index.php?page=kelas");
            exit;
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
        if ($id > 0 && $this->kelasModel->delete($id)) {
            $_SESSION['flash'] = [
                'status' => 'success',
                'title'  => 'Kelas Dihapus',
                'msg'    => 'Kelas dan seluruh jadwal terkait telah dihapus.'
            ];
        }
        header("Location: index.php?page=kelas");
        exit;
    }
}
?>
