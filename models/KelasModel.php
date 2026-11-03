<?php
class KelasModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Ambil semua data kelas + Nama Gurunya + Jumlah Band / Anggota
    public function getAll() {
        $query = "SELECT classes.*,
                         users.name as guru_name,
                         (SELECT COUNT(*) FROM class_bands cb WHERE cb.class_id = classes.id) as total_bands,
                         (SELECT COUNT(DISTINCT cm.student_id) FROM class_members cm WHERE cm.class_id = classes.id) as total_members
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

    // 1. Ambil Detail Kelas berdasarkan ID
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
                    u.id as student_id,
                    u.name, 
                    u.parent_name,
                    u.photo_profile,
                    u.branch,
                    MIN(cm.id) as member_id,
                    MIN(cm.joined_at) as joined_at,
                    GROUP_CONCAT(
                        CONCAT(
                            CASE WHEN cb.name IS NOT NULL THEN CONCAT('[', cb.name, '] ') ELSE '' END,
                            cm.day, ' (', TIME_FORMAT(cm.start_time, '%H:%i'), '-', TIME_FORMAT(cm.end_time, '%H:%i'), ')'
                        ) SEPARATOR ', '
                    ) as jadwal_lengkap
                  FROM class_members cm
                  JOIN users u ON cm.student_id = u.id
                  LEFT JOIN class_bands cb ON cm.band_id = cb.id
                  WHERE cm.class_id = ?
                  GROUP BY u.id
                  ORDER BY u.name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$class_id]);
        return $stmt->fetchAll();
    }

    // Cek apakah siswa sudah terdaftar di kelas (non-band / umum)
    public function isMember($class_id, $student_id) {
        $stmt = $this->db->prepare("SELECT 1 FROM class_members WHERE class_id = ? AND student_id = ? LIMIT 1");
        $stmt->execute([$class_id, $student_id]);
        return (bool)$stmt->fetch();
    }

    // 3. Masukkan Siswa ke Kelas (Enroll)
    public function addMember($data) {
        try {
            $band_id = !empty($data['band_id']) ? (int)$data['band_id'] : null;
            $query = "INSERT INTO class_members (student_id, band_id, class_id, day, start_time, end_time) 
                      VALUES (:student_id, :band_id, :class_id, :day, :start_time, :end_time)";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':student_id' => $data['student_id'],
                ':band_id'    => $band_id,
                ':class_id'   => $data['class_id'],
                ':day'        => $data['day'],
                ':start_time' => $data['start_time'],
                ':end_time'   => $data['end_time']
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                error_log("Duplicate entry: Siswa {$data['student_id']} sudah terdaftar di slot jadwal tersebut.");
            }
            return false;
        }
    }

    // --- FITUR GROUP CLASS -> BAND (Band 1, Band 2, Band 3 -> Siswa) ---

    public function getBandsByClass($class_id) {
        $stmt = $this->db->prepare("
            SELECT cb.*, u.name as teacher_name
            FROM class_bands cb
            JOIN users u ON cb.teacher_id = u.id
            WHERE cb.class_id = :cid
            ORDER BY cb.id ASC
        ");
        $stmt->execute([':cid' => $class_id]);
        $bands = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($bands)) {
            $stmtMem = $this->db->prepare("
                SELECT cm.id as member_id, cm.band_id, cm.student_id, cm.joined_at,
                       u.name as student_name, u.parent_name, u.phone, u.photo_profile, u.branch, u.instrument
                FROM class_members cm
                JOIN users u ON cm.student_id = u.id
                WHERE cm.band_id = :bid
                ORDER BY u.name ASC
            ");
            foreach ($bands as &$band) {
                $stmtMem->execute([':bid' => $band['id']]);
                $band['members'] = $stmtMem->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        return $bands;
    }

    public function getBandById($band_id) {
        $stmt = $this->db->prepare("SELECT * FROM class_bands WHERE id = ?");
        $stmt->execute([(int)$band_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createBand($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO class_bands (class_id, name, teacher_id, day, start_time, end_time)
                VALUES (:cid, :name, :tid, :day, :st, :et)
            ");
            $ok = $stmt->execute([
                ':cid'  => (int)$data['class_id'],
                ':name' => trim($data['name']),
                ':tid'  => (int)$data['teacher_id'],
                ':day'  => $data['day'],
                ':st'   => $data['start_time'],
                ':et'   => $data['end_time']
            ]);
            return $ok ? $this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("Gagal Create Band: " . $e->getMessage());
            return false;
        }
    }

    public function updateBand($band_id, $data) {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("
                UPDATE class_bands
                SET name = :name, teacher_id = :tid, day = :day, start_time = :st, end_time = :et
                WHERE id = :id
            ");
            $stmt->execute([
                ':name' => trim($data['name']),
                ':tid'  => (int)$data['teacher_id'],
                ':day'  => $data['day'],
                ':st'   => $data['start_time'],
                ':et'   => $data['end_time'],
                ':id'   => (int)$band_id
            ]);

            // Sinkronisasi otomatis hari & jam pada seluruh anggota siswa di dalam band ini
            $stmtSync = $this->db->prepare("
                UPDATE class_members
                SET day = :day, start_time = :st, end_time = :et
                WHERE band_id = :bid
            ");
            $stmtSync->execute([
                ':day' => $data['day'],
                ':st'  => $data['start_time'],
                ':et'  => $data['end_time'],
                ':bid' => (int)$band_id
            ]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Gagal Update Band: " . $e->getMessage());
            return false;
        }
    }

    public function deleteBand($band_id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM class_bands WHERE id = ?");
            return $stmt->execute([(int)$band_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function isBandMember($band_id, $student_id) {
        $stmt = $this->db->prepare("SELECT 1 FROM class_members WHERE band_id = ? AND student_id = ? LIMIT 1");
        $stmt->execute([(int)$band_id, (int)$student_id]);
        return (bool)$stmt->fetch();
    }

    // Validasi bentrok jadwal guru (mengabaikan siswa lain yang berada dalam band_id yang sama)
    public function isConflict($teacher_id, $day, $start_time, $end_time, $ignore_id = null, $ignore_band_id = null) {
        $query = "SELECT COUNT(*) as total 
                  FROM class_members cm
                  JOIN classes c ON cm.class_id = c.id
                  LEFT JOIN class_bands cb ON cm.band_id = cb.id
                  WHERE COALESCE(cb.teacher_id, c.teacher_id) = :teacher_id 
                  AND cm.day = :day
                  AND (:start_new < cm.end_time AND :end_new > cm.start_time)";
        
        $params = [
            ':teacher_id' => $teacher_id,
            ':day'        => $day,
            ':start_new'  => $start_time,
            ':end_new'    => $end_time
        ];

        if ($ignore_id) {
            $query .= " AND cm.id != :ignore_id";
            $params[':ignore_id'] = $ignore_id;
        }
        if ($ignore_band_id) {
            $query .= " AND (cm.band_id IS NULL OR cm.band_id != :ignore_band_id)";
            $params[':ignore_band_id'] = $ignore_band_id;
        }

        // Cek juga di class_bands kosong yang belum ada anggotanya
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        if (($result['total'] ?? 0) > 0) {
            return true;
        }

        $qBand = "SELECT COUNT(*) as total FROM class_bands cb
                  WHERE cb.teacher_id = :tid AND cb.day = :day
                  AND (:st < cb.end_time AND :et > cb.start_time)";
        $pBand = [':tid' => $teacher_id, ':day' => $day, ':st' => $start_time, ':et' => $end_time];
        if ($ignore_band_id) {
            $qBand .= " AND cb.id != :ibid";
            $pBand[':ibid'] = $ignore_band_id;
        }
        $stmtB = $this->db->prepare($qBand);
        $stmtB->execute($pBand);
        $resB = $stmtB->fetch();

        return ($resB['total'] ?? 0) > 0;
    }

    // Alias untuk kompatibilitas SiswaController::update_jadwal() (Mencegah Fatal Error)
    public function isScheduleConflict($teacher_id, $day, $start_time, $end_time, $ignore_id = null, $ignore_band_id = null) {
        return $this->isConflict($teacher_id, $day, $start_time, $end_time, $ignore_id, $ignore_band_id);
    }

    public function isStudentConflict($student_id, $day, $start_time, $end_time, $ignore_id = null) {
        $sql = "SELECT 1 FROM class_members cm
                WHERE cm.student_id = ? AND cm.day = ?
                AND NOT (cm.end_time <= ? OR cm.start_time >= ?)";
        $params = [$student_id, $day, $start_time, $end_time];
        if ($ignore_id) {
            $sql .= " AND cm.id != ?";
            $params[] = $ignore_id;
        }
        $sql .= " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }

    // 4. Keluarkan Siswa dari Kelas / Band
    public function removeMember($member_id) {
        $stmt = $this->db->prepare("DELETE FROM class_members WHERE id = ?");
        return $stmt->execute([$member_id]);
    }

    // 5. Hapus Kelas
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM classes WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
