<?php

namespace App\Models;

use PDO;
use function db;

class Cart
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function activeCartId(int $userId): int
    {
        $stm = $this->db->prepare('SELECT id FROM carts WHERE user_id = ? AND status = "open" LIMIT 1');
        $stm->execute([$userId]);
        $cartId = $stm->fetchColumn();
        if ($cartId) {
            return (int) $cartId;
        }

        $stm = $this->db->prepare('INSERT INTO carts (user_id, status) VALUES (?, "open")');
        $stm->execute([$userId]);
        return (int) $this->db->lastInsertId();
    }

    public function items(int $cartId): array
    {
        $sql = 'SELECT 
                ci.*, 
                p.name, 
                p.price, 
                pp.photo_path AS photo
            FROM cart_items ci
            INNER JOIN products p ON p.id = ci.product_id
            LEFT JOIN product_photos pp 
                ON pp.product_id = p.id AND pp.is_primary = 1
            WHERE ci.cart_id = ?';

        $stm = $this->db->prepare($sql);
        $stm->execute([$cartId]);
        return $stm->fetchAll();
    }

    public function addItem(int $cartId, int $productId, int $quantity): void
    {
        $stm = $this->db->prepare('SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?');
        $stm->execute([$cartId, $productId]);
        $item = $stm->fetch();
        if ($item) {
            $stm = $this->db->prepare('UPDATE cart_items SET quantity = quantity + ? WHERE id = ?');
            $stm->execute([$quantity, $item['id']]);
        } else {
            $stm = $this->db->prepare('INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)');
            $stm->execute([$cartId, $productId, $quantity]);
        }
    }

    public function updateItem(int $itemId, int $quantity): void
    {
        $stm = $this->db->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?');
        $stm->execute([$quantity, $itemId]);
    }

    public function removeItem(int $itemId): void
    {
        $stm = $this->db->prepare('DELETE FROM cart_items WHERE id = ?');
        $stm->execute([$itemId]);
    }

    public function removeItems(array $itemIds): void
    {
        if (empty($itemIds)) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
        $stm = $this->db->prepare("DELETE FROM cart_items WHERE id IN ($placeholders)");
        $stm->execute($itemIds);
    }

    public function clear(int $cartId): void
    {
        $stm = $this->db->prepare('DELETE FROM cart_items WHERE cart_id = ?');
        $stm->execute([$cartId]);
    }
}
