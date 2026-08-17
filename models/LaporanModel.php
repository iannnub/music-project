<?php
class LaporanModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // 1. REKAP KEUANGAN BULANAN
    public function getLaporanKeuangan($bulan, $tahun) {
        // Ambil data pembayaran yang LUNAS saja
        $query = "SELECT payments.*, users.name as student_name 
                  FROM payments 
                  JOIN users ON payments.student_id = users.id
                  WHERE payments.month = ? 
                  AND payments.year = ? 
                  AND payments.status = 'Lunas'
                  ORDER BY payments.created_at ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$bulan, $tahun]);
        return $stmt->fetchAll();
    }

    // 2. REKAP ABSENSI PER KELAS
    public function getLaporanAbsensi($class_id, $bulan, $tahun) {
    $query = "SELECT a.*, u.name as student_name, teacher.name as teacher_name
              FROM attendances a
              JOIN users u ON a.student_id = u.id
              JOIN class_members cm ON a.schedule_id = cm.id
              JOIN classes c ON cm.class_id = c.id
              JOIN users teacher ON c.teacher_id = teacher.id
              WHERE cm.class_id = ? 
              AND MONTH(a.date) = ? 
              AND YEAR(a.date) = ?
              ORDER BY a.date ASC, u.name ASC";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$class_id, (int)$bulan, $tahun]);
            return $stmt->fetchAll();
        }

    public function getLaporanAbsensiGuru($teacher_id = null, $bulan = null, $tahun = null, $tanggal = null) {
        $query = "SELECT 
                    ta.*, 
                    c.name as class_name, 
                    u.name as student_name,
                    teacher.name as teacher_name,
                    cm.start_time,
                    cm.end_time
                  FROM teacher_attendances ta
                  JOIN class_members cm ON ta.schedule_id = cm.id
                  JOIN classes c ON cm.class_id = c.id
                  JOIN users u ON cm.student_id = u.id
                  JOIN users teacher ON ta.teacher_id = teacher.id
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($teacher_id)) {
            $query .= " AND ta.teacher_id = ?";
            $params[] = $teacher_id;
        }
        
        if (!empty($tanggal)) {
            $query .= " AND ta.date = ?";
            $params[] = $tanggal;
        } else {
            if (!empty($bulan)) {
                $query .= " AND MONTH(ta.date) = ?";
                $params[] = (int)$bulan;
            }
            if (!empty($tahun)) {
                $query .= " AND YEAR(ta.date) = ?";
                $params[] = (int)$tahun;
            }
        }
        
        $query .= " ORDER BY ta.date ASC, cm.start_time ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Helper: Ambil Nama Kelas (untuk Judul Laporan)
    public function getNamaKelas($class_id) {
        $stmt = $this->db->prepare("SELECT name FROM classes WHERE id = ?");
        $stmt->execute([$class_id]);
        $res = $stmt->fetch();
        return $res ? $res['name'] : '-';
    }
}
?>