<?php
class DashboardModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // 1. STATISTIK RINGKAS (KARTU ATAS)
    public function getCounts() {
        return [
            'siswa' => $this->db->query("SELECT COUNT(*) FROM users WHERE role='siswa'")->fetchColumn(),
            'guru'  => $this->db->query("SELECT COUNT(*) FROM users WHERE role='guru'")->fetchColumn(),
            'kelas' => $this->db->query("SELECT COUNT(*) FROM classes")->fetchColumn(),
            'pending_payment' => $this->db->query("SELECT COUNT(*) FROM payments WHERE status='Belum Lunas'")->fetchColumn()
        ];
    }

    // 2. DATA GRAFIK PEMASUKAN (AREA CHART)
    public function getIncomeChart($year) {
        $query = "SELECT month, SUM(amount) as total 
                  FROM payments 
                  WHERE year = ? AND status = 'Lunas' 
                  GROUP BY month ORDER BY month ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$year]);
        $result = $stmt->fetchAll();

        $data = array_fill(1, 12, 0); 
        foreach ($result as $row) {
            $data[$row['month']] = $row['total'];
        }
        return array_values($data); 
    }

    public function getRecentActivities($limit = 5) {
    // Kita tambahkan kolom p.status agar data asli dari DB terbawa
    $query = "
        (SELECT 
            p.created_at, 
            u.name as user_name, 
            CONCAT('Membayar SPP: Rp ', FORMAT(p.amount, 0, 'id_ID')) as description, 
            p.status as payment_status, -- Tambahan: Mengambil status asli (Lunas/Belum Lunas)
            'payment' as type 
         FROM payments p 
         JOIN users u ON p.student_id = u.id)
        UNION
        (SELECT 
            a.created_at, 
            u.name as user_name, 
            CONCAT('Absensi di kelas: ', c.name) as description, 
            NULL as payment_status, -- Attendance tidak punya status bayar, jadi kita kasih NULL agar kolom sinkron
            'attendance' as type 
         FROM attendances a 
         JOIN users u ON a.student_id = u.id 
         JOIN schedules s ON a.schedule_id = s.id 
         JOIN classes c ON s.class_id = c.id)
        ORDER BY created_at DESC LIMIT :limit";

    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // 3. DATA GRAFIK ABSENSI (PIE CHART)
    public function getAttendancePie() {
        $query = "SELECT status, COUNT(*) as total FROM attendances GROUP BY status";
        $stmt = $this->db->query($query);
        $result = $stmt->fetchAll();
        $stats = ['Hadir' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alpha' => 0];
        foreach ($result as $row) {
            if ($row['status'] == 'Hadir') $stats['Hadir'] = $row['total'];
            elseif ($row['status'] == 'Izin') $stats['Izin'] = $row['total'];
            elseif ($row['status'] == 'Sakit') $stats['Sakit'] = $row['total'];
            elseif ($row['status'] == 'Ditolak' || $row['status'] == 'Alpha') $stats['Alpha'] = $row['total'];
        }
        return array_values($stats);
    }

    public function getJadwalGuru($teacher_id) {
        $query = "SELECT s.*, c.name as class_name, c.type, 
                  (SELECT GROUP_CONCAT(u.name SEPARATOR ', ') FROM class_members cm JOIN users u ON cm.student_id = u.id WHERE cm.class_id = c.id) as student_names
                  FROM schedules s JOIN classes c ON s.class_id = c.id 
                  WHERE c.teacher_id = :tid ORDER BY s.day, s.start_time";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['tid' => $teacher_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingValidation($teacher_id) {
        $query = "SELECT COUNT(*) FROM attendances a JOIN schedules s ON a.schedule_id = s.id JOIN classes c ON s.class_id = c.id 
                  WHERE c.teacher_id = :tid AND a.is_validated = 0";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['tid' => $teacher_id]);
        return $stmt->fetchColumn();
    }

    public function checkPaymentStatus($student_id, $month, $year) {
        $query = "SELECT status FROM payments WHERE student_id = ? AND month = ? AND year = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$student_id, $month, $year]);
        $status = $stmt->fetchColumn();
        return ($status == 'Lunas');
    }

    public function getNextClass($student_id, $day) {
        $now = date('H:i:s');
        $query = "SELECT s.*, c.name as class_name, u.name as teacher_name 
                  FROM schedules s JOIN classes c ON s.class_id = c.id JOIN class_members cm ON c.id = cm.class_id JOIN users u ON c.teacher_id = u.id
                  WHERE cm.student_id = :sid AND s.day = :day AND s.start_time > :now 
                  ORDER BY s.start_time ASC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['sid' => $student_id, 'day' => $day, 'now' => $now]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getJadwalSiswa($student_id) {
        $query = "SELECT s.*, c.name as class_name, u.name as teacher_name 
                  FROM schedules s JOIN classes c ON s.class_id = c.id JOIN class_members cm ON c.id = cm.class_id JOIN users u ON c.teacher_id = u.id
                  WHERE cm.student_id = ? ORDER BY s.day, s.start_time";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLastProgress($student_id) {
        $query = "SELECT * FROM progress_notes WHERE student_id = ? ORDER BY date DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$student_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>