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
        // Ambil absensi berdasarkan kelas, bulan, dan tahun
        $query = "SELECT attendances.*, users.name as student_name
                  FROM attendances
                  JOIN users ON attendances.student_id = users.id
                  JOIN schedules ON attendances.schedule_id = schedules.id
                  WHERE schedules.class_id = ? 
                  AND MONTH(attendances.date) = ? 
                  AND YEAR(attendances.date) = ?
                  ORDER BY attendances.date ASC, users.name ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$class_id, $bulan, $tahun]);
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