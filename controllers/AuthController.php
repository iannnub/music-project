<?php
require_once '../models/UserModel.php';
require_once '../models/PembayaranModel.php';

class AuthController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public static function getBranchTariffs() {
        return [
            'Balung' => [
                'label'            => 'Studio 1 Balung',
                'spp'              => 250000,
                'registration_fee' => 50000,
                'initial_total'    => 300000,
                'address_hint'     => 'Jl. Puger (Dekat Dira Balung), Kec. Balung, Jember'
            ],
            'Jember' => [
                'label'            => 'Studio 2 Jember Kota',
                'spp'              => 350000,
                'registration_fee' => 50000,
                'initial_total'    => 400000,
                'address_hint'     => 'Wilayah Kota Jember, Kabupaten Jember'
            ]
        ];
    }

    public function login_view() {
        $auth_error = null;
        if (isset($_GET['timeout']) && $_GET['timeout'] === 'true') {
            $auth_error = 'Sesi Anda telah berakhir demi keamanan. Silakan masuk kembali.';
        } elseif (!empty($_SESSION['auth_error'])) {
            $auth_error = $_SESSION['auth_error'];
            unset($_SESSION['auth_error']);
        }
        require_once '../views/auth/login.php';
    }

    public function register_view() {
        $tariffs = self::getBranchTariffs();
        $reg_error = $_SESSION['reg_error'] ?? null;
        $reg_old   = $_SESSION['reg_old'] ?? [];
        unset($_SESSION['reg_error'], $_SESSION['reg_old']);
        require_once '../views/auth/register.php';
    }

    public function isRateLimited($username, $ip) {
        $stmtAttempts = $this->db->prepare("
            SELECT COUNT(*) FROM login_attempts 
            WHERE (username = ? OR ip_address = ?) 
            AND success = 0 
            AND attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ");
        $stmtAttempts->execute([$username, $ip]);
        return (int)$stmtAttempts->fetchColumn() >= 5;
    }

    public function recordLoginAttempt($username, $ip, $success) {
        $stmtLog = $this->db->prepare("INSERT INTO login_attempts (username, ip_address, attempted_at, success) VALUES (?, ?, NOW(), ?)");
        return $stmtLog->execute([$username, $ip, $success ? 1 : 0]);
    }

    public function login_process() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=auth");
            exit;
        }
        require_once '../helpers/CsrfHelper.php';
        if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token tidak valid");
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if ($this->isRateLimited($username, $ip)) {
            http_response_code(429);
            $_SESSION['auth_error'] = 'Terlalu banyak percobaan gagal. Silakan tunggu 15 menit sebelum mencoba lagi.';
            header("Location: index.php?page=auth");
            exit;
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $this->recordLoginAttempt($username, $ip, true);
            session_regenerate_id(true);

            $_SESSION['user'] = $user;
            $_SESSION['LAST_ACTIVITY'] = time();

            switch ($user['role']) {
                case 'admin':
                    header("Location: index.php?page=dashboard");
                    break;
                case 'guru':
                    header("Location: index.php?page=dashboard_guru");
                    break;
                case 'siswa':
                    header("Location: index.php?page=dashboard_siswa");
                    break;
                default:
                    header("Location: index.php?page=auth");
                    break;
            }
            exit;
        } else {
            $this->recordLoginAttempt($username, $ip, false);
            $_SESSION['auth_error'] = 'Username atau kata sandi tidak sesuai. Periksa kembali ejaan akun Anda.';
            header("Location: index.php?page=auth");
            exit;
        }
    }

    // Pendaftaran Mandiri Siswa Baru + Pembuatan Tagihan Perdana Otomatis sesuai Cabang + Persetujuan Tata Tertib
    public function register_process() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=register");
            exit;
        }
        require_once '../helpers/CsrfHelper.php';
        if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token tidak valid");
        }

        // Anti-bot Honeypot
        if (!empty($_POST['website_url'])) {
            header("Location: index.php?page=auth");
            exit;
        }

        $tariffs = self::getBranchTariffs();
        $old = [
            'branch'         => trim($_POST['branch'] ?? ''),
            'parent_name'    => trim($_POST['parent_name'] ?? ''),
            'phone'          => trim($_POST['phone'] ?? ''),
            'name'           => trim($_POST['name'] ?? ''),
            'gender'         => trim($_POST['gender'] ?? ''),
            'school_or_work' => trim($_POST['school_or_work'] ?? ''),
            'age'            => trim($_POST['age'] ?? ''),
            'program'        => trim($_POST['program'] ?? ''),
            'instrument'     => trim($_POST['instrument'] ?? ''),
            'preferred_day'  => trim($_POST['preferred_day'] ?? ''),
            'preferred_time' => trim($_POST['preferred_time'] ?? ''),
            'address'        => trim($_POST['address'] ?? ''),
            'username'       => trim($_POST['username'] ?? '')
        ];

        $password    = $_POST['password'] ?? '';
        $agreeTatib  = isset($_POST['agree_tatib']) && $_POST['agree_tatib'] === '1';
        $scrolledEnd = isset($_POST['tatib_scrolled']) && $_POST['tatib_scrolled'] === '1';

        $error = null;
        if (!isset($tariffs[$old['branch']])) {
            $error = 'Silakan pilih cabang kursus (Studio 1 Balung atau Studio 2 Jember Kota).';
        } elseif ($old['parent_name'] === '' || $old['name'] === '' || $old['phone'] === '' || $old['address'] === '') {
            $error = 'Harap lengkapi Nama Wali, Nomor WhatsApp, Nama Lengkap Siswa, dan Alamat.';
        } elseif (!in_array($old['gender'], ['Laki-laki', 'Perempuan'], true)) {
            $error = 'Silakan pilih Jenis Kelamin siswa.';
        } elseif ((int)$old['age'] < 3 || (int)$old['age'] > 80) {
            $error = 'Usia siswa harus diisi dengan angka yang wajar (3 s/d 80 tahun).';
        } elseif ($old['program'] === '' || $old['instrument'] === '') {
            $error = 'Silakan pilih Program dan Bidang Les.';
        } elseif ($old['preferred_day'] === '' || $old['preferred_time'] === '') {
            $error = 'Harap isi pengajuan Hari dan Jam jadwal latihan.';
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]{4,30}$/', $old['username'])) {
            $error = 'Username harus terdiri dari 4-30 karakter (huruf, angka, titik, garis bawah, atau strip tanpa spasi).';
        } elseif (strlen($password) < 6) {
            $error = 'Kata sandi minimal 6 karakter.';
        } elseif (!$agreeTatib || !$scrolledEnd) {
            $error = 'Anda wajib membaca Tata Tertib hingga bagian paling bawah dan mencentang persetujuan.';
        }

        $userModel = new UserModel($this->db);
        if (!$error && $userModel->usernameExists($old['username'])) {
            $error = 'Username "' . htmlspecialchars($old['username']) . '" sudah terdaftar. Silakan pilih username lain.';
        }

        if ($error) {
            $_SESSION['reg_error'] = $error;
            $_SESSION['reg_old']   = $old;
            header("Location: index.php?page=register");
            exit;
        }

        $selectedTariff = $tariffs[$old['branch']];
        $cleanPhone = preg_replace('/[^0-9+]/', '', $old['phone']);

        try {
            $this->db->beginTransaction();

            $newUserId = $userModel->create([
                'username'        => $old['username'],
                'name'            => $old['name'],
                'email'           => null,
                'password'        => password_hash($password, PASSWORD_DEFAULT),
                'role'            => 'siswa',
                'phone'           => $cleanPhone,
                'parent_name'     => $old['parent_name'],
                'branch'          => $old['branch'],
                'spp_rate'        => $selectedTariff['spp'],
                'gender'          => $old['gender'],
                'school_or_work'  => $old['school_or_work'],
                'age'             => (int)$old['age'],
                'program'         => $old['program'],
                'instrument'      => $old['instrument'],
                'preferred_day'   => $old['preferred_day'],
                'preferred_time'  => $old['preferred_time'],
                'address'         => $old['address'],
                'agreed_tatib_at' => date('Y-m-d H:i:s')
            ]);

            if (!$newUserId) {
                throw new Exception('Gagal menyimpan akun siswa.');
            }

            // Buat Tagihan Perdana otomatis (SPP Bulan Pertama + Biaya Pendaftaran Rp 50.000)
            $pembayaranModel = new PembayaranModel($this->db);
            $noteInvoice = sprintf(
                'Tagihan Perdana %s: SPP Rp %s + Biaya Pendaftaran Rp %s (%s - %s, Pengajuan: %s %s)',
                $selectedTariff['label'],
                number_format($selectedTariff['spp'], 0, ',', '.'),
                number_format($selectedTariff['registration_fee'], 0, ',', '.'),
                $old['program'],
                $old['instrument'],
                $old['preferred_day'],
                $old['preferred_time']
            );

            $invoiceOk = $pembayaranModel->create([
                'student_id' => $newUserId,
                'admin_id'   => null,
                'month'      => (int)date('n'),
                'year'       => (int)date('Y'),
                'start_date' => date('Y-m-d'),
                'end_date'   => date('Y-m-d', strtotime('+7 days')),
                'amount'     => $selectedTariff['initial_total'],
                'status'     => 'Belum Lunas',
                'notes'      => $noteInvoice
            ]);

            if (!$invoiceOk) {
                throw new Exception('Gagal menerbitkan tagihan perdana siswa.');
            }

            $this->db->commit();

            // Auto-login siswa baru ke Dashboard Siswa
            $freshUser = $userModel->getById($newUserId);
            session_regenerate_id(true);
            $_SESSION['user'] = $freshUser;
            $_SESSION['LAST_ACTIVITY'] = time();
            $_SESSION['flash'] = [
                'status' => 'success',
                'title'  => 'Pendaftaran Berhasil',
                'msg'    => 'Selamat bergabung di KakYo Lesson! Tagihan perdana ' . $selectedTariff['label'] . ' sebesar Rp ' . number_format($selectedTariff['initial_total'], 0, ',', '.') . ' telah diterbitkan.'
            ];

            header("Location: index.php?page=dashboard_siswa");
            exit;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $_SESSION['reg_error'] = 'Terjadi kendala saat memproses pendaftaran: ' . $e->getMessage();
            $_SESSION['reg_old']   = $old;
            header("Location: index.php?page=register");
            exit;
        }
    }

    // Persetujuan wajib 1x Tata Tertib untuk akun siswa lama
    public function agree_tatib() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?page=dashboard_siswa");
            exit;
        }
        require_once '../helpers/CsrfHelper.php';
        if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die("CSRF token tidak valid");
        }
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        if ($userId > 0 && ($_POST['agree_tatib'] ?? '') === '1') {
            $userModel = new UserModel($this->db);
            $userModel->agreeTataTertib($userId);
            $_SESSION['user'] = $userModel->getById($userId);
            $_SESSION['flash'] = [
                'status' => 'success',
                'title'  => 'Terima Kasih',
                'msg'    => 'Persetujuan Tata Tertib KakYo Lesson telah tersimpan.'
            ];
        }
        $redirect = $_POST['redirect_page'] ?? 'dashboard_siswa';
        $allowedPages = ['dashboard_siswa', 'siswa_materi', 'siswa_tiktok', 'siswa_progress', 'siswa_absensi', 'siswa_bayar', 'siswa_tatib', 'profile'];
        if (!in_array($redirect, $allowedPages, true)) {
            $redirect = 'dashboard_siswa';
        }
        header("Location: index.php?page=" . $redirect);
        exit;
    }

    public function logout() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header("Location: index.php?page=auth");
        exit();
    }
}
?>
