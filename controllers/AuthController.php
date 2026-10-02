<?php
class AuthController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function login_view() {
        if (isset($_GET['timeout']) && $_GET['timeout'] == 'true') {
            echo "<script>alert('Sesi Anda telah berakhir. Silakan login kembali.');</script>";
        }
       require_once '../views/auth/login.php';
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
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            require_once '../helpers/CsrfHelper.php';
            if (!CsrfHelper::verifyToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                die("CSRF token tidak valid");
            }

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

            // 1. Cek Rate Limiting (Maksimal 5 percobaan gagal dalam 15 menit)
            if ($this->isRateLimited($username, $ip)) {
                http_response_code(429);
                echo "<script>
                    alert('Terlalu banyak percobaan login gagal. Silakan coba lagi dalam 15 menit.'); 
                    window.location='index.php?page=auth';
                </script>";
                exit;
            }

            // 2. Verifikasi Kredensial
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Catat percobaan berhasil
                $this->recordLoginAttempt($username, $ip, true);

                // Regenerate session ID to prevent Session Fixation
                session_regenerate_id(true);

                // Simpan data user ke Session
                $_SESSION['user'] = $user;
                $_SESSION['LAST_ACTIVITY'] = time();

                // --- LOGIC REDIRECT SESUAI ROLE ---
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
                        echo "Role tidak dikenali!";
                        exit;
                }
                exit;
            } else {
                // Catat percobaan gagal
                $this->recordLoginAttempt($username, $ip, false);

                echo "<script>
                    alert('Login Gagal! Username atau Password salah.'); 
                    window.location='index.php?page=auth';
                </script>";
                exit;
            }
        }
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