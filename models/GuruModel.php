<?php
class GuruModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // --- 1. DASHBOARD ---
    
    // Hitung Absensi Masuk Hari Ini (INI YANG TADI HILANG)
    public function countTodayAttendance($teacher_id) {
        $query = "SELECT COUNT(*) as total 
                  FROM attendances 
                  JOIN schedules ON attendances.schedule_id = schedules.id
                  JOIN classes ON schedules.class_id = classes.id
                  WHERE classes.teacher_id = ? 
                  AND attendances.date = CURRENT_DATE";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$teacher_id]);
        $result = $stmt->fetch();
        return $result['total'];
    }

    // Ambil daftar kelas yang diampu oleh Guru ini
    public function getMyClasses($teacher_id) {
        $stmt = $this->db->prepare("SELECT * FROM classes WHERE teacher_id = ? ORDER BY name ASC");
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    }

    // --- 2. PROGRESS & JURNAL ---

    // Ambil daftar siswa di dalam kelas tertentu
    public function getStudentsInClass($class_id) {
        $query = "SELECT users.id, users.name, users.photo_profile, class_members.joined_at
                  FROM class_members
                  JOIN users ON class_members.student_id = users.id
                  WHERE class_members.class_id = :class_id
                  ORDER BY users.name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':class_id' => $class_id]);
        return $stmt->fetchAll();
    }

    // Simpan Catatan Progress (TANPA STATUS)
    public function saveProgress($data) {
        try {
            $query = "INSERT INTO progress_logs (class_id, student_id, teacher_id, date, topic, notes) 
                      VALUES (:cid, :sid, :tid, :date, :topic, :notes)";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':cid'   => $data['class_id'],
                ':sid'   => $data['student_id'],
                ':tid'   => $data['teacher_id'],
                ':date'  => $data['date'],
                ':topic' => $data['topic'],
                ':notes' => $data['notes']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Ambil History Progress
    public function getProgressHistory($class_id, $teacher_id) {
        $query = "SELECT progress_logs.*, users.name as student_name 
                  FROM progress_logs 
                  JOIN users ON progress_logs.student_id = users.id
                  WHERE progress_logs.class_id = ? AND progress_logs.teacher_id = ?
                  ORDER BY progress_logs.created_at DESC LIMIT 10";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$class_id, $teacher_id]);
        return $stmt->fetchAll();
    }


    // --- 3. MATERI ---

    // Ambil Materi
    public function getMaterials($teacher_id) {
        $query = "SELECT materials.*, classes.name as class_name 
                  FROM materials 
                  JOIN classes ON materials.class_id = classes.id
                  WHERE classes.teacher_id = ? 
                  ORDER BY materials.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    }

    // Simpan Materi Baru
    public function saveMaterial($data) {
        $query = "INSERT INTO materials (class_id, title, description, video_url) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$data['class_id'], $data['title'], $data['description'], $data['video_url']]);
    }

    // Hapus Materi
    public function deleteMaterial($id) {
        $stmt = $this->db->prepare("DELETE FROM materials WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- 4. TUGAS (PR) ---

    // Ambil Daftar Tugas
    public function getAssignments($teacher_id) {
        $query = "SELECT assignments.*, classes.name as class_name,
                  (SELECT COUNT(*) FROM submissions WHERE submissions.assignment_id = assignments.id) as total_collected
                  FROM assignments 
                  JOIN classes ON assignments.class_id = classes.id
                  WHERE classes.teacher_id = ? 
                  ORDER BY assignments.deadline DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    }

    // Buat Tugas Baru
    public function saveAssignment($data) {
        $query = "INSERT INTO assignments (class_id, title, description, deadline) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$data['class_id'], $data['title'], $data['description'], $data['deadline']]);
    }

    // Hapus Tugas
    public function deleteAssignment($id) {
        $stmt = $this->db->prepare("DELETE FROM assignments WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- 5. PENILAIAN TUGAS ---

    // Ambil Detail Satu Tugas
    public function getAssignmentById($id) {
        $stmt = $this->db->prepare("SELECT * FROM assignments WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Ambil Daftar Pengumpulan Siswa
    public function getSubmissions($assignment_id) {
        $query = "SELECT submissions.*, users.name as student_name, users.photo_profile 
                  FROM submissions 
                  JOIN users ON submissions.student_id = users.id 
                  WHERE submissions.assignment_id = ? 
                  ORDER BY submissions.submitted_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$assignment_id]);
        return $stmt->fetchAll();
    }

    // Simpan Nilai & Feedback
    public function saveGrade($submission_id, $grade, $feedback) {
        $stmt = $this->db->prepare("UPDATE submissions SET grade = ?, teacher_feedback = ? WHERE id = ?");
        return $stmt->execute([$grade, $feedback, $submission_id]);
    }

}
?>