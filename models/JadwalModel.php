<?php
class JadwalModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Ambil semua jadwal (diurutkan Senin-Minggu)
    // models/JadwalModel.php

    public function getAll() {
        // Query Agregasi Kompleks
        // Logikanya: Ambil Data Kelas, lalu gabungkan semua jadwalnya jadi satu baris string
        $query = "SELECT 
                    classes.id as class_id,
                    classes.name as class_name, 
                    classes.type, 
                    teacher.name as teacher_name,
                    
                    -- GABUNGKAN NAMA ANGGOTA (Dipisah Koma)
                    (SELECT GROUP_CONCAT(users.name SEPARATOR ', ') 
                     FROM class_members 
                     JOIN users ON class_members.student_id = users.id 
                     WHERE class_members.class_id = classes.id) as member_names,

                    -- GABUNGKAN JADWAL (Format: ID|HARI|JAM_MULAI|JAM_SELESAI)
                    -- Dipisah pakai '__' antar jadwal
                    GROUP_CONCAT(
                        CONCAT(schedules.id, '|', schedules.day, '|', schedules.start_time, '|', schedules.end_time) 
                        ORDER BY FIELD(schedules.day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), schedules.start_time ASC
                        SEPARATOR '__'
                    ) as schedule_data

                  FROM classes
                  -- Pakai JOIN (bukan LEFT JOIN) biar kelas yang GAK PUNYA jadwal gak muncul di menu Jadwal
                  JOIN schedules ON classes.id = schedules.class_id
                  JOIN users as teacher ON classes.teacher_id = teacher.id
                  
                  GROUP BY classes.id
                  ORDER BY classes.name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Simpan Jadwal Baru
    public function create($data) {
        try {
            $query = "INSERT INTO schedules (class_id, day, start_time, end_time) 
                      VALUES (:class_id, :day, :start_time, :end_time)";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':class_id'   => $data['class_id'],
                ':day'        => $data['day'],
                ':start_time' => $data['start_time'],
                ':end_time'   => $data['end_time']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Hapus Jadwal
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM schedules WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // Update Jadwal (Hari & Jam)
    public function update($id, $data) {
        try {
            $query = "UPDATE schedules 
                      SET day = :day, 
                          start_time = :start_time, 
                          end_time = :end_time 
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':day'        => $data['day'],
                ':start_time' => $data['start_time'],
                ':end_time'   => $data['end_time'],
                ':id'         => $id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>