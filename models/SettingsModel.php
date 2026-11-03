<?php
class SettingsModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function get($key, $default = null)
    {
        try {
            $stmt = $this->db->prepare("SELECT setting_value FROM app_settings WHERE setting_key = :k LIMIT 1");
            $stmt->execute([':k' => $key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && $row['setting_value'] !== null) {
                return $row['setting_value'];
            }
        } catch (PDOException $e) {
            return $default;
        }
        return $default;
    }

    public function set($key, $value)
    {
        $stmt = $this->db->prepare("
            INSERT INTO app_settings (setting_key, setting_value)
            VALUES (:k, :v1)
            ON DUPLICATE KEY UPDATE setting_value = :v2
        ");
        return $stmt->execute([
            ':k'  => $key,
            ':v1' => $value,
            ':v2' => $value
        ]);
    }

    public function getAllMap()
    {
        try {
            $stmt = $this->db->query("SELECT setting_key, setting_value FROM app_settings");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $map = [];
            foreach ($rows as $r) {
                $map[$r['setting_key']] = $r['setting_value'];
            }
            return $map;
        } catch (PDOException $e) {
            return [];
        }
    }
}
