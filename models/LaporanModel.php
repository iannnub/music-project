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
        // KOREKSI: Kita JOIN ke class_members (cm), karena di sana class_id tersimpan
        $query = "SELECT a.*, u.name as student_name
                  FROM attendances a
                  JOIN users u ON a.student_id = u.id
                  JOIN class_members cm ON a.schedule_id = cm.id
                  WHERE cm.class_id = ? 
                  AND MONTH(a.date) = ? 
                  AND YEAR(a.date) = ?
                  ORDER BY a.date ASC, u.name ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$class_id, (int)$bulan, $tahun]); // Cast ke int buat jaga-jaga leading zero
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