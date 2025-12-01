<?php

namespace App\Models;

use PDO;
use function db;

class PasswordReset
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function create(int $userId): string
    {
        $token = bin2hex(random_bytes(20));
        $stm = $this->db->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))');
        $stm->execute([$userId, $token]);
        return $token;
    }

    public function consume(string $token): ?array
    {
        $stm = $this->db->prepare('SELECT * FROM password_resets WHERE token = ? AND used_at IS NULL AND expires_at > NOW()');
        $stm->execute([$token]);
        $row = $stm->fetch();
        if ($row) {
            $this->db->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')->execute([$row['id']]);
        }
        return $row ?: null;
    }
}


