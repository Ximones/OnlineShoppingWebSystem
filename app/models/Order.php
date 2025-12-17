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

    public function createFromCart(int $userId, int $cartId, array $shipping, array $options = []): int
    {
        require_once __DIR__ . '/../lib/rewards.php';

        return db_transaction(function (PDO $pdo) use ($userId, $cartId, $shipping, $options) {
            $selectedIds = array_filter(array_map('intval', $options['item_ids'] ?? []));
            $items = $this->cartItems($cartId, $selectedIds);
            if (empty($items)) {
                throw new \RuntimeException('No items selected for checkout.');
            }

            $total = array_reduce($items, function ($carry, $item) {
                return $carry + ($item['price'] * $item['quantity']);
            }, 0.0);

            $pointsRequested = (int) ($options['points_redeemed'] ?? 0);
            $pointsRequested = (int) floor($pointsRequested / 100) * 100;

            $stm = $pdo->prepare('SELECT reward_points FROM users WHERE id = ? FOR UPDATE');
            $stm->execute([$userId]);
            $currentPoints = (float) $stm->fetchColumn();
            $maxPointsFromBalance = (int) floor($currentPoints);
            $maxPointsFromTotal = (int) floor($total * 100);
            $pointsRedeemed = min($pointsRequested, $maxPointsFromBalance - ($maxPointsFromBalance % 100), $maxPointsFromTotal);
            $pointsRedeemed = max(0, $pointsRedeemed);
            $discount = $pointsRedeemed / 100;

            $shippingFee = max(0.0, (float)($options['shipping_fee'] ?? 0));
            $voucherDiscount = max(0.0, (float)($options['voucher_discount'] ?? 0));
            $pointsDiscount = $discount; // This is the points discount calculated above

            $baseTotal = max(0, $total - $discount - $voucherDiscount);
            $payableTotal = $baseTotal + $shippingFee;

            $orderStatus = $options['order_status'] ?? 'pending';

            $shippingMethod = $options['shipping_method'] ?? 'standard';

            // Map shipping method key to label
            $shippingMethodLabels = [
                'standard' => 'Standard Shipping (3-5 days)',
                'express' => 'Express Shipping (1-2 days)',
                'pickup' => 'Self Pickup (Free)',
            ];
            $shippingMethodLabel = $shippingMethodLabels[$shippingMethod] ?? 'Standard Shipping (3-5 days)';

            $stm = $pdo->prepare('INSERT INTO orders (user_id, cart_id, total_amount, status, shipping_name, shipping_phone, shipping_address, shipping_method, points_discount, voucher_discount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stm->execute([
                $userId,
                $cartId,
                $payableTotal,
                $orderStatus,
                $shipping['name'],
                $shipping['phone'],
                $shipping['address'],
                $shippingMethodLabel,
                $pointsDiscount,
                $voucherDiscount,
            ]);
            $orderId = (int) $pdo->lastInsertId();

            foreach ($items as $item) {
                $stm = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
                $stm->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
            }

            if ($pointsRedeemed > 0) {
                $pdo->prepare('UPDATE users SET reward_points = GREATEST(0, reward_points - ?), updated_at = NOW() WHERE id = ?')->execute([$pointsRedeemed, $userId]);
            }

            // Earn points based on merchandise spend after voucher/points, excluding shipping
            $pointsEarned = calculate_reward_points($baseTotal);
            if ($pointsEarned > 0) {
                $pdo->prepare('UPDATE users SET reward_points = reward_points + ?, updated_at = NOW() WHERE id = ?')->execute([$pointsEarned, $userId]);
            }

            $stm = $pdo->prepare('SELECT reward_points FROM users WHERE id = ?');
            $stm->execute([$userId]);
            $userPoints = (float) $stm->fetchColumn();
            $newTier = calculate_reward_tier($userPoints);
            $pdo->prepare('UPDATE users SET reward_tier = ? WHERE id = ?')->execute([$newTier, $userId]);

            if ($selectedIds) {
                $in = implode(',', array_fill(0, count($selectedIds), '?'));
                $pdo->prepare("DELETE FROM cart_items WHERE id IN ($in)")->execute($selectedIds);
            } else {
                $pdo->prepare('DELETE FROM cart_items WHERE cart_id = ?')->execute([$cartId]);
            }

            $stm = $pdo->prepare('SELECT COUNT(*) FROM cart_items WHERE cart_id = ?');
            $stm->execute([$cartId]);
            $remaining = (int) $stm->fetchColumn();
            if ($remaining === 0) {
                $pdo->prepare('UPDATE carts SET status = "checked_out", updated_at = NOW() WHERE id = ?')->execute([$cartId]);
            }

            return $orderId;
        });
    }

    public function cartItems(int $cartId, array $itemIds = []): array
    {
        $sql = 'SELECT ci.*, p.price FROM cart_items ci INNER JOIN products p ON p.id = ci.product_id WHERE ci.cart_id = ?';
        $params = [$cartId];
        if (!empty($itemIds)) {
            $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
            $sql .= " AND ci.id IN ($placeholders)";
            $params = array_merge($params, $itemIds);
        }
        $stm = $this->db->prepare($sql);
        $stm->execute($params);
        return $stm->fetchAll();
    }

    public function history(int $userId): array
    {
        $stm = $this->db->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
        $stm->execute([$userId]);
        return $stm->fetchAll();
    }

    public function countByUser(int $userId): int
    {
        $stm = $this->db->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
        $stm->execute([$userId]);
        return (int) $stm->fetchColumn();
    }

    public function detail(int $id): ?array
    {
        $stm = $this->db->prepare('SELECT * FROM orders WHERE id = ?');
        $stm->execute([$id]);
        $order = $stm->fetch();

        if (!$order) {
            return null;
        }

        $sql = '
        SELECT 
            oi.*,
            p.name,
            pp.photo_path AS photo
        FROM order_items oi
        INNER JOIN products p ON p.id = oi.product_id
        LEFT JOIN product_photos pp 
            ON pp.product_id = p.id AND pp.is_primary = 1
        WHERE oi.order_id = ?
    ';

        $stm = $this->db->prepare($sql);
        $stm->execute([$id]);
        $order['items'] = $stm->fetchAll();

        // Get voucher used for this order
        $stm = $this->db->prepare('SELECT uv.*, v.code, v.name, v.type, v.value, v.max_discount FROM user_vouchers uv INNER JOIN vouchers v ON v.id = uv.voucher_id WHERE uv.order_id = ? LIMIT 1');
        $stm->execute([$id]);
        $order['voucher'] = $stm->fetch() ?: null;

        // Get payments for this order
        $paymentModel = new \App\Models\Payment();
        $order['payments'] = $paymentModel->findByOrderId($id);

        // Calculate subtotal from items
        $order['subtotal'] = array_reduce($order['items'], function ($carry, $item) {
            return $carry + ($item['unit_price'] * $item['quantity']);
        }, 0.0);

        // Get stored values from database (if available)
        $order['points_discount'] = (float)($order['points_discount'] ?? 0);
        $order['voucher_discount'] = (float)($order['voucher_discount'] ?? 0);
        $order['shipping_method'] = $order['shipping_method'] ?? 'Standard Shipping (3-5 days)';

        // Calculate shipping voucher discount if voucher was used
        $order['shipping_voucher_discount'] = 0.0;

        if ($order['voucher']) {
            $voucher = $order['voucher'];

            // If voucher discount wasn't stored (for old orders), calculate it
            if ($order['voucher_discount'] == 0 && ($voucher['type'] === 'amount' || $voucher['type'] === 'percent')) {
                $subtotal = $order['subtotal'];

                if ($voucher['type'] === 'amount') {
                    $order['voucher_discount'] = min((float)$voucher['value'], $subtotal);
                } elseif ($voucher['type'] === 'percent') {
                    $discount = $subtotal * ((float)$voucher['value'] / 100);
                    if ($voucher['max_discount']) {
                        $discount = min($discount, (float)$voucher['max_discount']);
                    }
                    $order['voucher_discount'] = $discount;
                }
            }

            // Check for shipping vouchers
            if ($voucher['type'] === 'shipping_amount') {
                $order['shipping_voucher_discount'] = (float)$voucher['value'];
            } elseif ($voucher['type'] === 'free_shipping') {
                $order['shipping_voucher_discount'] = 999999; // Will be capped by actual shipping fee
            }
        } elseif ($order['voucher_discount'] > 0) {
            // Voucher was used but record not found - discount is still stored, so show it
            // This handles cases where voucher record might be missing
        }

        // Calculate shipping fee from stored values
        // Formula: total = subtotal - voucher_discount - points_discount + shipping_fee - shipping_voucher_discount
        // So: shipping_fee = total - subtotal + voucher_discount + points_discount + shipping_voucher_discount
        $subtotal = $order['subtotal'];
        $total = $order['total_amount'];

        $order['shipping_fee'] = max(0, $total - $subtotal + $order['voucher_discount'] + $order['points_discount']);

        // Apply shipping voucher discount if applicable
        if ($order['shipping_voucher_discount'] > 0) {
            if ($order['voucher']['type'] === 'free_shipping') {
                $order['shipping_fee'] = 0;
            } else {
                $order['shipping_fee'] = max(0, $order['shipping_fee'] - $order['shipping_voucher_discount']);
            }
        }

        return $order;
    }

    public function historyWithItems(int $userId): array
    {
        $stm = $this->db->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
        $stm->execute([$userId]);
        $orders = $stm->fetchAll();

        foreach ($orders as &$order) {
            $sql = '
            SELECT 
                oi.*,
                p.name,
                pp.photo_path AS photo
            FROM order_items oi
            INNER JOIN products p ON p.id = oi.product_id
            LEFT JOIN product_photos pp 
                ON pp.product_id = p.id AND pp.is_primary = 1
            WHERE oi.order_id = ?
            LIMIT 3
        ';

            $stm = $this->db->prepare($sql);
            $stm->execute([$order['id']]);
            $order['items'] = $stm->fetchAll();

            $stm = $this->db->prepare('SELECT COUNT(*) FROM order_items WHERE order_id = ?');
            $stm->execute([$order['id']]);
            $order['total_items'] = (int) $stm->fetchColumn();
        }

        return $orders;
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

    public function updateStatus(int $id, string $status): void
    {
        $stm = $this->db->prepare('UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?');
        $stm->execute([$status, $id]);
    }
}
