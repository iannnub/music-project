<?php
class UserModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // --- 1. FITUR ADMIN: MANAJEMEN USER ---

    public function getAllByRole($role) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE role = :role ORDER BY created_at DESC");
        $stmt->execute([':role' => $role]);
        return $stmt->fetchAll();
    }

    public function countByRole($role) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users WHERE role = :role");
        $stmt->execute([':role' => $role]);
        $result = $stmt->fetch();
        return $result['total'];
    }

    public function create($data) {
        try {
            $query = "INSERT INTO users (username, name, email, password, role, phone) 
                      VALUES (:username, :name, :email, :password, :role, :phone)";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':username' => $data['username'],
                ':name'     => $data['name'],
                ':email'    => $data['email'],
                ':password' => $data['password'],
                ':role'     => $data['role'],
                ':phone'    => $data['phone']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // UPDATE USER (OLEH ADMIN) - PERBAIKAN DI SINI
    public function update($id, $data) {
        try {
            if (!empty($data['password'])) {
                // Update dengan Password
                $query = "UPDATE users SET username=:u, name=:n, email=:e, phone=:p, password=:pass WHERE id=:id";
                $params = [
                    ':u' => $data['username'],
                    ':n' => $data['name'],
                    ':e' => $data['email'],
                    ':p' => $data['phone'],
                    ':pass' => $data['password'], 
                    ':id' => $id
                ];
            } else {
                // Update TANPA Password
                $query = "UPDATE users SET username=:u, name=:n, email=:e, phone=:p WHERE id=:id";
                $params = [
                    ':u' => $data['username'],
                    ':n' => $data['name'],
                    ':e' => $data['email'],
                    ':p' => $data['phone'],
                    ':id' => $id
                ];
            }
            // Eksekusi di luar if-else aman karena query & params pasti terisi salah satunya
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }
    
   // models/UserModel.php

    public function delete($id) {
        try {
            $this->db->beginTransaction(); // Mulai Transaksi biar aman

            // 1. Hapus data di tabel pembayaran
            $this->db->prepare("DELETE FROM payments WHERE student_id = ?")->execute([$id]);

            // 2. Hapus data di tabel absensi
            $this->db->prepare("DELETE FROM attendances WHERE student_id = ?")->execute([$id]);

            // 3. Hapus data di tabel pengumpulan tugas
            $this->db->prepare("DELETE FROM submissions WHERE student_id = ?")->execute([$id]);

            // 4. Hapus data di tabel anggota kelas
            $this->db->prepare("DELETE FROM class_members WHERE student_id = ?")->execute([$id]);

            // 5. Hapus data di tabel progress logs
            $this->db->prepare("DELETE FROM progress_logs WHERE student_id = ?")->execute([$id]);

            // 6. TERAKHIR: Hapus User Siswa-nya
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit(); // Simpan perubahan
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack(); // Batalkan jika ada error
            // Uncomment baris bawah ini untuk melihat pesan errornya di layar (Debugging)
            // die("Gagal hapus: " . $e->getMessage()); 
            return false;
        }
    }

    // --- 2. FITUR PROFILE USER (Update Diri Sendiri) ---

    // AMBIL DATA USER BY ID (PENTING BUAT REFRESH SESSION)
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // UPDATE PROFILE SENDIRI
    public function updateProfile($id, $data) {
        try {
            if (!empty($data['photo'])) {
                $query = "UPDATE users SET name=:n, email=:e, phone=:p, photo_profile=:f WHERE id=:id";
                $params = [
                    ':n' => $data['name'], 
                    ':e' => $data['email'], 
                    ':p' => $data['phone'], 
                    ':f' => $data['photo'], 
                    ':id' => $id
                ];
            } else {
                $query = "UPDATE users SET name=:n, email=:e, phone=:p WHERE id=:id";
                $params = [
                    ':n' => $data['name'], 
                    ':e' => $data['email'], 
                    ':p' => $data['phone'], 
                    ':id' => $id
                ];
            }
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function changePassword($id, $old_pass, $new_pass) {
        $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user && password_verify($old_pass, $user['password'])) {
            $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $upd = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            return $upd->execute([$new_hash, $id]);
        } else {
            return false;
        }
    }
}
?>