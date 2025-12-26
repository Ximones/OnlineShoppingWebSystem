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
            $pointsRequested = (int) floor($pointsRequested / 10) * 10;

            $stm = $pdo->prepare('SELECT reward_points FROM users WHERE id = ? FOR UPDATE');
            $stm->execute([$userId]);
            $currentPoints = (float) $stm->fetchColumn();
            $maxPointsFromBalance = (int) floor($currentPoints);
            $maxPointsFromTotal = (int) floor($total * 10);
            $pointsRedeemed = min($pointsRequested, $maxPointsFromBalance - ($maxPointsFromBalance % 10), $maxPointsFromTotal);
            $pointsRedeemed = max(0, $pointsRedeemed);
            $discount = $pointsRedeemed / 10;

            // Get shipping fee and discounts
            // shipping_fee in options is the EFFECTIVE fee (after shipping voucher discount)
            $effectiveShippingFee = max(0.0, (float)($options['shipping_fee'] ?? 0));
            $voucherDiscount = max(0.0, (float)($options['voucher_discount'] ?? 0)); // Merchandise discount only
            $shippingVoucherDiscount = max(0.0, (float)($options['voucher_shipping_discount'] ?? 0)); // Shipping discount
            $pointsDiscount = $discount; // This is the points discount calculated above

            // Calculate base total (subtotal minus merchandise discounts)
            $baseTotal = max(0, $total - $discount - $voucherDiscount);
            
            // Payable total = base total + effective shipping fee (already has shipping discount applied)
            $payableTotal = $baseTotal + $effectiveShippingFee;
            
            // Store shipping voucher discount in voucher_discount field temporarily if it's a shipping-only voucher
            // We'll use this to calculate backwards in detail() method
            // Actually, we can't store it separately without a new column, so we'll calculate it in detail()

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

            // Only deduct points and earn points if order is already paid (PayLater)
            // For Stripe orders (pending), points will be handled after payment succeeds
            if ($orderStatus === 'paid' && $pointsRedeemed > 0) {
                $pdo->prepare('UPDATE users SET reward_points = GREATEST(0, reward_points - ?), updated_at = NOW() WHERE id = ?')->execute([$pointsRedeemed, $userId]);
            }

            // Earn points based on merchandise spend after voucher/points, excluding shipping
            if ($orderStatus === 'paid') {
                $pointsEarned = calculate_reward_points($baseTotal);
                if ($pointsEarned > 0) {
                    $pdo->prepare('UPDATE users SET reward_points = reward_points + ?, updated_at = NOW() WHERE id = ?')->execute([$pointsEarned, $userId]);
                }

                $stm = $pdo->prepare('SELECT reward_points FROM users WHERE id = ?');
                $stm->execute([$userId]);
                $userPoints = (float) $stm->fetchColumn();
                $newTier = calculate_reward_tier($userPoints);
                $pdo->prepare('UPDATE users SET reward_tier = ? WHERE id = ?')->execute([$newTier, $userId]);
            }

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

        // Attach existing reviews per item for the current user (if logged in)
        if (auth_id()) {
            $reviewModel = new \App\Models\Review();
            foreach ($order['items'] as &$item) {
                $item['user_review'] = $reviewModel->findForUserAndOrderItem(auth_id(), (int)$item['product_id'], $id);
            }
            unset($item);
        }

        // Tracking history
        $stm = $this->db->prepare('SELECT * FROM tracking_details WHERE order_id = ? ORDER BY tracking_date ASC, id ASC');
        $stm->execute([$id]);
        $order['tracking'] = $stm->fetchAll();

        // Get voucher used for this order
        // Order by used_at DESC to get the most recently used voucher if multiple exist (shouldn't happen, but safety)
        $stm = $this->db->prepare('SELECT uv.*, v.code, v.name, v.type, v.value, v.max_discount FROM user_vouchers uv INNER JOIN vouchers v ON v.id = uv.voucher_id WHERE uv.order_id = ? ORDER BY uv.used_at DESC LIMIT 1');
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

        // Calculate original shipping fee from shipping method
        $shippingMethodLabels = [
            'Standard Shipping (3-5 days)' => 10.0,
            'Express Shipping (1-2 days)' => 25.0,
            'Self Pickup (Free)' => 0.0,
        ];
        $originalShippingFee = $shippingMethodLabels[$order['shipping_method']] ?? 10.0;

        // Calculate effective shipping fee from order total
        // Formula: total = subtotal - voucher_discount - points_discount + effective_shipping_fee
        // So: effective_shipping_fee = total - subtotal + voucher_discount + points_discount
        $subtotal = $order['subtotal'];
        $total = $order['total_amount'];
        $effectiveShippingFee = max(0, $total - $subtotal + $order['voucher_discount'] + $order['points_discount']);
        $order['shipping_fee'] = $effectiveShippingFee;

        // Calculate shipping voucher discount
        $order['shipping_voucher_discount'] = 0.0;

        if ($order['voucher']) {
            $voucher = $order['voucher'];

            // If voucher discount wasn't stored (for old orders), calculate merchandise discount
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

            // Calculate shipping voucher discount based on voucher type
            if ($voucher['type'] === 'shipping_amount') {
                // Shipping amount discount: min of value and original shipping fee
                $order['shipping_voucher_discount'] = min((float)$voucher['value'], $originalShippingFee);
            } elseif ($voucher['type'] === 'free_shipping') {
                // Free shipping: discount equals original shipping fee
                $order['shipping_voucher_discount'] = $originalShippingFee;
            }
        } elseif ($order['voucher_discount'] > 0) {
            // Voucher was used but record not found - discount is still stored, so show it
            // This handles cases where voucher record might be missing
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

    /**
     * Process points deduction and voucher marking after payment succeeds.
     * This is called for Stripe orders that were created with status 'pending'.
     */
    public function processPointsAndVouchers(int $orderId, ?string $voucherCode = null, ?int $userVoucherId = null): void
    {
        require_once __DIR__ . '/../lib/rewards.php';
        
        $order = $this->detail($orderId);
        if (!$order) {
            return;
        }

        $userId = (int) $order['user_id'];
        $pointsDiscount = (float) ($order['points_discount'] ?? 0);
        $pointsRedeemed = (int) ($pointsDiscount * 10); // Convert RM to points (10 points = RM1)
        
        // Deduct points if any were redeemed
        if ($pointsRedeemed > 0) {
            $this->db->prepare('UPDATE users SET reward_points = GREATEST(0, reward_points - ?), updated_at = NOW() WHERE id = ?')
                ->execute([$pointsRedeemed, $userId]);
        }

        // Mark voucher as used if one was applied
        // Use code-based marking as it's more reliable than ID-based
        $userVoucherModel = new \App\Models\UserVoucher();
        $marked = false;
        
        if ($voucherCode) {
            // Always try to mark by code first (most reliable)
            try {
                $marked = $userVoucherModel->markUsedByCode($userId, $voucherCode, $orderId);
            } catch (\RuntimeException $e) {
                error_log("Error marking voucher by code '$voucherCode' for order #$orderId: " . $e->getMessage());
            }
        }
        
        // If code-based marking failed and we have an ID, try ID-based as fallback
        if (!$marked && $userVoucherId) {
            try {
                $userVoucherModel->markUsed($userVoucherId, $orderId);
                $marked = true;
            } catch (\RuntimeException $e) {
                error_log("Error marking voucher #$userVoucherId for order #$orderId: " . $e->getMessage());
                // If ID fails, try code again as last resort
                if ($voucherCode) {
                    try {
                        $marked = $userVoucherModel->markUsedByCode($userId, $voucherCode, $orderId);
                    } catch (\RuntimeException $e2) {
                        error_log("Final attempt to mark voucher by code '$voucherCode' failed: " . $e2->getMessage());
                    }
                }
            }
        }

        // Earn points based on merchandise spend after voucher/points, excluding shipping
        $subtotal = array_reduce($order['items'], function ($carry, $item) {
            return $carry + ($item['unit_price'] * $item['quantity']);
        }, 0.0);
        $baseTotal = max(0, $subtotal - $pointsDiscount - ($order['voucher_discount'] ?? 0));
        $pointsEarned = calculate_reward_points($baseTotal);
        
        if ($pointsEarned > 0) {
            $this->db->prepare('UPDATE users SET reward_points = reward_points + ?, updated_at = NOW() WHERE id = ?')
                ->execute([$pointsEarned, $userId]);
        }

        // Update reward tier
        $stm = $this->db->prepare('SELECT reward_points FROM users WHERE id = ?');
        $stm->execute([$userId]);
        $userPoints = (float) $stm->fetchColumn();
        $newTier = calculate_reward_tier($userPoints);
        $this->db->prepare('UPDATE users SET reward_tier = ? WHERE id = ?')->execute([$newTier, $userId]);
    }

    /**
     * Refund points when an order is cancelled
     * - Deduct earned points (if order was paid and earned points)
     * - Refund redeemed points (if any were redeemed)
     * - Update reward tier
     */
    public function refundPointsOnCancel(int $orderId): void
    {
        require_once __DIR__ . '/../lib/rewards.php';
        
        $order = $this->detail($orderId);
        if (!$order) {
            return;
        }

        $userId = (int) $order['user_id'];
        $currentStatus = strtolower($order['status'] ?? '');
        
        // Calculate points earned (same calculation as when order was created)
        $subtotal = array_reduce($order['items'], function ($carry, $item) {
            return $carry + ($item['unit_price'] * $item['quantity']);
        }, 0.0);
        $pointsDiscount = (float) ($order['points_discount'] ?? 0);
        $voucherDiscount = (float) ($order['voucher_discount'] ?? 0);
        $baseTotal = max(0, $subtotal - $pointsDiscount - $voucherDiscount);
        $pointsEarned = calculate_reward_points($baseTotal);
        
        // Deduct earned points (if order was paid and earned points)
        // Only deduct if order was in a paid state (not pending Stripe orders that were never paid)
        // Pending orders that were paid via Stripe would have been updated to 'processing' or 'shipped' after payment
        $paidStatuses = ['paid', 'processing', 'shipped', 'completed'];
        if (in_array($currentStatus, $paidStatuses, true) && $pointsEarned > 0) {
            $this->db->prepare('UPDATE users SET reward_points = GREATEST(0, reward_points - ?), updated_at = NOW() WHERE id = ?')
                ->execute([$pointsEarned, $userId]);
        }
        
        // Refund redeemed points (if any were redeemed)
        // This applies to all orders regardless of status, as points were deducted when order was created
        $pointsRedeemed = (int) ($pointsDiscount * 10); // Convert RM to points (10 points = RM1)
        if ($pointsRedeemed > 0) {
            $this->db->prepare('UPDATE users SET reward_points = reward_points + ?, updated_at = NOW() WHERE id = ?')
                ->execute([$pointsRedeemed, $userId]);
        }
        
        // Update reward tier
        $stm = $this->db->prepare('SELECT reward_points FROM users WHERE id = ?');
        $stm->execute([$userId]);
        $userPoints = (float) $stm->fetchColumn();
        $newTier = calculate_reward_tier($userPoints);
        $this->db->prepare('UPDATE users SET reward_tier = ? WHERE id = ?')->execute([$newTier, $userId]);
    }

    public function addTracking(int $orderId, string $status, ?string $location = null, ?string $remarks = null): void
    {
        $stm = $this->db->prepare(
            'INSERT INTO tracking_details (order_id, status, location, remarks) VALUES (?, ?, ?, ?)'
        );
        $stm->execute([$orderId, $status, $location, $remarks]);
    }

    public function deleteTracking(int $trackingId, int $orderId): void
    {
        $stm = $this->db->prepare('DELETE FROM tracking_details WHERE id = ? AND order_id = ?');
        $stm->execute([$trackingId, $orderId]);
    }
}
