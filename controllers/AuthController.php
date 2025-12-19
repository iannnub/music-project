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
                // Simpan data user ke Session
                $_SESSION['user'] = $user;

                // --- LOGIC REDIRECT SESUAI ROLE ---
                switch ($user['role']) {
                    case 'admin':
                        header("Location: index.php?page=dashboard");
                        break;
                    case 'guru':
                        // Nanti kita buat di Fase 7
                        header("Location: index.php?page=dashboard_guru"); 
                        break;
                    case 'siswa':
                        // INI TARGET KITA SEKARANG
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
        session_destroy();
        header("Location: index.php?page=auth");
    }
}
?>