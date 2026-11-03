<?php
class PembayaranModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Ambil Semua Data Pembayaran (Join ke Siswa & Admin + Ambil Nomor HP & Cabang)
    public function getAll() {
        $query = "SELECT payments.*, 
                         siswa.name as student_name, 
                         siswa.username as student_nis,
                         siswa.phone as student_phone,
                         siswa.branch as student_branch,
                         admin.name as admin_name
                  FROM payments
                  JOIN users as siswa ON payments.student_id = siswa.id
                  LEFT JOIN users as admin ON payments.admin_id = admin.id
                  ORDER BY payments.year DESC, payments.month DESC, payments.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Input Pembayaran Baru
    public function create($data) {
        try {
            $query = "INSERT INTO payments (student_id, admin_id, month, year, start_date, end_date, amount, status, notes, payment_date) 
                      VALUES (:sid, :aid, :m, :y, :s_date, :e_date, :amt, :stat, :notes, :pdate)";
            
            $stmt = $this->db->prepare($query);
            $pdate = ($data['status'] === 'Lunas') ? date('Y-m-d H:i:s') : null;
            return $stmt->execute([
                ':sid'    => $data['student_id'],
                ':aid'    => !empty($data['admin_id']) ? $data['admin_id'] : null,
                ':m'      => $data['month'],
                ':y'      => $data['year'],
                ':s_date' => $data['start_date'],
                ':e_date' => $data['end_date'],
                ':amt'    => $data['amount'],
                ':stat'   => $data['status'],
                ':notes'  => $data['notes'],
                ':pdate'  => $pdate
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Ambil Semua Riwayat Pembayaran khusus 1 Siswa
    public function getAllByStudent($student_id) {
        $query = "SELECT payments.*, 
                         siswa.name as student_name, 
                         siswa.phone as student_phone,
                         siswa.branch as student_branch,
                         siswa.spp_rate as student_spp_rate,
                         admin.name as admin_name
                  FROM payments
                  JOIN users as siswa ON payments.student_id = siswa.id
                  LEFT JOIN users as admin ON payments.admin_id = admin.id
                  WHERE payments.student_id = ?
                  ORDER BY payments.year DESC, payments.month DESC, payments.id DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteAllByStudent($student_id) {
        try {
            $query = "DELETE FROM payments WHERE student_id = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$student_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // LEFT JOIN agar siswa baru yang mendaftar mandiri (belum masuk kelas) langsung terlihat tagihan perdananya
    public function getAllGrouped() {
        $query = "SELECT 
                    u.id AS student_id, 
                    u.name AS nama_siswa,
                    u.branch AS branch,
                    u.spp_rate AS spp_rate,
                    u.program AS program,
                    u.instrument AS instrument,
                    COALESCE(
                        GROUP_CONCAT(DISTINCT CASE WHEN cb.name IS NOT NULL THEN CONCAT(c.name, ' - ', cb.name) ELSE c.name END SEPARATOR ', '),
                        CASE WHEN u.instrument IS NOT NULL THEN CONCAT('Pendaftar Baru (', u.instrument, ')') ELSE 'Belum Plotting Kelas' END
                    ) AS nama_kelas,
                    (SELECT CONCAT(DATE_FORMAT(p2.start_date, '%d/%m/%y'), ' - ', DATE_FORMAT(p2.end_date, '%d/%m/%y'))
                     FROM payments p2
                     WHERE p2.student_id = u.id
                     ORDER BY p2.year DESC, p2.month DESC, p2.id DESC
                     LIMIT 1) AS periode_terakhir,
                    CASE 
                        WHEN COUNT(DISTINCT CASE WHEN p.status = 'Belum Lunas' THEN p.id END) > 0 THEN 'Ada Tunggakan'
                        WHEN COUNT(DISTINCT p.id) = 0 THEN 'Belum Ada Tagihan'
                        ELSE 'Lunas'
                    END AS status_global
                FROM users u
                LEFT JOIN class_members cm ON u.id = cm.student_id
                LEFT JOIN classes c ON cm.class_id = c.id
                LEFT JOIN class_bands cb ON cm.band_id = cb.id
                LEFT JOIN payments p ON u.id = p.student_id
                WHERE u.role = 'siswa'
                GROUP BY u.id
                ORDER BY 
                    CASE 
                        WHEN COUNT(DISTINCT CASE WHEN p.status = 'Belum Lunas' THEN p.id END) > 0 THEN 1
                        WHEN COUNT(DISTINCT p.id) = 0 THEN 2
                        ELSE 3
                    END ASC,
                    u.name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update Pembayaran
    public function update($id, $data) {
        try {
            $pdateSql = ($data['status'] === 'Lunas') ? ", payment_date = COALESCE(payment_date, NOW())" : "";
            $query = "UPDATE payments 
                      SET month=:m, year=:y, start_date=:s_date, end_date=:e_date, amount=:amt, status=:stat, notes=:notes{$pdateSql}
                      WHERE id=:id";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':m'      => $data['month'],
                ':y'      => $data['year'],
                ':s_date' => $data['start_date'],
                ':e_date' => $data['end_date'],
                ':amt'    => $data['amount'],
                ':stat'   => $data['status'],
                ':notes'  => $data['notes'],
                ':id'     => $id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Ambil 1 Data Pembayaran by ID (Untuk Cetak Kwitansi)
    public function getById($id) {
        $query = "SELECT payments.*, 
                         siswa.name as student_name, 
                         siswa.username as student_nis,
                         siswa.phone as student_phone,
                         siswa.branch as student_branch,
                         admin.name as admin_name
                  FROM payments 
                  JOIN users as siswa ON payments.student_id = siswa.id
                  LEFT JOIN users as admin ON payments.admin_id = admin.id
                  WHERE payments.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function checkStatusSiswa($student_id, $month, $year) {
        $query = "SELECT status FROM payments 
                  WHERE student_id = ? AND month = ? AND year = ? AND status = 'Lunas'";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$student_id, $month, $year]);
        return $stmt->rowCount() > 0;
    }

    public function checkExisting($student_id, $month, $year, $exclude_id = null) {
        $query = "SELECT COUNT(*) FROM payments WHERE student_id = ? AND month = ? AND year = ?";
        $params = [$student_id, (int)$month, (int)$year];
        if ($exclude_id) {
            $query .= " AND id != ?";
            $params[] = (int)$exclude_id;
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM payments WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
?>
