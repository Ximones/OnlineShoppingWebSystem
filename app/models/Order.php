<?php

namespace App\Models;

use PDO;
use function db;
use function db_transaction;

class Order
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function createFromCart(int $userId, int $cartId, array $shipping): int
    {
        return db_transaction(function (PDO $pdo) use ($userId, $cartId, $shipping) {
            $items = $this->cartItems($cartId);
            $total = array_reduce($items, function ($carry, $item) {
                return $carry + ($item['price'] * $item['quantity']);
            }, 0);

            $stm = $pdo->prepare('INSERT INTO orders (user_id, cart_id, total_amount, status, shipping_name, shipping_phone, shipping_address) VALUES (?, ?, ?, "pending", ?, ?, ?)');
            $stm->execute([
                $userId,
                $cartId,
                $total,
                $shipping['name'],
                $shipping['phone'],
                $shipping['address'],
            ]);
            $orderId = (int) $pdo->lastInsertId();

            foreach ($items as $item) {
                $stm = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
                $stm->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
            }

            $pdo->prepare('UPDATE carts SET status = "checked_out", updated_at = NOW() WHERE id = ?')->execute([$cartId]);
            $pdo->prepare('DELETE FROM cart_items WHERE cart_id = ?')->execute([$cartId]);

            return $orderId;
        });
    }

    public function cartItems(int $cartId): array
    {
        $stm = $this->db->prepare('SELECT ci.*, p.price FROM cart_items ci INNER JOIN products p ON p.id = ci.product_id WHERE ci.cart_id = ?');
        $stm->execute([$cartId]);
        return $stm->fetchAll();
    }

    public function history(int $userId): array
    {
        $stm = $this->db->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
        $stm->execute([$userId]);
        return $stm->fetchAll();
    }

    public function detail(int $id): ?array
    {
        $stm = $this->db->prepare('SELECT * FROM orders WHERE id = ?');
        $stm->execute([$id]);
        $order = $stm->fetch();
        if (!$order) {
            return null;
        }

        $stm = $this->db->prepare('SELECT oi.*, p.name FROM order_items oi INNER JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?');
        $stm->execute([$id]);
        $order['items'] = $stm->fetchAll();
        return $order;
    }

    public function adminList(array $filters = []): array
    {
        $sql = 'SELECT o.*, u.name AS member_name FROM orders o INNER JOIN users u ON u.id = o.user_id WHERE 1=1';
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND o.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['keyword'])) {
            $sql .= ' AND (u.name LIKE ? OR u.email LIKE ? OR o.id = ?)';
            $params[] = '%' . $filters['keyword'] . '%';
            $params[] = '%' . $filters['keyword'] . '%';
            $params[] = $filters['keyword'];
        }

        $sql .= ' ORDER BY o.created_at DESC';

        $stm = $this->db->prepare($sql);
        $stm->execute($params);
        return $stm->fetchAll();
    }
}


