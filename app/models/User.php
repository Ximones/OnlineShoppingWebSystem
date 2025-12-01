<?php

namespace App\Models;

use PDO;
use function db;

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function find(int $id): ?array
    {
        $stm = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stm->execute([$id]);
        return $stm->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stm = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stm->execute([$email]);
        return $stm->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stm = $this->db->prepare('INSERT INTO users (name, email, phone, password_hash, role, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stm->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['password_hash'],
            $data['role'] ?? 'member',
            $data['status'] ?? 'active',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateProfile(int $id, array $data): void
    {
        $stm = $this->db->prepare('UPDATE users SET name = ?, phone = ?, address = ?, updated_at = NOW() WHERE id = ?');
        $stm->execute([
            $data['name'],
            $data['phone'],
            $data['address'],
            $id,
        ]);
    }

    public function updatePassword(int $id, string $hash): void
    {
        $stm = $this->db->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
        $stm->execute([$hash, $id]);
    }

    public function updateAvatar(int $id, string $path): void
    {
        $stm = $this->db->prepare('UPDATE users SET avatar = ?, updated_at = NOW() WHERE id = ?');
        $stm->execute([$path, $id]);
    }

    public function listMembers(string $keyword = ''): array
    {
        $sql = 'SELECT * FROM users WHERE role = "member"';
        $params = [];
        if ($keyword) {
            $sql .= ' AND (name LIKE ? OR email LIKE ?)';
            $params = ["%$keyword%", "%$keyword%"];
        }
        $sql .= ' ORDER BY created_at DESC';

        $stm = $this->db->prepare($sql);
        $stm->execute($params);
        return $stm->fetchAll();
    }
}


