<?php

namespace App\Models;

use PDO;
use function db;

class Review
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function upsert(int $userId, int $productId, int $orderId, int $rating, string $comment): void
    {
        // One review per user+product+order
        $stm = $this->db->prepare('
            INSERT INTO product_reviews (user_id, product_id, order_id, rating, comment)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), updated_at = CURRENT_TIMESTAMP
        ');
        $stm->execute([$userId, $productId, $orderId, $rating, $comment]);
    }

    public function forProduct(int $productId): array
    {
        $sql = 'SELECT r.*, u.name AS user_name
                FROM product_reviews r
                INNER JOIN users u ON u.id = r.user_id
                WHERE r.product_id = ?
                ORDER BY r.created_at DESC';
        $stm = $this->db->prepare($sql);
        $stm->execute([$productId]);
        return $stm->fetchAll();
    }

    public function findForUserAndOrderItem(int $userId, int $productId, int $orderId): ?array
    {
        $stm = $this->db->prepare('
            SELECT * FROM product_reviews
            WHERE user_id = ? AND product_id = ? AND order_id = ?
            LIMIT 1
        ');
        $stm->execute([$userId, $productId, $orderId]);
        return $stm->fetch() ?: null;
    }
}


