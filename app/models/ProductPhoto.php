<?php

namespace App\Models;

use PDO;
use function db;

class ProductPhoto
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function getByProductId(int $productId): array
    {
        $stm = $this->db->prepare(
            'SELECT * FROM product_photos WHERE product_id = ? ORDER BY display_order ASC'
        );
        $stm->execute([$productId]);
        return $stm->fetchAll();
    }

    public function getPrimaryPhoto(int $productId): ?array
    {
        $stm = $this->db->prepare(
            'SELECT * FROM product_photos WHERE product_id = ? AND is_primary = 1 LIMIT 1'
        );
        $stm->execute([$productId]);
        return $stm->fetch() ?: null;
    }

    public function create(int $productId, string $photoPath, bool $isPrimary = false, int $order = 0): int
    {
        $stm = $this->db->prepare(
            'INSERT INTO product_photos (product_id, photo_path, is_primary, display_order) VALUES (?, ?, ?, ?)'
        );
        $stm->execute([$productId, $photoPath, $isPrimary ? 1 : 0, $order]);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $photoId): void
    {
        $stm = $this->db->prepare('DELETE FROM product_photos WHERE id = ?');
        $stm->execute([$photoId]);
    }

    public function deleteByProductId(int $productId): void
    {
        $stm = $this->db->prepare('DELETE FROM product_photos WHERE product_id = ?');
        $stm->execute([$productId]);
    }

    public function setPrimary(int $photoId, int $productId): void
    {
        $stm = $this->db->prepare('UPDATE product_photos SET is_primary = 0 WHERE product_id = ?');
        $stm->execute([$productId]);
        
        $stm = $this->db->prepare('UPDATE product_photos SET is_primary = 1 WHERE id = ?');
        $stm->execute([$photoId]);
    }
}