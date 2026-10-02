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

    public function login_process() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
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
                // Login Gagal
                echo "<script>
                    alert('Login Gagal! Username atau Password salah.'); 
                    window.location='index.php?page=auth';
                </script>";
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