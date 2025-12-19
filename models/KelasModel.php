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
        $query = "SELECT class_members.id as member_id, users.name, users.photo_profile, class_members.joined_at 
                  FROM class_members 
                  JOIN users ON class_members.student_id = users.id 
                  WHERE class_members.class_id = :class_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':class_id' => $class_id]);
        return $stmt->fetchAll();
    }

    // 3. Masukkan Siswa ke Kelas (Enroll)
    public function addMember($class_id, $student_id) {
        try {
            // Cek dulu biar gak duplikat (Satu siswa masuk kelas yang sama 2x)
            $cek = $this->db->prepare("SELECT id FROM class_members WHERE class_id = ? AND student_id = ?");
            $cek->execute([$class_id, $student_id]);
            if ($cek->rowCount() > 0) return false; // Sudah ada

            $stmt = $this->db->prepare("INSERT INTO class_members (class_id, student_id) VALUES (?, ?)");
            return $stmt->execute([$class_id, $student_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // 4. Keluarkan Siswa dari Kelas (Kick)
    public function removeMember($member_id) {
        $stmt = $this->db->prepare("DELETE FROM class_members WHERE id = :id");
        return $stmt->execute([':id' => $member_id]);
    }
}
?>