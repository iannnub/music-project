<?php
class AbsensiModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Cek apakah siswa sudah absen hari ini di jadwal ini? (Prevent Double Absen)
    public function cekSudahAbsen($student_id, $schedule_id, $date)
    {
        $stmt = $this->db->prepare("SELECT id FROM attendances 
                                    WHERE student_id = ? AND schedule_id = ? AND date = ?");
        $stmt->execute([$student_id, $schedule_id, $date]);
        return $stmt->rowCount() > 0;
    }

    // Simpan Data Absensi Baru
    public function create($data)
    {
        $query = "INSERT INTO attendances (schedule_id, student_id, date, status, photo_proof, location_lat, location_long) 
                  VALUES (:sid, :stud_id, :date, 'Hadir', :photo, :lat, :long)";

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

    public function getPendingAbsensiByGuru($teacher_id)
    {
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

    public function getStudentAttendanceSummary($teacher_id)
    {
        $query = "SELECT 
                u.id as student_id, 
                u.name as student_name, 
                u.parent_name,
                u.photo_profile,
                -- MAGIC LINE: Gabungin nama kelas biar gak duplikat baris
                GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as class_names,
                COUNT(CASE WHEN a.status = 'Hadir' THEN 1 END) as total_hadir,
                COUNT(CASE WHEN a.status IN ('Izin', 'Sakit') THEN 1 END) as total_izin,
                COUNT(CASE WHEN a.status = 'Ditolak' THEN 1 END) as total_alpha
              FROM users u
              JOIN class_members cm ON u.id = cm.student_id
              JOIN classes c ON cm.class_id = c.id
              LEFT JOIN attendances a ON cm.id = a.schedule_id AND a.status != 'Menunggu'
              WHERE c.teacher_id = :tid
              GROUP BY u.id -- KUNCINYA DI SINI: Cukup Group by Student ID
              ORDER BY u.name ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':tid' => $teacher_id]);
        return $stmt->fetchAll();
    }

    public function getAttendanceDetailByStudent($student_id, $teacher_id)
    {
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
    public function getAbsensiByGuru($teacher_id)
    {
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
    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("UPDATE attendances SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function submitTeacherAttendance($data)
    {
        // 1. Ambil Hari Ini dalam Bahasa Indonesia
        $hari_ini = date('l');
        $daftar_hari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        $hari_indo = $daftar_hari[$hari_ini];

        // 2. CARI JADWAL GURU HARI INI DI DATABASE
        // Cek apakah guru punya jadwal hari ini
        $queryCekJadwal = "SELECT COUNT(*) FROM class_members cm JOIN classes c ON cm.class_id = c.id WHERE c.teacher_id = :tid AND cm.day = :day";
        $stmtCek = $this->db->prepare($queryCekJadwal);
        $stmtCek->execute([':tid' => $data['teacher_id'], ':day' => $hari_indo]);
        $has_schedule_today = $stmtCek->fetchColumn() > 0;

        if (!$has_schedule_today) {
            return "tidak_ada_jadwal";
        }

        $current_time = $data['check_in_time'];
        $req_schedule_id = isset($data['schedule_id']) ? (int)$data['schedule_id'] : 0;

        if ($req_schedule_id > 0) {
            // Find specific schedule_id and verify it is active now
            $queryJadwal = "SELECT cm.id as schedule_id, cm.start_time, cm.end_time 
                            FROM class_members cm
                            JOIN classes c ON cm.class_id = c.id
                            WHERE cm.id = :sid AND c.teacher_id = :tid AND cm.day = :day
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
            $jadwal = $stmtJadwal->fetch();
        } else {
            // Fallback: search by time window
            $queryJadwal = "SELECT cm.id as schedule_id, cm.start_time, cm.end_time 
                            FROM class_members cm
                            JOIN classes c ON cm.class_id = c.id
                            WHERE c.teacher_id = :tid AND cm.day = :day
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
            $jadwal = $stmtJadwal->fetch();
        }

        if (!$jadwal) {
            // Cek apakah ada jadwal di hari ini tapi belum masuk jam absen
            if ($req_schedule_id > 0) {
                // specific check for the requested schedule_id
                $queryFuture = "SELECT COUNT(*) FROM class_members cm 
                                JOIN classes c ON cm.class_id = c.id 
                                WHERE cm.id = :sid AND c.teacher_id = :tid AND cm.day = :day 
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
                                WHERE c.teacher_id = :tid AND cm.day = :day 
                                AND SUBTIME(cm.start_time, '00:05:00') > :current_time";
                $stmtFuture = $this->db->prepare($queryFuture);
                $stmtFuture->execute([
                    ':tid' => $data['teacher_id'],
                    ':day' => $hari_indo,
                    ':current_time' => $current_time
                ]);
            }
            $has_future = $stmtFuture->fetchColumn() > 0;
            if ($has_future) {
                return "absen_belum_dibuka";
            }
            return "absen_ditutup";
        }

        // Cek apakah guru sudah absen untuk kelas/jadwal ini pada hari ini (Prevent Double Absen)
        $queryCekAbsen = "SELECT COUNT(*) FROM teacher_attendances WHERE teacher_id = :tid AND schedule_id = :sid AND date = :date";
        $stmtCekAbsen = $this->db->prepare($queryCekAbsen);
        $stmtCekAbsen->execute([
            ':tid' => $data['teacher_id'],
            ':sid' => $jadwal['schedule_id'],
            ':date' => $data['date']
        ]);
        if ($stmtCekAbsen->fetchColumn() > 0) {
            return "sudah_absen";
        }

        $startTime = $jadwal['start_time'];
        $checkIn = $data['check_in_time'];

        // HITUNG GAJI POKOK DARI DURASI MENGAJAR (Rp 30.000 per 1 jam)
        $startSecs = strtotime($jadwal['start_time']);
        $endSecs = strtotime($jadwal['end_time']);
        $durationInHours = ($endSecs - $startSecs) / 3600;
        if ($durationInHours < 0.1) {
            $durationInHours = 1;
        }
        $baseSalary = $durationInHours * 30000;

        // 3. LOGIKA RADIUS (GEOFENCING)
        $latKantor = -8.28483445437825;
        $lngKantor = 113.52589125398372;
        $radiusMaksimal = 0.1; // 100 meter

        $earthRadius = 6371;
        $dLat = deg2rad($data['latitude'] - $latKantor);
        $dLon = deg2rad($data['longitude'] - $lngKantor);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($latKantor)) * cos(deg2rad($data['latitude'])) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $jarak = $earthRadius * $c;

        if ($jarak > $radiusMaksimal) {
            return "diluar_radius";
        }

        // 4. HITUNG DENDA SESUAI JADWAL DINAMIS
        $penalty = 0;
        if (strtotime($checkIn) > strtotime($startTime)) {
            $diffInSeconds = strtotime($checkIn) - strtotime($startTime);
            $diffInMinutes = floor($diffInSeconds / 60);
            $penalty = floor($diffInMinutes / 10) * 5000;
        }

        $totalSalary = $baseSalary - $penalty;

        // 5. SIMPAN KE DATABASE
        $query = "INSERT INTO teacher_attendances 
                  (teacher_id, schedule_id, date, check_in_time, photo_proof, latitude, longitude, base_salary, penalty_amount, total_salary) 
                  VALUES (:tid, :sid, :d, :ct, :pp, :lat, :lng, :bs, :pa, :ts)";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':tid' => $data['teacher_id'],
            ':sid' => $jadwal['schedule_id'],
            ':d'   => $data['date'],
            ':ct'  => $checkIn,
            ':pp'  => $data['photo'],
            ':lat' => $data['latitude'],
            ':lng' => $data['longitude'],
            ':bs'  => $baseSalary,
            ':pa'  => $penalty,
            ':ts'  => $totalSalary
        ]);
    }

    public function autoProcessAlpha()
    {
        // 1. Get the current date and time
        $today = new DateTime();
        $today->setTime(23, 59, 59); // Timezone-safe date comparison
        $current_time = date('H:i:s');
        $current_date_str = date('Y-m-d');

        // We only check the last 14 days to keep it extremely fast
        $start_date_limit = new DateTime('-14 days');
        $start_date_limit->setTime(0, 0, 0);
        $start_date_str = $start_date_limit->format('Y-m-d');

        // 2. Fetch all existing attendances in the last 14 days into a fast lookup array
        $stmtAtt = $this->db->prepare("SELECT schedule_id, student_id, date FROM attendances WHERE date >= ?");
        $stmtAtt->execute([$start_date_str]);
        $existing = [];
        while ($row = $stmtAtt->fetch(PDO::FETCH_ASSOC)) {
            $key = $row['student_id'] . '_' . $row['schedule_id'] . '_' . $row['date'];
            $existing[$key] = true;
        }

        // 3. Fetch all class_members
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

            // Loop from max(joined_date, start_date_limit) to today
            $start = clone $joined_date;
            if ($start < $start_date_limit) {
                $start = clone $start_date_limit;
            }
            $start->setTime(0, 0, 0);

            while ($start <= $today) {
                if ($start->format('l') === $target_day_name) {
                    $date_str = $start->format('Y-m-d');

                    // If it is today, check if class has ended
                    if ($date_str === $current_date_str) {
                        if ($current_time < $m['end_time']) {
                            $start->modify('+1 day');
                            continue;
                        }
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

        // 4. Batch insert all missing attendances
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
        $today->setTime(23, 59, 59); // Timezone-safe date comparison
        $current_time = date('H:i:s');
        $current_date_str = date('Y-m-d');

        // We only check the last 14 days
        $start_date_limit = new DateTime('-14 days');
        $start_date_limit->setTime(0, 0, 0);
        $start_date_str = $start_date_limit->format('Y-m-d');

        // Fetch all existing teacher attendances in the last 14 days
        $stmtAtt = $this->db->prepare("SELECT schedule_id, date FROM teacher_attendances WHERE date >= ?");
        $stmtAtt->execute([$start_date_str]);
        $existing = [];
        while ($row = $stmtAtt->fetch(PDO::FETCH_ASSOC)) {
            $key = $row['schedule_id'] . '_' . $row['date'];
            $existing[$key] = true;
        }

        // Fetch all teacher schedules from class_members (individually per student, as attendance is per student)
        $query = "SELECT cm.id as schedule_id, c.teacher_id, cm.day, cm.start_time, cm.end_time, cm.joined_at 
                  FROM class_members cm
                  JOIN classes c ON cm.class_id = c.id";
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

            // Hitung denda & gaji session berdasarkan durasi mengajar (Rp 30.000 per 1 jam)
            $startSecs = strtotime($s['start_time']);
            $endSecs = strtotime($s['end_time']);
            $durationInHours = ($endSecs - $startSecs) / 3600;
            if ($durationInHours < 0.1) {
                $durationInHours = 1;
            }
            $classSalary = $durationInHours * 30000;

            while ($start <= $today) {
                if ($start->format('l') === $target_day_name) {
                    $date_str = $start->format('Y-m-d');

                    // If it is today, only mark as absent if class has ended
                    if ($date_str === $current_date_str) {
                        if ($current_time < $s['end_time']) {
                            $start->modify('+1 day');
                            continue;
                        }
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

        // Insert missing teacher attendances as absent (Alpha / Tidak Absen)
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
