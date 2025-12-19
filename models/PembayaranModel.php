<?php
class PembayaranModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Ambil Semua Data Pembayaran (Join ke Siswa & Admin + Ambil Nomor HP)
    public function getAll() {
        // Kita tambahin siswa.phone supaya bisa kirim WA nanti
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
            // Tambahin start_date dan end_date ke query
            $query = "INSERT INTO payments (student_id, admin_id, month, year, start_date, end_date, amount, status, notes, payment_date) 
                      VALUES (:sid, :aid, :m, :y, :s_date, :e_date, :amt, :stat, :notes, :pdate)";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':sid'    => $data['student_id'],
                ':aid'    => $data['admin_id'],
                ':m'      => $data['month'],
                ':y'      => $data['year'],
                ':s_date' => $data['start_date'], // Kolom baru
                ':e_date' => $data['end_date'],   // Kolom baru
                ':amt'    => $data['amount'],
                ':stat'   => $data['status'],
                ':notes'  => $data['notes'],
                ':pdate'  => date('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            return false;
        }
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

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM payments WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
?>