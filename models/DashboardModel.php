<?php
class DashboardModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // 1. STATISTIK RINGKAS (KARTU ATAS)
    public function getCounts() {
        return [
            'siswa' => $this->db->query("SELECT COUNT(*) FROM users WHERE role='siswa'")->fetchColumn(),
            'guru'  => $this->db->query("SELECT COUNT(*) FROM users WHERE role='guru'")->fetchColumn(),
            'kelas' => $this->db->query("SELECT COUNT(*) FROM classes")->fetchColumn(),
            'pending_payment' => $this->db->query("SELECT COUNT(*) FROM payments WHERE status='Belum Lunas'")->fetchColumn()
        ];
    }

    // 2. DATA GRAFIK PEMASUKAN (AREA CHART)
    public function getIncomeChart($year) {
        // Query: Kelompokkan berdasarkan bulan, jumlahkan nominalnya
        $query = "SELECT month, SUM(amount) as total 
                  FROM payments 
                  WHERE year = ? AND status = 'Lunas' 
                  GROUP BY month ORDER BY month ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$year]);
        $result = $stmt->fetchAll();

        // Format data biar gampang dipakai Chart.js (Array 1-12 bulan)
        $data = array_fill(1, 12, 0); // Default 0 semua bulan
        foreach ($result as $row) {
            $data[$row['month']] = $row['total'];
        }
        return array_values($data); // Reset keys jadi 0-11
    }

    // 3. DATA GRAFIK ABSENSI (PIE CHART)
    public function getAttendancePie() {
        // Hitung jumlah masing-masing status
        $query = "SELECT status, COUNT(*) as total FROM attendances GROUP BY status";
        $stmt = $this->db->query($query);
        $result = $stmt->fetchAll();

        // Format data: [Hadir, Izin, Sakit, Alpha]
        $stats = ['Hadir' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alpha' => 0];
        foreach ($result as $row) {
            // Mapping status database ke kategori umum
            if ($row['status'] == 'Hadir') $stats['Hadir'] = $row['total'];
            elseif ($row['status'] == 'Izin') $stats['Izin'] = $row['total'];
            elseif ($row['status'] == 'Sakit') $stats['Sakit'] = $row['total'];
            elseif ($row['status'] == 'Ditolak') $stats['Alpha'] = $row['total']; // Ditolak kita anggap Alpha
        }
        return array_values($stats);
    }
}
?>