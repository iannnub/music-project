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

    public function usernameExists($username, $exclude_id = null) {
        $sql = "SELECT id FROM users WHERE username = :u";
        $params = [':u' => $username];
        if ($exclude_id) {
            $sql .= " AND id != :id";
            $params[':id'] = (int)$exclude_id;
        }
        $sql .= " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }

    public function create($data) {
        try {
            $query = "INSERT INTO users (
                        username, name, email, password, role, phone, parent_name,
                        branch, spp_rate, gender, school_or_work, age, program, instrument,
                        preferred_day, preferred_time, address, agreed_tatib_at
                      ) VALUES (
                        :username, :name, :email, :password, :role, :phone, :parent_name,
                        :branch, :spp_rate, :gender, :school_or_work, :age, :program, :instrument,
                        :preferred_day, :preferred_time, :address, :agreed_tatib_at
                      )";

            $stmt = $this->db->prepare($query);
            $email          = !empty($data['email']) ? $data['email'] : null;
            $parent_name    = !empty($data['parent_name']) ? $data['parent_name'] : null;
            $branch         = !empty($data['branch']) ? $data['branch'] : null;
            $spp_rate       = isset($data['spp_rate']) && $data['spp_rate'] !== '' ? (float)$data['spp_rate'] : null;
            $gender         = !empty($data['gender']) ? $data['gender'] : null;
            $school_or_work = !empty($data['school_or_work']) ? $data['school_or_work'] : null;
            $age            = isset($data['age']) && $data['age'] !== '' ? (int)$data['age'] : null;
            $program        = !empty($data['program']) ? $data['program'] : null;
            $instrument     = !empty($data['instrument']) ? $data['instrument'] : null;
            $preferred_day  = !empty($data['preferred_day']) ? $data['preferred_day'] : null;
            $preferred_time = !empty($data['preferred_time']) ? $data['preferred_time'] : null;
            $address        = !empty($data['address']) ? $data['address'] : null;
            $agreed_tatib   = !empty($data['agreed_tatib_at']) ? $data['agreed_tatib_at'] : null;

            $result = $stmt->execute([
                ':username'        => $data['username'],
                ':name'            => $data['name'],
                ':email'           => $email,
                ':password'        => $data['password'],
                ':role'            => $data['role'],
                ':phone'           => $data['phone'] ?? null,
                ':parent_name'     => $parent_name,
                ':branch'          => $branch,
                ':spp_rate'        => $spp_rate,
                ':gender'          => $gender,
                ':school_or_work'  => $school_or_work,
                ':age'             => $age,
                ':program'         => $program,
                ':instrument'      => $instrument,
                ':preferred_day'   => $preferred_day,
                ':preferred_time'  => $preferred_time,
                ':address'         => $address,
                ':agreed_tatib_at' => $agreed_tatib
            ]);

            return $result ? $this->db->lastInsertId() : false;

        } catch (PDOException $e) {
            error_log("Gagal Create User: " . $e->getMessage());
            return false;
        }
    }

    // UPDATE USER (OLEH ADMIN)
    public function update($id, $data) {
        try {
            $email       = !empty($data['email']) ? $data['email'] : null;
            $parent_name = !empty($data['parent_name']) ? $data['parent_name'] : null;
            $branch      = isset($data['branch']) ? ($data['branch'] !== '' ? $data['branch'] : null) : null;
            $spp_rate    = isset($data['spp_rate']) && $data['spp_rate'] !== '' ? (float)$data['spp_rate'] : null;

            $extraCols = "";
            $params = [
                ':u'  => $data['username'],
                ':n'  => $data['name'],
                ':e'  => $email,
                ':p'  => $data['phone'],
                ':pn' => $parent_name,
                ':id' => $id
            ];

            if (array_key_exists('branch', $data)) {
                $extraCols .= ", branch=:br, spp_rate=:sr";
                $params[':br'] = $branch;
                $params[':sr'] = $spp_rate;
            }
            if (array_key_exists('address', $data)) {
                $extraCols .= ", address=:addr";
                $params[':addr'] = !empty($data['address']) ? $data['address'] : null;
            }
            if (!empty($data['password'])) {
                $extraCols .= ", password=:pass";
                $params[':pass'] = $data['password'];
            }

            $query = "UPDATE users SET username=:u, name=:n, email=:e, phone=:p, parent_name=:pn{$extraCols} WHERE id=:id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Gagal Update User: " . $e->getMessage());
            return false;
        }
    }

    public function agreeTataTertib($user_id) {
        $stmt = $this->db->prepare("UPDATE users SET agreed_tatib_at = NOW() WHERE id = ?");
        return $stmt->execute([(int)$user_id]);
    }

    public function delete($id) {
        try {
            $this->db->beginTransaction();

            $this->db->prepare("DELETE FROM payments WHERE student_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM attendances WHERE student_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM submissions WHERE student_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM class_members WHERE student_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM progress_chapters WHERE student_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM progress_logs WHERE student_id = ?")->execute([$id]);

            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Gagal Delete User (Cascade): " . $e->getMessage());
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
            $email = !empty($data['email']) ? $data['email'] : null;
            $ig_link = !empty($data['ig_link']) ? $data['ig_link'] : null;
            $parent_name = !empty($data['parent_name']) ? $data['parent_name'] : null;

            $query = "UPDATE users SET 
                name = :n, 
                email = :e, 
                phone = :p, 
                parent_name = :pn,
                photo_profile = :f, 
                ig_link = :ig 
                WHERE id = :id";

            $params = [
                ':n' => $data['name'],
                ':e' => $email,
                ':p' => $data['phone'],
                ':pn' => $parent_name,
                ':f' => $data['photo'],
                ':ig' => $ig_link,
                ':id' => $id
            ];

            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);

        } catch (PDOException $e) {
            error_log("Update Profile Error: " . $e->getMessage());
            return false;
        }
    }

    // Ambil jadwal spesifik yang dimiliki oleh seorang siswa (mendukung Private & Band)
    public function getStudentSchedule($student_id) {
        $query = "SELECT cm.*,
                         c.name as class_name,
                         c.type as class_type,
                         cb.name as band_name,
                         COALESCE(ut_band.name, u.name) as teacher_name
                  FROM class_members cm
                  JOIN classes c ON cm.class_id = c.id
                  LEFT JOIN class_bands cb ON cm.band_id = cb.id
                  LEFT JOIN users u ON c.teacher_id = u.id
                  LEFT JOIN users ut_band ON cb.teacher_id = ut_band.id
                  WHERE cm.student_id = :id
                  ORDER BY FIELD(cm.day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), cm.start_time ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $student_id]);
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
