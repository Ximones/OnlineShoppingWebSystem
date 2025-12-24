<?php

namespace App\Models;

use PDO;
use function db;

class Category
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function all(): array
    {
        $stm = $this->db->query('SELECT * FROM categories ORDER BY name');
        return $stm->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stm = $this->db->prepare('SELECT * FROM categories WHERE id = ?');
        $stm->execute([$id]);
        return $stm->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stm = $this->db->prepare(
            'INSERT INTO categories (name, description, image_url) VALUES (?, ?, ?)'
        );
        $stm->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['image_url'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stm = $this->db->prepare(
            'UPDATE categories SET name = ?, description = ?, image_url = ?, updated_at = NOW() WHERE id = ?'
        );
        $stm->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['image_url'] ?? null,
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        // Check if category has products
        $stm = $this->db->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
        $stm->execute([$id]);
        $productCount = (int) $stm->fetchColumn();

        if ($productCount > 0) {
            throw new \RuntimeException("Cannot delete category. There are $productCount product(s) in this category.");
        }

        $stm = $this->db->prepare('DELETE FROM categories WHERE id = ?');
        $stm->execute([$id]);
    }

    public function batchDelete(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $failed = [];
        foreach ($ids as $id) {
            try {
                $this->delete($id);
            } catch (\RuntimeException $e) {
                $failed[] = ['id' => $id, 'error' => $e->getMessage()];
            }
        }
        return $failed;
    }
}


