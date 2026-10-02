-- Migration: Tabel login_attempts untuk rate-limiting brute-force login
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at DATETIME NOT NULL,
    success TINYINT(1) NOT NULL,
    INDEX idx_user_ip_time (username, ip_address, attempted_at)
);
