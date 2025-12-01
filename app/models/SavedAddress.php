<?php

namespace App\Models;

use PDO;
use function db;

class SavedAddress
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function findByUser(int $userId): array
    {
        $stm = $this->db->prepare('SELECT * FROM saved_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC');
        $stm->execute([$userId]);
        return $stm->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stm = $this->db->prepare('SELECT * FROM saved_addresses WHERE id = ?');
        $stm->execute([$id]);
        $result = $stm->fetch();
        return $result ?: null;
    }

    public function create(array $data): int
    {
        // If this is set as default, unset other defaults for this user
        if (!empty($data['is_default'])) {
            $this->db->prepare('UPDATE saved_addresses SET is_default = 0 WHERE user_id = ?')->execute([$data['user_id']]);
        }

        $stm = $this->db->prepare('INSERT INTO saved_addresses (user_id, label, name, phone, address, is_default) VALUES (?, ?, ?, ?, ?, ?)');
        $stm->execute([
            $data['user_id'],
            $data['label'],
            $data['name'],
            $data['phone'],
            $data['address'],
            $data['is_default'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        // If this is set as default, unset other defaults for this user
        if (!empty($data['is_default'])) {
            $current = $this->find($id);
            if ($current) {
                $this->db->prepare('UPDATE saved_addresses SET is_default = 0 WHERE user_id = ? AND id != ?')->execute([$current['user_id'], $id]);
            }
        }

        $stm = $this->db->prepare('UPDATE saved_addresses SET label = ?, name = ?, phone = ?, address = ?, is_default = ?, updated_at = NOW() WHERE id = ?');
        $stm->execute([
            $data['label'],
            $data['name'],
            $data['phone'],
            $data['address'],
            $data['is_default'] ?? 0,
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stm = $this->db->prepare('DELETE FROM saved_addresses WHERE id = ?');
        $stm->execute([$id]);
    }

    public function setDefault(int $id, int $userId): void
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare('UPDATE saved_addresses SET is_default = 0 WHERE user_id = ?')->execute([$userId]);
            $this->db->prepare('UPDATE saved_addresses SET is_default = 1 WHERE id = ? AND user_id = ?')->execute([$id, $userId]);
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}

