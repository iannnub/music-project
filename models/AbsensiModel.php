<?php
class AbsensiModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Cek apakah siswa sudah absen hari ini di jadwal ini? (Prevent Double Absen)
    public function cekSudahAbsen($student_id, $schedule_id, $date) {
        $stmt = $this->db->prepare("SELECT id FROM attendances 
                                    WHERE student_id = ? AND schedule_id = ? AND date = ?");
        $stmt->execute([$student_id, $schedule_id, $date]);
        return $stmt->rowCount() > 0;
    }

    // Simpan Data Absensi Baru
    public function create($data) {
        $query = "INSERT INTO attendances (schedule_id, student_id, date, photo_proof, location_lat, location_long) 
                  VALUES (:sid, :stud_id, :date, :photo, :lat, :long)";
        
        $stmt = $this->db->prepare($query);
        
        // 1. Cek foto proof (Tetap NULL jika tidak ada)
        $photo = !empty($data['photo']) ? $data['photo'] : null;

        // 2. Cek koordinat (Fase 3: Gunakan 0 jika data tidak dikirim/kosong)
        $lat = isset($data['lat']) ? $data['lat'] : 0;
        $long = isset($data['long']) ? $data['long'] : 0;

        return $stmt->execute([
            ':sid'     => $data['schedule_id'],
            ':stud_id' => $data['student_id'],
            ':date'    => $data['date'],
            ':photo'   => $photo,
            ':lat'     => $lat,
            ':long'    => $long
        ]);
    }

    public function getPendingAbsensiByGuru($teacher_id) {
        $query = "SELECT a.*, u.name as student_name, c.name as class_name, cm.start_time, cm.end_time
                  FROM attendances a
                  JOIN users u ON a.student_id = u.id
                  JOIN class_members cm ON a.schedule_id = cm.id
                  JOIN classes c ON cm.class_id = c.id
                  WHERE c.teacher_id = :teacher_id AND a.status = 'Menunggu'
                  ORDER BY a.created_at ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':teacher_id' => $teacher_id]);
        return $stmt->fetchAll();
    }

    public function getStudentAttendanceSummary($teacher_id) {
    $query = "SELECT 
                u.id as student_id, 
                u.name as student_name, 
                u.photo_profile,
                -- MAGIC LINE: Gabungin nama kelas biar gak duplikat baris
                GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as class_names,
                COUNT(CASE WHEN a.status = 'Hadir' THEN 1 END) as total_hadir,
                COUNT(CASE WHEN a.status IN ('Izin', 'Sakit') THEN 1 END) as total_izin,
                COUNT(CASE WHEN a.status = 'Ditolak' THEN 1 END) as total_alpha
              FROM users u
              JOIN class_members cm ON u.id = cm.student_id
              JOIN classes c ON cm.class_id = c.id
              LEFT JOIN attendances a ON u.id = a.student_id AND a.status != 'Menunggu'
              WHERE c.teacher_id = :tid
              GROUP BY u.id -- KUNCINYA DI SINI: Cukup Group by Student ID
              ORDER BY u.name ASC";

    $stmt = $this->db->prepare($query);
    $stmt->execute([':tid' => $teacher_id]);
    return $stmt->fetchAll();
}

    public function getAttendanceDetailByStudent($student_id, $teacher_id) {
        $query = "SELECT a.*, c.name as class_name
                  FROM attendances a
                  JOIN class_members cm ON a.schedule_id = cm.id
                  JOIN classes c ON cm.class_id = c.id
                  WHERE a.student_id = :sid 
                  AND c.teacher_id = :tid 
                  AND a.status != 'Menunggu'
                  ORDER BY a.date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':sid' => $student_id, ':tid' => $teacher_id]);
        return $stmt->fetchAll();
    }

    // --- FITUR GURU: LIST ABSENSI ---
    public function getAbsensiByGuru($teacher_id) {
        // KUNCINYA: Join ke class_members (cm), bukan schedules
        $query = "SELECT a.*, 
                         u.name as student_name, 
                         c.name as class_name,
                         cm.start_time, cm.end_time
                  FROM attendances a
                  JOIN users u ON a.student_id = u.id
                  JOIN class_members cm ON a.schedule_id = cm.id
                  JOIN classes c ON cm.class_id = c.id
                  WHERE c.teacher_id = :teacher_id
                  ORDER BY a.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':teacher_id' => $teacher_id]);
        return $stmt->fetchAll();
    }

    // --- FITUR GURU: UPDATE STATUS ABSEN ---
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE attendances SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

}
?>