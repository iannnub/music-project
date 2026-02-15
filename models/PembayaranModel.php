<?php
class PembayaranModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Ambil Semua Data Pembayaran (Join ke Siswa & Admin + Ambil Nomor HP)
    public function getAll() {
        $query = "SELECT payments.*, 
                         siswa.name as student_name, 
                         siswa.username as student_nis,
                         siswa.phone as student_phone, 
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
            return $stmt->execute([
                ':sid'    => $data['student_id'],
                ':aid'    => $data['admin_id'],
                ':m'      => $data['month'],
                ':y'      => $data['year'],
                ':s_date' => $data['start_date'],
                ':e_date' => $data['end_date'],
                ':amt'    => $data['amount'],
                ':stat'   => $data['status'],
                ':notes'  => $data['notes'],
                ':pdate'  => date('Y-m-d H:i:s')
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
                     admin.name as admin_name
              FROM payments
              JOIN users as siswa ON payments.student_id = siswa.id
              LEFT JOIN users as admin ON payments.admin_id = admin.id
              WHERE payments.student_id = ?
              ORDER BY payments.year DESC, payments.month DESC";
    
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

    public function getAllGrouped() {
        $query = "SELECT 
                    u.id AS student_id, 
                    u.name AS nama_siswa, 
                    c.name AS nama_kelas,
                    -- Mengambil range tanggal terakhir sebagai info periode
                    MAX(CONCAT(DATE_FORMAT(p.start_date, '%d/%m/%y'), ' - ', DATE_FORMAT(p.end_date, '%d/%m/%y'))) AS periode_terakhir,
                    -- Status Global Siswa
                    CASE 
                        WHEN COUNT(CASE WHEN p.status = 'Belum Lunas' THEN 1 END) > 0 THEN 'Ada Tunggakan'
                        WHEN COUNT(p.id) = 0 THEN 'Belum Ada Tagihan'
                        ELSE 'Lunas'
                    END AS status_global
                FROM users u
                JOIN class_members cm ON u.id = cm.student_id
                JOIN classes c ON cm.class_id = c.id
                LEFT JOIN payments p ON u.id = p.student_id
                WHERE u.role = 'siswa'
                GROUP BY u.id, c.name
                ORDER BY u.name ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update Pembayaran
    public function update($id, $data) {
        try {
            // Update juga start_date dan end_date-nya
            $query = "UPDATE payments 
                      SET month=:m, year=:y, start_date=:s_date, end_date=:e_date, amount=:amt, status=:stat, notes=:notes 
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
    // Mengecek apakah sudah ada record pembayaran yang LUNAS untuk bulan & tahun ini
    $query = "SELECT status FROM payments 
              WHERE student_id = ? AND month = ? AND year = ? AND status = 'Lunas'";
    $stmt = $this->db->prepare($query);
    $stmt->execute([$student_id, $month, $year]);
    return $stmt->rowCount() > 0; // Mengembalikan true jika lunas
}

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM payments WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
?>