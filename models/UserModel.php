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
        $email = !empty($data['email']) ? $data['email'] : null;

        $result = $stmt->execute([
            ':username' => $data['username'],
            ':name'     => $data['name'],
            ':email'    => $email,
            ':password' => $data['password'],
            ':role'     => $data['role'],
            ':phone'    => $data['phone']
        ]);

        return $result ? $this->db->lastInsertId() : false;

    } catch (PDOException $e) {
        // TAMBAHKAN BARIS INI UNTUK DEBUGGING:
        die("Waduh, Error MySQL: " . $e->getMessage()); 
    }
}

    // UPDATE USER (OLEH ADMIN) - PERBAIKAN DI SINI
    public function update($id, $data) {
        try {
            $email = !empty($data['email']) ? $data['email'] : null;

            if (!empty($data['password'])) {
                $query = "UPDATE users SET username=:u, name=:n, email=:e, phone=:p, password=:pass WHERE id=:id";
                $params = [
                    ':u' => $data['username'], ':n' => $data['name'], ':e' => $email, 
                    ':p' => $data['phone'], ':pass' => $data['password'], ':id' => $id
                ];
            } else {
                $query = "UPDATE users SET username=:u, name=:n, email=:e, phone=:p WHERE id=:id";
                $params = [
                    ':u' => $data['username'], ':n' => $data['name'], ':e' => $email, 
                    ':p' => $data['phone'], ':id' => $id
                ];
            }
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }
    
   // models/UserModel.php

    public function delete($id) {
        try {
            $this->db->beginTransaction();

            // Cascade delete manual (Hapus relasi data sebelum hapus user)
            $this->db->prepare("DELETE FROM payments WHERE student_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM attendances WHERE student_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM submissions WHERE student_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM class_members WHERE student_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM progress_logs WHERE student_id = ?")->execute([$id]);

            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
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
        // 1. Standarisasi data nullable agar database tetap bersih (Data Integrity)
        $email = !empty($data['email']) ? $data['email'] : null;
        $gdrive = !empty($data['gdrive_link']) ? $data['gdrive_link'] : null;

        // 2. Gunakan query universal. 
        // Karena di Controller kita sudah menjamin $data['photo'] selalu ada (foto baru atau lama), 
        // kita tidak perlu lagi memisah query pakai if-else. Ini lebih Best Practice di SI!
        $query = "UPDATE users SET 
                    name = :n, 
                    email = :e, 
                    phone = :p, 
                    photo_profile = :f, 
                    gdrive_link = :g 
                  WHERE id = :id";

        $params = [
            ':n'  => $data['name'],
            ':e'  => $email,
            ':p'  => $data['phone'],
            ':f'  => $data['photo'], // Mengambil hasil dari Cropper/Base64 atau foto lama
            ':g'  => $gdrive,       // Kolom sakti GDrive kita
            ':id' => $id
        ];

        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);

    } catch (PDOException $e) {
        // Logging error bisa ditambahkan di sini untuk debugging yang lebih pro
        error_log("Update Profile Error: " . $e->getMessage());
        return false;
    }
}

    // Ambil jadwal spesifik yang dimiliki oleh seorang siswa
public function getStudentSchedule($student_id) {
    $query = "SELECT cm.*, c.name as class_name 
              FROM class_members cm
              JOIN classes c ON cm.class_id = c.id
              WHERE cm.student_id = :id";
    $stmt = $this->db->prepare($query);
    $stmt->execute([':id' => $student_id]);
    // Kita pake fetch() kalau satu siswa cuma boleh punya 1 jadwal aktif, 
    // atau fetchAll() kalau mereka boleh ambil banyak kelas.
    return $stmt->fetchAll(); 
}

// Update data plotting di tabel class_members
public function updateJadwalSiswa($id_member, $data) {
    $query = "UPDATE class_members SET 
                class_id = :class_id,
                day = :day,
                start_time = :start_time,
                end_time = :end_time
              WHERE id = :id_member";
    $stmt = $this->db->prepare($query);
    return $stmt->execute([
        ':class_id'   => $data['class_id'],
        ':day'        => $data['day'],
        ':start_time' => $data['start_time'],
        ':end_time'   => $data['end_time'],
        ':id_member'  => $id_member
    ]);
}

    public function changePassword($id, $old_pass, $new_pass) {
        $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user && password_verify($old_pass, $user['password'])) {
            $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $upd = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            return $upd->execute([$new_hash, $id]);
        }
        return false;
    }
}
?>