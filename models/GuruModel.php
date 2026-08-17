<?php
class GuruModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // --- 1. DASHBOARD ---
    
    // Hitung Absensi Masuk Hari Ini (INI YANG TADI HILANG)
    public function countTodayAttendance($teacher_id) {
        $query = "SELECT COUNT(a.id) as total 
                  FROM attendances a
                  JOIN class_members cm ON a.schedule_id = cm.id
                  JOIN classes c ON cm.class_id = c.id
                  WHERE c.teacher_id = :tid 
                  AND a.status = 'Menunggu'"; 
                  
        $stmt = $this->db->prepare($query);
        $stmt->execute([':tid' => $teacher_id]);
        $res = $stmt->fetch();
        return $res['total'] ?? 0;
    }

    // Ambil daftar kelas yang diampu oleh Guru ini
    public function getMyClasses($teacher_id) {
        $stmt = $this->db->prepare("SELECT * FROM classes WHERE teacher_id = ? ORDER BY name ASC");
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    }

    // --- 2. PROGRESS & JURNAL ---

    // Ambil daftar siswa di dalam kelas tertentu (Versi FIX: No Duplicate)
    public function getStudentsInClass($class_id) {
        $query = "SELECT users.id, users.name, users.photo_profile, MIN(class_members.joined_at) as joined_at
                  FROM class_members
                  JOIN users ON class_members.student_id = users.id
                  WHERE class_members.class_id = :class_id
                  GROUP BY users.id ORDER BY users.name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':class_id' => $class_id]);
        return $stmt->fetchAll();
    }

    public function getMyStudents($teacher_id) {
        $query = "SELECT u.id, u.name, cm.class_id 
                  FROM users u
                  JOIN class_members cm ON u.id = cm.student_id
                  JOIN classes c ON cm.class_id = c.id
                  WHERE c.teacher_id = ?
                  GROUP BY u.id, cm.class_id
                  ORDER BY u.name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    }


    // Simpan Catatan Progress (TANPA STATUS)
    public function saveProgress($data) {
        try {
            $query = "INSERT INTO progress_logs (class_id, student_id, teacher_id, date, topic, notes) 
                      VALUES (:cid, :sid, :tid, :date, :topic, :notes)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':cid' => $data['class_id'], ':sid' => $data['student_id'],
                ':tid' => $data['teacher_id'], ':date' => $data['date'],
                ':topic' => $data['topic'], ':notes' => $data['notes']
            ]);
        } catch (PDOException $e) { return false; }
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

    public function updateProgress($id, $data) {
        try {
            $query = "UPDATE progress_logs SET 
                        date = :date, 
                        topic = :topic, 
                        notes = :notes 
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':date'  => $data['date'],
                ':topic' => $data['topic'],
                ':notes' => $data['notes'],
                ':id'    => $id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // 2. Hapus data progress berdasarkan ID
    public function deleteProgress($id) {
        try {
            $query = "DELETE FROM progress_logs WHERE id = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }


    // --- 3. MATERI ---

    // Ambil Materi
    public function getMaterials($teacher_id) {
        $query = "SELECT m.*, c.name as class_name, u.name as student_name 
                  FROM materials m
                  JOIN classes c ON m.class_id = c.id
                  LEFT JOIN users u ON m.student_id = u.id
                  WHERE c.teacher_id = ? ORDER BY m.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    }

public function updateMaterial($id, $data) {
        $query = "UPDATE materials SET class_id = ?, student_id = ?, title = ?, description = ?, video_url = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$data['class_id'], $data['student_id'], $data['title'], $data['description'], $data['video_url'], $id]);
    }

    // Simpan Materi Baru
    public function saveMaterial($data) {
        $query = "INSERT INTO materials (class_id, student_id, title, description, video_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$data['class_id'], $data['student_id'], $data['title'], $data['description'], $data['video_url']]);
    }

    // Hapus Materi
    public function deleteMaterial($id) {
        $stmt = $this->db->prepare("DELETE FROM materials WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- 4. TUGAS (PR) ---

    // Ambil Daftar Tugas
    // Ambil Daftar Tugas dengan Breakdown Status (Fase 1)
    public function getAssignments($teacher_id) {
        $query = "SELECT a.*, c.name as class_name, u.name as student_name,
                 -- Menghitung yang baru masuk tapi belum di-ACC
                 (SELECT COUNT(*) FROM submissions s 
                  WHERE s.assignment_id = a.id 
                  AND s.status = 'Menunggu Verifikasi') as total_pending,
                 
                 -- Menghitung yang sudah resmi dinilai/selesai
                 (SELECT COUNT(*) FROM submissions s 
                  WHERE s.assignment_id = a.id 
                  AND s.status = 'Selesai') as total_finished,
                 
                 -- Target jumlah murid
                 (CASE 
                     WHEN a.student_id IS NOT NULL THEN 1 
                     ELSE (SELECT COUNT(*) FROM class_members cm WHERE cm.class_id = a.class_id) 
                 END) as total_expected
                 
                 FROM assignments a
                 JOIN classes c ON a.class_id = c.id
                 LEFT JOIN users u ON a.student_id = u.id
                 WHERE c.teacher_id = ? 
                 ORDER BY a.deadline DESC";
                 
        $stmt = $this->db->prepare($query);
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    }

public function updateAssignment($id, $data) {
        // TAMBAHKAN link_referensi di sini
        $query = "UPDATE assignments SET 
                    class_id = ?, 
                    student_id = ?, 
                    title = ?, 
                    description = ?, 
                    deadline = ?,
                    link_referensi = ? 
                  WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['class_id'], 
            $data['student_id'], 
            $data['title'], 
            $data['description'], 
            $data['deadline'], 
            $data['link_referensi'], // <--- Sync data
            $id
        ]);
    }

    // Buat Tugas Baru
    public function saveAssignment($data) {
        $query = "INSERT INTO assignments (class_id, student_id, title, description, deadline, link_referensi) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['class_id'], $data['student_id'], $data['title'], 
            $data['description'], $data['deadline'], $data['link_referensi']
        ]);
    }

    // Hapus Tugas
    public function deleteAssignment($id) {
        $stmt = $this->db->prepare("DELETE FROM assignments WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- 5. PENILAIAN TUGAS ---

    // Ambil Detail Satu Tugas
    public function getAssignmentById($id) {
        $query = "SELECT a.*, c.name as class_name FROM assignments a 
                  JOIN classes c ON a.class_id = c.id WHERE a.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

// 1. Ambil Tugas yang Mepet Deadline atau Telat (Urgent)
    public function getUrgentTasks($teacher_id) {
        $query = "SELECT a.*, c.name as class_name, 
                  (SELECT COUNT(*) FROM submissions s WHERE s.assignment_id = a.id) as total_collected,
                  (CASE 
                      WHEN a.student_id IS NOT NULL THEN 1 
                      ELSE (SELECT COUNT(*) FROM class_members cm WHERE cm.class_id = a.class_id) 
                  END) as total_expected
                  FROM assignments a
                  JOIN classes c ON a.class_id = c.id
                  WHERE c.teacher_id = ? 
                  AND (
                      (a.deadline BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 48 HOUR)) -- Mepet (48 Jam)
                      OR (a.deadline < NOW()) -- Sudah lewat tapi nanti difilter di logic if belum lengkap
                  )
                  ORDER BY a.deadline ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    }

    // 2. Ambil Statistik Global Pengumpulan Tugas
    public function getGlobalTaskStats($teacher_id) {
        $query = "SELECT 
                    COUNT(a.id) as total_tugas,
                    SUM((SELECT COUNT(*) FROM submissions s WHERE s.assignment_id = a.id)) as total_setoran,
                    SUM(CASE 
                        WHEN a.student_id IS NOT NULL THEN 1 
                        ELSE (SELECT COUNT(*) FROM class_members cm WHERE cm.class_id = a.class_id) 
                    END) as total_target
                  FROM assignments a
                  JOIN classes c ON a.class_id = c.id
                  WHERE c.teacher_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$teacher_id]);
        return $stmt->fetch();
    }

    public function getTotalKelas($teacher_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM classes WHERE teacher_id = ?");
        $stmt->execute([$teacher_id]);
        $res = $stmt->fetch();
        return $res['total'] ?? 0;
    }

    public function getTotalSiswa($teacher_id) {
        $query = "SELECT COUNT(DISTINCT cm.student_id) as total 
                  FROM class_members cm 
                  JOIN classes c ON cm.class_id = c.id 
                  WHERE c.teacher_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$teacher_id]);
        $res = $stmt->fetch();
        return $res['total'] ?? 0;
    }

    // 3. Tadi di Controller lo manggil getTotalPendingAbsen
    // Method ini mirip dengan countTodayAttendance lo, tapi namanya harus sama dengan di Controller
    public function getTotalPendingAbsen($teacher_id) {
        return $this->countTodayAttendance($teacher_id);
    }
    // 4. Tadi di Controller lo manggil getJadwalByGuru
    public function getJadwalByGuru($teacher_id) {
        $query = "SELECT 
                    cm.id as schedule_id,
                    cm.day, cm.start_time, cm.end_time, c.name as class_name, 
                    c.type, c.id as class_id, u.name as student_name 
                  FROM class_members cm
                  JOIN classes c ON cm.class_id = c.id
                  JOIN users u ON cm.student_id = u.id
                  WHERE c.teacher_id = ?
                  ORDER BY FIELD(cm.day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), 
                           cm.start_time ASC, cm.id ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    }

    // Ambil Daftar Pengumpulan Siswa
    public function getSubmissions($assignment_id) {
        $query = "SELECT s.*, u.name as student_name, u.photo_profile 
                  FROM submissions s
                  JOIN users u ON s.student_id = u.id 
                  WHERE s.assignment_id = ? 
                  ORDER BY FIELD(s.status, 'Menunggu Verifikasi', 'Selesai', 'Belum Mengerjakan'), s.submitted_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$assignment_id]);
        return $stmt->fetchAll();
    }

    public function accSubmission($submission_id) {
        $query = "UPDATE submissions SET status = 'Selesai' WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$submission_id]);
    }

    public function saveGrade($submission_id, $grade, $feedback) {
        /** * LOGIKA SI: MEMBERI NILAI = ACC (VERIFIKASI)
         * Kita mengupdate status menjadi 'Selesai' secara otomatis.
         */
        $query = "UPDATE submissions SET 
                    grade = ?, 
                    teacher_feedback = ?, 
                    status = 'Selesai' 
                  WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$grade, $feedback, $submission_id]);
    }
}
?>