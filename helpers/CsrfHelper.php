<?php
class CsrfHelper {

    // 1. Generate Token (Dipanggil sekali saat login/mulai sesi)
    public static function generateToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    // 2. Buat Input Hidden (Ditaruh di dalam <form>)
    public static function formField() {
        $token = $_SESSION['csrf_token'] ?? '';
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    // 3. Verifikasi Token (Ditaruh di Controller)
    public static function verifyToken($token_from_post) {
        if (!isset($_SESSION['csrf_token']) || !isset($token_from_post)) {
            return false;
        }
        // Pakai hash_equals untuk mencegah Timing Attack
        return hash_equals($_SESSION['csrf_token'], $token_from_post);
    }
}
?>