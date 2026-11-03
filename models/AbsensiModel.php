<?php
class AbsensiModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getConfig($key, $default = null)
    {
        static $config = null;
        if ($config === null) {
            $configFile = __DIR__ . '/../config/app.php';
            if (file_exists($configFile)) {
                $config = require $configFile;
            } else {
                $config = [];
            }
        }
        return $config[$key] ?? $default;
    }

    public function getStudioConfig()
    {
        return [
            'salary_per_hour'   => (float)$this->getConfig('salary_per_hour', 30000),
            'penalty_per_10min' => (float)$this->getConfig('penalty_per_10min', 5000),
        ];
    }

    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad((float)$lat2 - (float)$lat1);
        $dLon = deg2rad((float)$lon2 - (float)$lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad((float)$lat1)) * cos(deg2rad((float)$lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    // Cek apakah siswa sudah absen hari ini di jadwal ini
    public function cekSudahAbsen($student_id, $schedule_id, $date)
    {
        $stmt = $this->db->prepare("SELECT id FROM attendances WHERE student_id = ? AND schedule_id = ? AND date = ?");
        $stmt->execute([$student_id, $schedule_id, $date]);
        return $stmt->rowCount() > 0;
    }

    // Simpan Data Absensi Siswa Baru (Lokasi fleksibel dari perangkat pengguna)
    public function create($data)
    {
        $query = "INSERT INTO attendances (schedule_id, student_id, date, status, photo_proof, location_lat, location_long) 
                  VALUES (:sid, :stud_id, :date, 'Hadir', :photo, :lat, :long)";

        $stmt = $this->db->prepare($query);
        $photo = !empty($data['photo']) ? $data['photo'] : null;
        $lat   = (isset($data['lat']) && $data['lat'] !== '') ? (string)$data['lat'] : '0';
        $long  = (isset($data['long']) && $data['long'] !== '') ? (string)$data['long'] : '0';

        return $stmt->execute([
            ':sid'     => $data['schedule_id'],
            ':stud_id' => $data['student_id'],
            ':date'    => $data['date'],
            ':photo'   => $photo,
            ':lat'     => $lat,
            ':long'    => $long
        ]);
    }

    // Ambil detail 1 absensi beserta ID guru pemiliknya (Validasi anti-IDOR)
    public function getAttendanceByIdWithTeacher($id)
    {
        $stmt = $this->db->prepare("
            SELECT a.*, COALESCE(cb.teacher_id, c.teacher_id) as teacher_id
            FROM attendances a
            JOIN class_members cm ON a.schedule_id = cm.id
            JOIN classes c ON cm.class_id = c.id
            LEFT JOIN class_bands cb ON cm.band_id = cb.id
            WHERE a.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPendingAbsensiByGuru($teacher_id)
    {
        $query = "SELECT a.*, u.name as student_name, c.name as class_name, cb.name as band_name, cm.start_time, cm.end_time
                  FROM attendances a
                  JOIN users u ON a.student_id = u.id
                  JOIN class_members cm ON a.schedule_id = cm.id
                  JOIN classes c ON cm.class_id = c.id
                  LEFT JOIN class_bands cb ON cm.band_id = cb.id
                  WHERE COALESCE(cb.teacher_id, c.teacher_id) = :teacher_id AND a.status = 'Menunggu'
                  ORDER BY a.created_at ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':teacher_id' => $teacher_id]);
        return $stmt->fetchAll();
    }

    public function getStudentAttendanceSummary($teacher_id)
    {
        $query = "SELECT 
                    u.id as student_id, 
                    u.name as student_name, 
                    u.parent_name,
                    u.photo_profile,
                    GROUP_CONCAT(DISTINCT CASE WHEN cb.name IS NOT NULL THEN CONCAT(c.name, ' (', cb.name, ')') ELSE c.name END SEPARATOR ', ') as class_names,
                    COUNT(CASE WHEN a.status = 'Hadir' THEN 1 END) as total_hadir,
                    COUNT(CASE WHEN a.status IN ('Izin', 'Sakit') THEN 1 END) as total_izin,
                    COUNT(CASE WHEN a.status = 'Ditolak' THEN 1 END) as total_alpha
                  FROM users u
                  JOIN class_members cm ON u.id = cm.student_id
                  JOIN classes c ON cm.class_id = c.id
                  LEFT JOIN class_bands cb ON cm.band_id = cb.id
                  LEFT JOIN attendances a ON cm.id = a.schedule_id AND a.status != 'Menunggu'
                  WHERE COALESCE(cb.teacher_id, c.teacher_id) = :tid
                  GROUP BY u.id
                  ORDER BY u.name ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':tid' => $teacher_id]);
        return $stmt->fetchAll();
    }

    public function getAttendanceDetailByStudent($student_id, $teacher_id)
    {
        $query = "SELECT a.*, c.name as class_name, cb.name as band_name
                  FROM attendances a
                  JOIN class_members cm ON a.schedule_id = cm.id
                  JOIN classes c ON cm.class_id = c.id
                  LEFT JOIN class_bands cb ON cm.band_id = cb.id
                  WHERE a.student_id = :sid 
                  AND COALESCE(cb.teacher_id, c.teacher_id) = :tid 
                  AND a.status != 'Menunggu'
                  ORDER BY a.date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':sid' => $student_id, ':tid' => $teacher_id]);
        return $stmt->fetchAll();
    }

    public function getAbsensiByGuru($teacher_id)
    {
        $query = "SELECT a.*, 
                         u.name as student_name, 
                         c.name as class_name,
                         cb.name as band_name,
                         cm.start_time, cm.end_time
                  FROM attendances a
                  JOIN users u ON a.student_id = u.id
                  JOIN class_members cm ON a.schedule_id = cm.id
                  JOIN classes c ON cm.class_id = c.id
                  LEFT JOIN class_bands cb ON cm.band_id = cb.id
                  WHERE COALESCE(cb.teacher_id, c.teacher_id) = :teacher_id
                  ORDER BY a.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':teacher_id' => $teacher_id]);
        return $stmt->fetchAll();
    }

    public function updateStatus($id, $status)
    {
        $allowed = ['Menunggu', 'Hadir', 'Izin', 'Sakit', 'Ditolak'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE attendances SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => (int)$id]);
    }

    // Presensi Guru Multi-Lokasi (Tanpa blokir radius titik tunggal; merekam koordinat pengguna langsung)
    public function submitTeacherAttendance($data)
    {
        $daftar_hari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        $hari_indo = $daftar_hari[date('l')];

        $queryCekJadwal = "SELECT COUNT(*)
                           FROM class_members cm
                           JOIN classes c ON cm.class_id = c.id
                           LEFT JOIN class_bands cb ON cm.band_id = cb.id
                           WHERE COALESCE(cb.teacher_id, c.teacher_id) = :tid AND cm.day = :day";
        $stmtCek = $this->db->prepare($queryCekJadwal);
        $stmtCek->execute([':tid' => $data['teacher_id'], ':day' => $hari_indo]);
        if ($stmtCek->fetchColumn() == 0) {
            return "tidak_ada_jadwal";
        }

        $current_time = $data['check_in_time'];
        $req_schedule_id = isset($data['schedule_id']) ? (int)$data['schedule_id'] : 0;

        if ($req_schedule_id > 0) {
            $queryJadwal = "SELECT cm.id as schedule_id, cm.band_id, cm.start_time, cm.end_time 
                            FROM class_members cm
                            JOIN classes c ON cm.class_id = c.id
                            LEFT JOIN class_bands cb ON cm.band_id = cb.id
                            WHERE cm.id = :sid AND COALESCE(cb.teacher_id, c.teacher_id) = :tid AND cm.day = :day
                            AND :current_time1 >= SUBTIME(cm.start_time, '00:05:00')
                            AND :current_time2 <= cm.end_time";
            $stmtJadwal = $this->db->prepare($queryJadwal);
            $stmtJadwal->execute([
                ':sid' => $req_schedule_id,
                ':tid' => $data['teacher_id'],
                ':day' => $hari_indo,
                ':current_time1' => $current_time,
                ':current_time2' => $current_time
            ]);
            $jadwal = $stmtJadwal->fetch(PDO::FETCH_ASSOC);
        } else {
            $queryJadwal = "SELECT cm.id as schedule_id, cm.band_id, cm.start_time, cm.end_time 
                            FROM class_members cm
                            JOIN classes c ON cm.class_id = c.id
                            LEFT JOIN class_bands cb ON cm.band_id = cb.id
                            WHERE COALESCE(cb.teacher_id, c.teacher_id) = :tid AND cm.day = :day
                            AND :current_time1 >= SUBTIME(cm.start_time, '00:05:00')
                            AND :current_time2 <= cm.end_time
                            ORDER BY cm.start_time ASC, cm.id ASC LIMIT 1";
            $stmtJadwal = $this->db->prepare($queryJadwal);
            $stmtJadwal->execute([
                ':tid' => $data['teacher_id'],
                ':day' => $hari_indo,
                ':current_time1' => $current_time,
                ':current_time2' => $current_time
            ]);
            $jadwal = $stmtJadwal->fetch(PDO::FETCH_ASSOC);
        }

        if (!$jadwal) {
            if ($req_schedule_id > 0) {
                $queryFuture = "SELECT COUNT(*) FROM class_members cm 
                                JOIN classes c ON cm.class_id = c.id
                                LEFT JOIN class_bands cb ON cm.band_id = cb.id
                                WHERE cm.id = :sid AND COALESCE(cb.teacher_id, c.teacher_id) = :tid AND cm.day = :day 
                                AND SUBTIME(cm.start_time, '00:05:00') > :current_time";
                $stmtFuture = $this->db->prepare($queryFuture);
                $stmtFuture->execute([
                    ':sid' => $req_schedule_id,
                    ':tid' => $data['teacher_id'],
                    ':day' => $hari_indo,
                    ':current_time' => $current_time
                ]);
            } else {
                $queryFuture = "SELECT COUNT(*) FROM class_members cm 
                                JOIN classes c ON cm.class_id = c.id
                                LEFT JOIN class_bands cb ON cm.band_id = cb.id
                                WHERE COALESCE(cb.teacher_id, c.teacher_id) = :tid AND cm.day = :day 
                                AND SUBTIME(cm.start_time, '00:05:00') > :current_time";
                $stmtFuture = $this->db->prepare($queryFuture);
                $stmtFuture->execute([
                    ':tid' => $data['teacher_id'],
                    ':day' => $hari_indo,
                    ':current_time' => $current_time
                ]);
            }
            if ($stmtFuture->fetchColumn() > 0) {
                return "absen_belum_dibuka";
            }
            return "absen_ditutup";
        }

        // Kanonikalisasi schedule_id untuk sesi Band agar 1 Band dihitung 1 sesi mengajar
        $canonicalScheduleId = (int)$jadwal['schedule_id'];
        if (!empty($jadwal['band_id'])) {
            $stmtMin = $this->db->prepare("SELECT MIN(id) FROM class_members WHERE band_id = ?");
            $stmtMin->execute([(int)$jadwal['band_id']]);
            $minId = (int)$stmtMin->fetchColumn();
            if ($minId > 0) {
                $canonicalScheduleId = $minId;
            }
        }

        // Prevent Double Absen
        $stmtCekAbsen = $this->db->prepare("SELECT COUNT(*) FROM teacher_attendances WHERE teacher_id = :tid AND schedule_id = :sid AND date = :date");
        $stmtCekAbsen->execute([
            ':tid'  => $data['teacher_id'],
            ':sid'  => $canonicalScheduleId,
            ':date' => $data['date']
        ]);
        if ($stmtCekAbsen->fetchColumn() > 0) {
            return "sudah_absen";
        }

        $startTime = $jadwal['start_time'];
        $checkIn   = $data['check_in_time'];

        $startSecs = strtotime($jadwal['start_time']);
        $endSecs   = strtotime($jadwal['end_time']);
        $durationInHours = ($endSecs - $startSecs) / 3600;
        if ($durationInHours < 0.1) {
            $durationInHours = 1;
        }
        $salaryPerHour = (float)$this->getConfig('salary_per_hour', 30000);
        $baseSalary    = $durationInHours * $salaryPerHour;

        // Lokasi diambil langsung dari koordinat perangkat pengguna tanpa batasan satu titik studio
        $latitude  = (isset($data['latitude']) && $data['latitude'] !== '') ? (string)$data['latitude'] : '0';
        $longitude = (isset($data['longitude']) && $data['longitude'] !== '') ? (string)$data['longitude'] : '0';

        // Hitung potongan keterlambatan secara otomatis di balik layar (Rp 5.000 per 10 menit)
        $penalty = 0;
        $penaltyPer10Min = (float)$this->getConfig('penalty_per_10min', 5000);
        if (strtotime($checkIn) > strtotime($startTime)) {
            $diffInSeconds = strtotime($checkIn) - strtotime($startTime);
            $diffInMinutes = floor($diffInSeconds / 60);
            $penalty = floor($diffInMinutes / 10) * $penaltyPer10Min;
        }

        $totalSalary = max(0, $baseSalary - $penalty);

        $query = "INSERT INTO teacher_attendances 
                  (teacher_id, schedule_id, date, check_in_time, photo_proof, latitude, longitude, base_salary, penalty_amount, total_salary) 
                  VALUES (:tid, :sid, :d, :ct, :pp, :lat, :lng, :bs, :pa, :ts)";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':tid' => $data['teacher_id'],
            ':sid' => $canonicalScheduleId,
            ':d'   => $data['date'],
            ':ct'  => $checkIn,
            ':pp'  => $data['photo'],
            ':lat' => $latitude,
            ':lng' => $longitude,
            ':bs'  => $baseSalary,
            ':pa'  => $penalty,
            ':ts'  => $totalSalary
        ]);
    }

    // Daftar seluruh slot jadwal aktif guru untuk form konfirmasi/susulan Admin
    public function getAllActiveSchedulesForAdmin()
    {
        $sql = "SELECT 
                    MIN(cm.id) as schedule_id,
                    COALESCE(cb.teacher_id, c.teacher_id) as teacher_id,
                    ut.name as teacher_name,
                    c.name as class_name,
                    cb.name as band_name,
                    cm.day,
                    cm.start_time,
                    cm.end_time,
                    GROUP_CONCAT(u.name ORDER BY u.name SEPARATOR ', ') as student_names
                FROM class_members cm
                JOIN classes c ON cm.class_id = c.id
                LEFT JOIN class_bands cb ON cm.band_id = cb.id
                JOIN users ut ON COALESCE(cb.teacher_id, c.teacher_id) = ut.id
                JOIN users u ON cm.student_id = u.id
                GROUP BY CASE WHEN cm.band_id IS NOT NULL THEN CONCAT('band_', cm.band_id) ELSE CONCAT('cm_', cm.id) END
                ORDER BY ut.name ASC, FIELD(cm.day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), cm.start_time ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Konfirmasi Admin untuk guru lupa absen / terlambat absen
    public function adminConfirmTeacherAttendance($data)
    {
        $attendanceId = !empty($data['attendance_id']) ? (int)$data['attendance_id'] : 0;
        $salaryMode   = ($data['salary_mode'] ?? 'full') === 'actual' ? 'actual' : 'full';
        $checkInTime  = !empty($data['check_in_time']) ? $data['check_in_time'] : '00:00:00';
        if (strlen($checkInTime) === 5) {
            $checkInTime .= ':00';
        }

        if ($attendanceId > 0) {
            $stmt = $this->db->prepare("
                SELECT ta.*, cm.start_time, cm.end_time
                FROM teacher_attendances ta
                JOIN class_members cm ON ta.schedule_id = cm.id
                WHERE ta.id = ?
                LIMIT 1
            ");
            $stmt->execute([$attendanceId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$existing) {
                return false;
            }
            $teacherId  = (int)$existing['teacher_id'];
            $scheduleId = (int)$existing['schedule_id'];
            $date       = $existing['date'];
            $startTime  = $existing['start_time'];
            $endTime    = $existing['end_time'];
            if ($checkInTime === '00:00:00') {
                $checkInTime = ($existing['check_in_time'] !== '00:00:00') ? $existing['check_in_time'] : $startTime;
            }
        } else {
            $scheduleId = (int)($data['schedule_id'] ?? 0);
            $date       = $data['date'] ?? date('Y-m-d');
            if ($scheduleId <= 0) {
                return false;
            }
            $stmtSched = $this->db->prepare("
                SELECT cm.id, cm.band_id, cm.start_time, cm.end_time, COALESCE(cb.teacher_id, c.teacher_id) as teacher_id
                FROM class_members cm
                JOIN classes c ON cm.class_id = c.id
                LEFT JOIN class_bands cb ON cm.band_id = cb.id
                WHERE cm.id = ?
                LIMIT 1
            ");
            $stmtSched->execute([$scheduleId]);
            $sched = $stmtSched->fetch(PDO::FETCH_ASSOC);
            if (!$sched) {
                return false;
            }
            if (!empty($sched['band_id'])) {
                $stmtMin = $this->db->prepare("SELECT MIN(id) FROM class_members WHERE band_id = ?");
                $stmtMin->execute([(int)$sched['band_id']]);
                $minId = (int)$stmtMin->fetchColumn();
                if ($minId > 0) {
                    $scheduleId = $minId;
                }
            }
            $teacherId = (int)$sched['teacher_id'];
            $startTime = $sched['start_time'];
            $endTime   = $sched['end_time'];
            if ($checkInTime === '00:00:00') {
                $checkInTime = $startTime;
            }

            // Cek apakah sudah ada baris di tanggal & jadwal ini (mis. status tidak_absen)
            $stmtEx = $this->db->prepare("SELECT * FROM teacher_attendances WHERE teacher_id = ? AND schedule_id = ? AND date = ? LIMIT 1");
            $stmtEx->execute([$teacherId, $scheduleId, $date]);
            $existing = $stmtEx->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $attendanceId = (int)$existing['id'];
            }
        }

        $startSecs = strtotime($startTime);
        $endSecs   = strtotime($endTime);
        $durationInHours = ($endSecs - $startSecs) / 3600;
        if ($durationInHours < 0.1) {
            $durationInHours = 1;
        }
        $baseSalary = $durationInHours * (float)$this->getConfig('salary_per_hour', 30000);

        $penalty = 0;
        if ($salaryMode === 'actual' && strtotime($checkInTime) > strtotime($startTime)) {
            $diffInMinutes = floor((strtotime($checkInTime) - strtotime($startTime)) / 60);
            $penalty = floor($diffInMinutes / 10) * (float)$this->getConfig('penalty_per_10min', 5000);
        }
        $totalSalary = max(0, $baseSalary - $penalty);

        if ($attendanceId > 0 && !empty($existing)) {
            $keepPhoto = !in_array($existing['photo_proof'], ['tidak_absen', 'confirmed_admin', 'expired_monthly', ''], true);
            $photoVal  = $keepPhoto ? $existing['photo_proof'] : 'confirmed_admin';
            $stmtUpd = $this->db->prepare("
                UPDATE teacher_attendances
                SET check_in_time = :ct,
                    photo_proof = :pp,
                    base_salary = :bs,
                    penalty_amount = :pa,
                    total_salary = :ts
                WHERE id = :id
            ");
            return $stmtUpd->execute([
                ':ct' => $checkInTime,
                ':pp' => $photoVal,
                ':bs' => $baseSalary,
                ':pa' => $penalty,
                ':ts' => $totalSalary,
                ':id' => $attendanceId
            ]);
        }

        $stmtIns = $this->db->prepare("
            INSERT INTO teacher_attendances
            (teacher_id, schedule_id, date, check_in_time, photo_proof, latitude, longitude, base_salary, penalty_amount, total_salary)
            VALUES (:tid, :sid, :d, :ct, 'confirmed_admin', '0', '0', :bs, :pa, :ts)
        ");
        return $stmtIns->execute([
            ':tid' => $teacherId,
            ':sid' => $scheduleId,
            ':d'   => $date,
            ':ct'  => $checkInTime,
            ':bs'  => $baseSalary,
            ':pa'  => $penalty,
            ':ts'  => $totalSalary
        ]);
    }

    // Pembersihan otomatis foto bukti absen guru setiap tanggal 1 (menghapus file bulan-bulan sebelumnya tanpa menghapus data gaji)
    public function cleanupMonthlyTeacherPhotos($force = false, $cutoffDate = null)
    {
        $currentYm = date('Y-m');
        if (!$force) {
            try {
                $stmtChk = $this->db->prepare("SELECT setting_value FROM app_settings WHERE setting_key = 'last_teacher_photo_cleanup_ym' LIMIT 1");
                $stmtChk->execute();
                $lastYm = $stmtChk->fetchColumn();
                if ($lastYm === $currentYm) {
                    return 0;
                }
            } catch (PDOException $e) {
                return 0;
            }
        }

        $firstOfMonth = $cutoffDate ?: date('Y-m-01');
        $stmt = $this->db->prepare("
            SELECT id, photo_proof
            FROM teacher_attendances
            WHERE date < :cutoff
              AND photo_proof NOT IN ('tidak_absen', 'confirmed_admin', 'expired_monthly', '')
        ");
        $stmt->execute([':cutoff' => $firstOfMonth]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $uploadDir = __DIR__ . '/../public/uploads/absen_guru/';
        $cleaned = 0;

        if (!empty($rows)) {
            $stmtUpd = $this->db->prepare("UPDATE teacher_attendances SET photo_proof = 'expired_monthly' WHERE id = ?");
            foreach ($rows as $r) {
                $safeFile = basename($r['photo_proof']);
                $fullPath = $uploadDir . $safeFile;
                if ($safeFile !== '' && is_file($fullPath)) {
                    @unlink($fullPath);
                }
                $stmtUpd->execute([(int)$r['id']]);
                $cleaned++;
            }
        }

        if ($cutoffDate === null) {
            try {
                $stmtSet = $this->db->prepare("
                    INSERT INTO app_settings (setting_key, setting_value)
                    VALUES ('last_teacher_photo_cleanup_ym', :ym1)
                    ON DUPLICATE KEY UPDATE setting_value = :ym2
                ");
                $stmtSet->execute([':ym1' => $currentYm, ':ym2' => $currentYm]);
            } catch (PDOException $e) {
                // Ignore if table unavailable
            }
        }

        return $cleaned;
    }

    public function autoProcessAlpha()
    {
        $today = new DateTime();
        $today->setTime(23, 59, 59);
        $current_time = date('H:i:s');
        $current_date_str = date('Y-m-d');

        $start_date_limit = new DateTime('-14 days');
        $start_date_limit->setTime(0, 0, 0);
        $start_date_str = $start_date_limit->format('Y-m-d');

        $stmtAtt = $this->db->prepare("SELECT schedule_id, student_id, date FROM attendances WHERE date >= ?");
        $stmtAtt->execute([$start_date_str]);
        $existing = [];
        while ($row = $stmtAtt->fetch(PDO::FETCH_ASSOC)) {
            $key = $row['student_id'] . '_' . $row['schedule_id'] . '_' . $row['date'];
            $existing[$key] = true;
        }

        $query = "SELECT cm.id as schedule_id, cm.student_id, cm.day, cm.start_time, cm.end_time, cm.joined_at 
                  FROM class_members cm";
        $stmt = $this->db->query($query);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $days_map = [
            'Senin' => 'Monday',
            'Selasa' => 'Tuesday',
            'Rabu' => 'Wednesday',
            'Kamis' => 'Thursday',
            'Jumat' => 'Friday',
            'Sabtu' => 'Saturday',
            'Minggu' => 'Sunday'
        ];

        $inserts = [];
        foreach ($members as $m) {
            $joined_date = new DateTime(date('Y-m-d', strtotime($m['joined_at'])));
            $target_day_name = $days_map[$m['day']] ?? null;
            if (!$target_day_name) continue;

            $start = clone $joined_date;
            if ($start < $start_date_limit) {
                $start = clone $start_date_limit;
            }
            $start->setTime(0, 0, 0);

            while ($start <= $today) {
                if ($start->format('l') === $target_day_name) {
                    $date_str = $start->format('Y-m-d');

                    if ($date_str === $current_date_str && $current_time < $m['end_time']) {
                        $start->modify('+1 day');
                        continue;
                    }

                    $key = $m['student_id'] . '_' . $m['schedule_id'] . '_' . $date_str;
                    if (!isset($existing[$key])) {
                        $inserts[] = [
                            'schedule_id' => $m['schedule_id'],
                            'student_id' => $m['student_id'],
                            'date' => $date_str
                        ];
                    }
                }
                $start->modify('+1 day');
            }
        }

        if (!empty($inserts)) {
            $this->db->beginTransaction();
            $stmtInsert = $this->db->prepare("INSERT INTO attendances (schedule_id, student_id, date, status, location_lat, location_long) VALUES (?, ?, ?, 'Ditolak', '0', '0')");
            foreach ($inserts as $ins) {
                $stmtInsert->execute([$ins['schedule_id'], $ins['student_id'], $ins['date']]);
            }
            $this->db->commit();
        }
    }

    public function autoProcessTeacherAlpha()
    {
        $today = new DateTime();
        $today->setTime(23, 59, 59);
        $current_time = date('H:i:s');
        $current_date_str = date('Y-m-d');

        $start_date_limit = new DateTime('-14 days');
        $start_date_limit->setTime(0, 0, 0);
        $start_date_str = $start_date_limit->format('Y-m-d');

        $stmtAtt = $this->db->prepare("SELECT schedule_id, date FROM teacher_attendances WHERE date >= ?");
        $stmtAtt->execute([$start_date_str]);
        $existing = [];
        while ($row = $stmtAtt->fetch(PDO::FETCH_ASSOC)) {
            $key = $row['schedule_id'] . '_' . $row['date'];
            $existing[$key] = true;
        }

        // Kelompokkan sesi Band menjadi 1 jadwal guru agar tidak terjadi duplikasi alpha per anggota band
        $query = "SELECT MIN(cm.id) as schedule_id,
                         COALESCE(cb.teacher_id, c.teacher_id) as teacher_id,
                         cm.day, cm.start_time, cm.end_time,
                         MIN(cm.joined_at) as joined_at 
                  FROM class_members cm
                  JOIN classes c ON cm.class_id = c.id
                  LEFT JOIN class_bands cb ON cm.band_id = cb.id
                  GROUP BY CASE WHEN cm.band_id IS NOT NULL THEN CONCAT('band_', cm.band_id) ELSE CONCAT('cm_', cm.id) END";
        $stmt = $this->db->query($query);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $days_map = [
            'Senin' => 'Monday',
            'Selasa' => 'Tuesday',
            'Rabu' => 'Wednesday',
            'Kamis' => 'Thursday',
            'Jumat' => 'Friday',
            'Sabtu' => 'Saturday',
            'Minggu' => 'Sunday'
        ];

        $inserts = [];
        foreach ($schedules as $s) {
            $joined_date = new DateTime(date('Y-m-d', strtotime($s['joined_at'])));
            $target_day_name = $days_map[$s['day']] ?? null;
            if (!$target_day_name) continue;

            $start = clone $joined_date;
            if ($start < $start_date_limit) {
                $start = clone $start_date_limit;
            }
            $start->setTime(0, 0, 0);

            $startSecs = strtotime($s['start_time']);
            $endSecs = strtotime($s['end_time']);
            $durationInHours = ($endSecs - $startSecs) / 3600;
            if ($durationInHours < 0.1) {
                $durationInHours = 1;
            }
            $classSalary = $durationInHours * (float)$this->getConfig('salary_per_hour', 30000);

            while ($start <= $today) {
                if ($start->format('l') === $target_day_name) {
                    $date_str = $start->format('Y-m-d');

                    if ($date_str === $current_date_str && $current_time < $s['end_time']) {
                        $start->modify('+1 day');
                        continue;
                    }

                    $key = $s['schedule_id'] . '_' . $date_str;
                    if (!isset($existing[$key])) {
                        $inserts[] = [
                            'teacher_id' => $s['teacher_id'],
                            'schedule_id' => $s['schedule_id'],
                            'date' => $date_str,
                            'penalty' => $classSalary
                        ];
                    }
                }
                $start->modify('+1 day');
            }
        }

        if (!empty($inserts)) {
            $this->db->beginTransaction();
            $stmtInsert = $this->db->prepare("
                INSERT INTO teacher_attendances 
                (teacher_id, schedule_id, date, check_in_time, photo_proof, latitude, longitude, base_salary, penalty_amount, total_salary) 
                VALUES (?, ?, ?, '00:00:00', 'tidak_absen', '0', '0', 0.00, ?, ?)
            ");
            foreach ($inserts as $ins) {
                $stmtInsert->execute([
                    $ins['teacher_id'],
                    $ins['schedule_id'],
                    $ins['date'],
                    $ins['penalty'],
                    -$ins['penalty']
                ]);
            }
            $this->db->commit();
        }
    }
}
