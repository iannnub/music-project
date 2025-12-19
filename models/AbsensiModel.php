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
        try {
            $query = "INSERT INTO attendances (schedule_id, student_id, date, status, photo_proof, location_lat, location_long) 
                      VALUES (:schedule_id, :student_id, :date, :status, :photo_proof, :lat, :long)";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':schedule_id' => $data['schedule_id'],
                ':student_id'  => $data['student_id'],
                ':date'        => $data['date'],     // Tanggal hari ini (Y-m-d)
                ':status'      => 'Hadir',           // Default Hadir
                ':photo_proof' => $data['photo'],    // Nama file foto
                ':lat'         => $data['lat'],
                ':long'        => $data['long']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // --- FITUR GURU: LIST ABSENSI ---
    public function getAbsensiByGuru($teacher_id) {
        $query = "SELECT attendances.*, 
                         users.name as student_name, 
                         classes.name as class_name,
                         schedules.start_time, schedules.end_time
                  FROM attendances
                  JOIN users ON attendances.student_id = users.id
                  JOIN schedules ON attendances.schedule_id = schedules.id
                  JOIN classes ON schedules.class_id = classes.id
                  WHERE classes.teacher_id = :teacher_id
                  ORDER BY attendances.created_at DESC";
        
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