<?php
class GuruModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // --- 1. DASHBOARD ---

    public function countTodayAttendance($teacher_id) {
        $query = "SELECT COUNT(a.id) as total 
                  FROM attendances a
                  JOIN class_members cm ON a.schedule_id = cm.id
                  JOIN classes c ON cm.class_id = c.id
                  LEFT JOIN class_bands cb ON cm.band_id = cb.id
                  WHERE COALESCE(cb.teacher_id, c.teacher_id) = :tid 
                  AND a.status = 'Menunggu'";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':tid' => $teacher_id]);
        $res = $stmt->fetch();
        return $res['total'] ?? 0;
    }

    // Ambil daftar kelas yang diampu oleh Guru ini (termasuk kelas Group yang Band-nya diampu Guru ini)
    public function getMyClasses($teacher_id) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT c.*
            FROM classes c
            LEFT JOIN class_bands cb ON cb.class_id = c.id
            WHERE c.teacher_id = ? OR cb.teacher_id = ?
            ORDER BY c.name ASC
        ");
        $stmt->execute([$teacher_id, $teacher_id]);
        return $stmt->fetchAll();
    }

    public function canTeacherAccessClass($teacher_id, $class_id) {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM classes c
            LEFT JOIN class_bands cb ON cb.class_id = c.id
            WHERE c.id = ? AND (c.teacher_id = ? OR cb.teacher_id = ?)
            LIMIT 1
        ");
        $stmt->execute([(int)$class_id, (int)$teacher_id, (int)$teacher_id]);
        return (bool)$stmt->fetch();
    }

    // --- 2. PROGRESS, KURIKULUM BAB/SUBBAB & JURNAL ---

    public function getStudentsInClass($class_id, $teacher_id = null) {
        $params = [':class_id' => $class_id];
        $teacherFilter = "";
        if ($teacher_id !== null) {
            $teacherFilter = " AND COALESCE(cb.teacher_id, c.teacher_id) = :tid ";
            $params[':tid'] = (int)$teacher_id;
        }
        $query = "SELECT u.id, u.name, u.photo_profile, u.branch,
                         GROUP_CONCAT(DISTINCT cb.name SEPARATOR ', ') as band_names,
                         MIN(cm.joined_at) as joined_at
                  FROM class_members cm
                  JOIN classes c ON cm.class_id = c.id
                  LEFT JOIN class_bands cb ON cm.band_id = cb.id
                  JOIN users u ON cm.student_id = u.id
                  WHERE cm.class_id = :class_id {$teacherFilter}
                  GROUP BY u.id
                  ORDER BY u.name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($students as &$st) {
            $summary = $this->getStudentCurriculumSummary($st['id'], $class_id);
            $st['progress_percent'] = $summary['overall_percent'];
            $st['total_chapters']   = $summary['total_chapters'];
        }
        return $students;
    }

    public function getMyStudents($teacher_id) {
        $query = "SELECT u.id, u.name, cm.class_id 
                  FROM users u
                  JOIN class_members cm ON u.id = cm.student_id
                  JOIN classes c ON cm.class_id = c.id
                  LEFT JOIN class_bands cb ON cm.band_id = cb.id
                  WHERE COALESCE(cb.teacher_id, c.teacher_id) = ?
                  GROUP BY u.id, cm.class_id
                  ORDER BY u.name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    }

    // --- KURIKULUM BAB & SUBBAB PER SISWA + PERSENTASE OTOMATIS RAPORT ---

    public function getChaptersWithSubchapters($student_id, $class_id = null) {
        $params = [':sid' => (int)$student_id];
        $classFilter = "";
        if ($class_id !== null && (int)$class_id > 0) {
            $classFilter = " AND pc.class_id = :cid ";
            $params[':cid'] = (int)$class_id;
        }

        $stmt = $this->db->prepare("
            SELECT pc.*, c.name as class_name, c.instrument, u.name as teacher_name
            FROM progress_chapters pc
            JOIN classes c ON pc.class_id = c.id
            JOIN users u ON pc.teacher_id = u.id
            WHERE pc.student_id = :sid {$classFilter}
            ORDER BY pc.sort_order ASC, pc.id ASC
        ");
        $stmt->execute($params);
        $chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $overallTotal = 0;
        $overallCompleted = 0;

        if (!empty($chapters)) {
            $stmtSub = $this->db->prepare("
                SELECT * FROM progress_subchapters
                WHERE chapter_id = :chid
                ORDER BY id ASC
            ");
            foreach ($chapters as &$ch) {
                $stmtSub->execute([':chid' => $ch['id']]);
                $subs = $stmtSub->fetchAll(PDO::FETCH_ASSOC);
                $total = count($subs);
                $done  = 0;
                foreach ($subs as $sb) {
                    if ((int)$sb['is_completed'] === 1) {
                        $done++;
                    }
                }
                $ch['subchapters']     = $subs;
                $ch['total_sub']       = $total;
                $ch['completed_sub']   = $done;
                $ch['percent']         = $total > 0 ? (int)round(($done / $total) * 100) : 0;
                $overallTotal         += $total;
                $overallCompleted     += $done;
            }
        }

        return [
            'chapters'          => $chapters,
            'total_chapters'    => count($chapters),
            'overall_total'     => $overallTotal,
            'overall_completed' => $overallCompleted,
            'overall_percent'   => $overallTotal > 0 ? (int)round(($overallCompleted / $overallTotal) * 100) : 0
        ];
    }

    public function getStudentCurriculumSummary($student_id, $class_id = null) {
        $data = $this->getChaptersWithSubchapters($student_id, $class_id);
        return [
            'total_chapters'    => $data['total_chapters'],
            'overall_total'     => $data['overall_total'],
            'overall_completed' => $data['overall_completed'],
            'overall_percent'   => $data['overall_percent']
        ];
    }

    public function createChapter($data) {
        $stmtOrder = $this->db->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM progress_chapters WHERE student_id = ? AND class_id = ?");
        $stmtOrder->execute([(int)$data['student_id'], (int)$data['class_id']]);
        $nextOrder = (int)$stmtOrder->fetchColumn();

        $stmt = $this->db->prepare("
            INSERT INTO progress_chapters (class_id, student_id, teacher_id, title, sort_order)
            VALUES (:cid, :sid, :tid, :title, :ord)
        ");
        $ok = $stmt->execute([
            ':cid'   => (int)$data['class_id'],
            ':sid'   => (int)$data['student_id'],
            ':tid'   => (int)$data['teacher_id'],
            ':title' => trim($data['title']),
            ':ord'   => $nextOrder
        ]);
        return $ok ? $this->db->lastInsertId() : false;
    }

    public function getChapterById($chapter_id) {
        $stmt = $this->db->prepare("SELECT * FROM progress_chapters WHERE id = ?");
        $stmt->execute([(int)$chapter_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateChapter($chapter_id, $teacher_id, $title) {
        $ch = $this->getChapterById($chapter_id);
        if (!$ch || ((int)$ch['teacher_id'] !== (int)$teacher_id && !$this->canTeacherAccessClass($teacher_id, $ch['class_id']))) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE progress_chapters SET title = :t WHERE id = :id");
        return $stmt->execute([':t' => trim($title), ':id' => (int)$chapter_id]);
    }

    public function deleteChapter($chapter_id, $teacher_id) {
        $ch = $this->getChapterById($chapter_id);
        if (!$ch || ((int)$ch['teacher_id'] !== (int)$teacher_id && !$this->canTeacherAccessClass($teacher_id, $ch['class_id']))) {
            return false;
        }
        $stmt = $this->db->prepare("DELETE FROM progress_chapters WHERE id = ?");
        return $stmt->execute([(int)$chapter_id]);
    }

    public function createSubchapter($chapter_id, $teacher_id, $title, $notes = null, $is_completed = 0) {
        $ch = $this->getChapterById($chapter_id);
        if (!$ch || ((int)$ch['teacher_id'] !== (int)$teacher_id && !$this->canTeacherAccessClass($teacher_id, $ch['class_id']))) {
            return false;
        }
        $done = ((int)$is_completed === 1) ? 1 : 0;
        $completedAt = $done ? date('Y-m-d') : null;
        $stmt = $this->db->prepare("
            INSERT INTO progress_subchapters (chapter_id, title, is_completed, notes, completed_at)
            VALUES (:chid, :title, :done, :notes, :cat)
        ");
        return $stmt->execute([
            ':chid'  => (int)$chapter_id,
            ':title' => trim($title),
            ':done'  => $done,
            ':notes' => !empty($notes) ? trim($notes) : null,
            ':cat'   => $completedAt
        ]);
    }

    public function getSubchapterWithOwner($subchapter_id) {
        $stmt = $this->db->prepare("
            SELECT ps.*, pc.teacher_id, pc.class_id, pc.student_id
            FROM progress_subchapters ps
            JOIN progress_chapters pc ON ps.chapter_id = pc.id
            WHERE ps.id = ?
        ");
        $stmt->execute([(int)$subchapter_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function toggleSubchapter($subchapter_id, $teacher_id, $is_completed, $notes = null) {
        $sub = $this->getSubchapterWithOwner($subchapter_id);
        if (!$sub || ((int)$sub['teacher_id'] !== (int)$teacher_id && !$this->canTeacherAccessClass($teacher_id, $sub['class_id']))) {
            return false;
        }
        $done = ((int)$is_completed === 1) ? 1 : 0;
        $completedAt = $done ? date('Y-m-d') : null;
        $notesToSave = ($notes !== null) ? (trim($notes) !== '' ? trim($notes) : null) : $sub['notes'];

        $stmt = $this->db->prepare("
            UPDATE progress_subchapters
            SET is_completed = :done, completed_at = :cat, notes = :notes
            WHERE id = :id
        ");
        return $stmt->execute([
            ':done'  => $done,
            ':cat'   => $completedAt,
            ':notes' => $notesToSave,
            ':id'    => (int)$subchapter_id
        ]);
    }

    public function updateSubchapter($subchapter_id, $teacher_id, $title, $notes, $is_completed) {
        $sub = $this->getSubchapterWithOwner($subchapter_id);
        if (!$sub || ((int)$sub['teacher_id'] !== (int)$teacher_id && !$this->canTeacherAccessClass($teacher_id, $sub['class_id']))) {
            return false;
        }
        $done = ((int)$is_completed === 1) ? 1 : 0;
        $completedAt = $done ? ($sub['completed_at'] ?: date('Y-m-d')) : null;
        $stmt = $this->db->prepare("
            UPDATE progress_subchapters
            SET title = :title, notes = :notes, is_completed = :done, completed_at = :cat
            WHERE id = :id
        ");
        return $stmt->execute([
            ':title' => trim($title),
            ':notes' => trim((string)$notes) !== '' ? trim($notes) : null,
            ':done'  => $done,
            ':cat'   => $completedAt,
            ':id'    => (int)$subchapter_id
        ]);
    }

    public function deleteSubchapter($subchapter_id, $teacher_id) {
        $sub = $this->getSubchapterWithOwner($subchapter_id);
        if (!$sub || ((int)$sub['teacher_id'] !== (int)$teacher_id && !$this->canTeacherAccessClass($teacher_id, $sub['class_id']))) {
            return false;
        }
        $stmt = $this->db->prepare("DELETE FROM progress_subchapters WHERE id = ?");
        return $stmt->execute([(int)$subchapter_id]);
    }

    // --- CATATAN PERTEMUAN TAMBAHAN (LEGACY PROGRESS LOGS TETAP AMAN) ---

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

    public function getProgressHistory($class_id, $teacher_id, $student_id = null) {
        $params = [$class_id, $teacher_id];
        $studSql = "";
        if ($student_id !== null) {
            $studSql = " AND progress_logs.student_id = ? ";
            $params[] = (int)$student_id;
        }
        $query = "SELECT progress_logs.*, users.name as student_name 
                  FROM progress_logs 
                  JOIN users ON progress_logs.student_id = users.id
                  WHERE progress_logs.class_id = ? AND progress_logs.teacher_id = ? {$studSql}
                  ORDER BY progress_logs.date DESC, progress_logs.created_at DESC LIMIT 20";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
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

    public function getProgressById($id) {
        $stmt = $this->db->prepare("SELECT p.*, c.teacher_id as class_teacher_id FROM progress_logs p JOIN classes c ON p.class_id = c.id WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteProgress($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM progress_logs WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // --- 3. MATERI ---

    public function getMaterials($teacher_id) {
        $query = "SELECT DISTINCT m.*, c.name as class_name, u.name as student_name 
                  FROM materials m
                  JOIN classes c ON m.class_id = c.id
                  LEFT JOIN class_bands cb ON cb.class_id = c.id
                  LEFT JOIN users u ON m.student_id = u.id
                  WHERE c.teacher_id = ? OR cb.teacher_id = ?
                  ORDER BY m.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$teacher_id, $teacher_id]);
        return $stmt->fetchAll();
    }

    public function updateMaterial($id, $data) {
        $query = "UPDATE materials SET class_id = ?, student_id = ?, title = ?, description = ?, video_url = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$data['class_id'], $data['student_id'], $data['title'], $data['description'], $data['video_url'], $id]);
    }

    public function saveMaterial($data) {
        $query = "INSERT INTO materials (class_id, student_id, title, description, video_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$data['class_id'], $data['student_id'], $data['title'], $data['description'], $data['video_url']]);
    }

    public function deleteMaterial($id) {
        $stmt = $this->db->prepare("DELETE FROM materials WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getMaterialById($id) {
        $stmt = $this->db->prepare("SELECT m.*, c.teacher_id FROM materials m JOIN classes c ON m.class_id = c.id WHERE m.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- 4. STATISTIK GURU & JADWAL MENGAJAR ---

    public function getTotalKelas($teacher_id) {
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT c.id) as total
            FROM classes c
            LEFT JOIN class_bands cb ON cb.class_id = c.id
            WHERE c.teacher_id = ? OR cb.teacher_id = ?
        ");
        $stmt->execute([$teacher_id, $teacher_id]);
        $res = $stmt->fetch();
        return $res['total'] ?? 0;
    }

    public function getTotalSiswa($teacher_id) {
        $query = "SELECT COUNT(DISTINCT cm.student_id) as total 
                  FROM class_members cm 
                  JOIN classes c ON cm.class_id = c.id
                  LEFT JOIN class_bands cb ON cm.band_id = cb.id
                  WHERE COALESCE(cb.teacher_id, c.teacher_id) = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$teacher_id]);
        $res = $stmt->fetch();
        return $res['total'] ?? 0;
    }

    public function getTotalPendingAbsen($teacher_id) {
        return $this->countTodayAttendance($teacher_id);
    }

    // Jadwal mengajar guru (menggabungkan anggota Band dalam 1 sesi jadwal agar presensi & tampilan rapi)
    public function getJadwalByGuru($teacher_id) {
        $query = "SELECT 
                    MIN(cm.id) as schedule_id,
                    cm.day,
                    cm.start_time,
                    cm.end_time,
                    c.name as class_name,
                    c.type,
                    c.id as class_id,
                    cb.name as band_name,
                    CASE 
                        WHEN cm.band_id IS NOT NULL THEN CONCAT(cb.name, ': ', GROUP_CONCAT(u.name ORDER BY u.name SEPARATOR ', '))
                        ELSE u.name
                    END as student_name
                  FROM class_members cm
                  JOIN classes c ON cm.class_id = c.id
                  LEFT JOIN class_bands cb ON cm.band_id = cb.id
                  JOIN users u ON cm.student_id = u.id
                  WHERE COALESCE(cb.teacher_id, c.teacher_id) = ?
                  GROUP BY CASE WHEN cm.band_id IS NOT NULL THEN CONCAT('band_', cm.band_id) ELSE CONCAT('cm_', cm.id) END
                  ORDER BY FIELD(cm.day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), 
                           cm.start_time ASC, schedule_id ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    }
}
?>
