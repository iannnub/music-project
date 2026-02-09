<?php
class KelasModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Ambil semua data kelas + Nama Gurunya (JOIN TABLE)
    public function getAll() {
        // Kita pakai JOIN biar yang muncul bukan 'teacher_id' (angka), tapi 'name' (nama guru)
        $query = "SELECT classes.*, users.name as guru_name 
                  FROM classes 
                  JOIN users ON classes.teacher_id = users.id 
                  ORDER BY classes.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Tambah Kelas Baru
    public function create($data) {
        try {
            $query = "INSERT INTO classes (name, teacher_id, type, instrument, description) 
                      VALUES (:name, :teacher_id, :type, :instrument, :description)";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':name'        => $data['name'],
                ':teacher_id'  => $data['teacher_id'],
                ':type'        => $data['type'],
                ':instrument'  => $data['instrument'],
                ':description' => $data['description']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Update Data Kelas
    public function update($id, $data) {
        try {
            $query = "UPDATE classes 
                      SET name = :name, 
                          teacher_id = :teacher_id, 
                          type = :type, 
                          instrument = :instrument, 
                          description = :description 
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            // Gabungkan ID ke dalam array data untuk eksekusi
            $data['id'] = $id; 
            
            return $stmt->execute([
                ':name'        => $data['name'],
                ':teacher_id'  => $data['teacher_id'],
                ':type'        => $data['type'],
                ':instrument'  => $data['instrument'],
                ':description' => $data['description'],
                ':id'          => $id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    

    // 1. Ambil Detail Kelas berdasarkan ID (Buat Judul Halaman)
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT classes.*, users.name as guru_name 
                                    FROM classes 
                                    JOIN users ON classes.teacher_id = users.id 
                                    WHERE classes.id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    

    // 2. Ambil Daftar Siswa yang SUDAH masuk kelas ini
    public function getMembers($class_id) {
        $query = "SELECT 
                    u.name, 
                    u.photo_profile,
                    cm.id as member_id, 
                    cm.joined_at,
                    cm.day, 
                    cm.start_time, 
                    cm.end_time
                  FROM class_members cm
                  JOIN users u ON cm.student_id = u.id
                  WHERE cm.class_id = ?
                  ORDER BY cm.day ASC, cm.start_time ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$class_id]);
        return $stmt->fetchAll();
    }

    // 3. Masukkan Siswa ke Kelas (Enroll)
    public function addMember($data) {
        try {
            $query = "INSERT INTO class_members (student_id, class_id, day, start_time, end_time) 
                      VALUES (:student_id, :class_id, :day, :start_time, :end_time)";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':student_id' => $data['student_id'],
                ':class_id'   => $data['class_id'],
                ':day'        => $data['day'],
                ':start_time' => $data['start_time'],
                ':end_time'   => $data['end_time']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function isConflict($teacher_id, $day, $start_time, $end_time, $ignore_id = null) {
    // Logika: Cek apakah guru sudah punya jadwal di hari dan jam yang bersinggungan
    $query = "SELECT COUNT(*) as total 
              FROM class_members cm
              JOIN classes c ON cm.class_id = c.id
              WHERE c.teacher_id = :teacher_id 
              AND cm.day = :day
              AND (:start_new < cm.end_time AND :end_new > cm.start_time)";
    
    // Jika sedang edit, jangan bandingkan dengan jadwal dirinya sendiri
    if ($ignore_id) {
        $query .= " AND cm.id != :ignore_id";
    }

    $stmt = $this->db->prepare($query);
    $params = [
        ':teacher_id' => $teacher_id,
        ':day'        => $day,
        ':start_new'  => $start_time,
        ':end_new'    => $end_time
    ];
    if ($ignore_id) $params[':ignore_id'] = $ignore_id;

    $stmt->execute($params);
    $result = $stmt->fetch();
    
    return $result['total'] > 0; // True jika ada bentrok
}

    // 4. Keluarkan Siswa dari Kelas (Kick)
    public function removeMember($member_id) {
        $stmt = $this->db->prepare("DELETE FROM class_members WHERE id = ?");
        return $stmt->execute([$member_id]);
    }
}
?>