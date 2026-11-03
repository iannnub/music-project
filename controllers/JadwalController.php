<?php
require_once '../models/JadwalModel.php';
require_once '../models/KelasModel.php';

class JadwalController {
    private $db;
    private $jadwalModel;
    private $kelasModel;

    public function __construct($db) {
        $this->db = $db;
        $this->jadwalModel = new JadwalModel($db);
        $this->kelasModel = new KelasModel($db);
    }

    public function index() {
        $query = "SELECT 
                    cm.day, 
                    cm.start_time, 
                    cm.end_time, 
                    u_student.name as student_name,
                    CASE WHEN cb.name IS NOT NULL THEN CONCAT(c.name, ' - ', cb.name) ELSE c.name END as class_name, 
                    COALESCE(u_band_teacher.name, u_teacher.name) as teacher_name
                  FROM class_members cm
                  JOIN users u_student ON cm.student_id = u_student.id
                  JOIN classes c ON cm.class_id = c.id
                  LEFT JOIN class_bands cb ON cm.band_id = cb.id
                  JOIN users u_teacher ON c.teacher_id = u_teacher.id
                  LEFT JOIN users u_band_teacher ON cb.teacher_id = u_band_teacher.id
                  ORDER BY FIELD(cm.day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), cm.start_time ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $all_schedules = $stmt->fetchAll();

        require_once '../views/layouts/header.php';
        require_once '../views/layouts/sidebar.php';
        require_once '../views/layouts/topbar.php';
        require_once '../views/admin/jadwal/index.php';
        require_once '../views/layouts/footer.php';
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

        $class_id    = $_POST['class_id'] ?? 0;
        $days        = $_POST['day'] ?? [];
        $start_times = $_POST['start_time'] ?? [];
        $end_times   = $_POST['end_time'] ?? [];
        $successCount = 0;

        for ($i = 0; $i < count($days); $i++) {
            if (!empty($days[$i]) && !empty($start_times[$i])) {
                if ($this->jadwalModel->create([
                    'class_id'   => $class_id,
                    'day'        => $days[$i],
                    'start_time' => $start_times[$i],
                    'end_time'   => $end_times[$i]
                ])) {
                    $successCount++;
                }
            }
        }

        $_SESSION['flash'] = ($successCount > 0)
            ? ['status' => 'success', 'title' => 'Jadwal Tersimpan', 'msg' => $successCount . ' sesi jadwal berhasil ditambahkan.']
            : ['status' => 'error', 'title' => 'Gagal Simpan', 'msg' => 'Tidak ada jadwal yang berhasil disimpan.'];

        header("Location: index.php?page=jadwal");
        exit;
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
        if ($id > 0 && $this->jadwalModel->delete($id)) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Terhapus', 'msg' => 'Jadwal telah dihapus.'];
        }
        header("Location: index.php?page=jadwal");
        exit;
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

        $id = (int)($_POST['id'] ?? 0);
        if ($this->jadwalModel->update($id, [
            'day'        => $_POST['day'] ?? '',
            'start_time' => $_POST['start_time'] ?? '',
            'end_time'   => $_POST['end_time'] ?? ''
        ])) {
            $_SESSION['flash'] = ['status' => 'success', 'title' => 'Jadwal Diperbarui', 'msg' => 'Perubahan jadwal telah disimpan.'];
        }
        header("Location: index.php?page=jadwal");
        exit;
    }
}
?>
