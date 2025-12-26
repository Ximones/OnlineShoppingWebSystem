<?php

namespace App\Models;

use PDO;
use function db;

class UserVoucher
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function activeForUser(int $userId): array
    {
        $sql = 'SELECT uv.*, v.*
                FROM user_vouchers uv
                INNER JOIN vouchers v ON v.id = uv.voucher_id
                WHERE uv.user_id = ? AND uv.status = "active" AND v.is_active = 1
                ORDER BY uv.claimed_at DESC';
        $stm = $this->db->prepare($sql);
        $stm->execute([$userId]);
        return $stm->fetchAll();
    }

    public function allForUser(int $userId): array
    {
        $sql = 'SELECT uv.*, v.*
                FROM user_vouchers uv
                INNER JOIN vouchers v ON v.id = uv.voucher_id
                WHERE uv.user_id = ?
                ORDER BY uv.claimed_at DESC';
        $stm = $this->db->prepare($sql);
        $stm->execute([$userId]);
        return $stm->fetchAll();
    }

    /**
     * Attempt to claim a voucher.
     *
     * @return string One of: 'ok', 'duplicate', 'sold_out'
     */
    public function claim(int $userId, int $voucherId, ?int $maxClaims = null): string
    {
        // Use transaction to prevent race conditions
        $this->db->beginTransaction();
        try {
            // Prevent duplicate claims (any status)
            $stm = $this->db->prepare('SELECT COUNT(*) FROM user_vouchers WHERE user_id = ? AND voucher_id = ?');
            $stm->execute([$userId, $voucherId]);
            if ($stm->fetchColumn() > 0) {
                $this->db->rollBack();
                return 'duplicate';
            }

            if ($maxClaims !== null) {
                $stm = $this->db->prepare('SELECT COUNT(*) FROM user_vouchers WHERE voucher_id = ?');
                $stm->execute([$voucherId]);
                $totalClaims = (int) $stm->fetchColumn();
                if ($totalClaims >= $maxClaims) {
                    $this->db->rollBack();
                    return 'sold_out';
                }
            }

            $stm = $this->db->prepare('INSERT INTO user_vouchers (user_id, voucher_id, status) VALUES (?, ?, "active")');
            $stm->execute([$userId, $voucherId]);
            $this->db->commit();
            return 'ok';
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findForUser(int $userId, int $userVoucherId): ?array
    {
        $sql = 'SELECT uv.*, v.*
                FROM user_vouchers uv
                INNER JOIN vouchers v ON v.id = uv.voucher_id
                WHERE uv.id = ? AND uv.user_id = ?';
        $stm = $this->db->prepare($sql);
        $stm->execute([$userVoucherId, $userId]);
        return $stm->fetch() ?: null;
    }

    /**
     * Mark a voucher as used by ID
     */
    public function markUsed(int $userVoucherId, int $orderId): void
    {
        // Check if voucher exists and get its current status
        $stm = $this->db->prepare('SELECT uv.order_id, uv.status, uv.user_id 
                                   FROM user_vouchers uv 
                                   WHERE uv.id = ?');
        $stm->execute([$userVoucherId]);
        $existing = $stm->fetch();
        
        if (!$existing) {
            throw new \RuntimeException("Voucher #$userVoucherId does not exist. Cannot mark as used for order #$orderId.");
        }
        
        // If already marked for this order, no-op (idempotent)
        if ($existing['order_id'] == $orderId && $existing['status'] === 'used') {
            return;
        }
        
        // If marked for a different order, that's an error
        if ($existing['order_id'] != null && $existing['order_id'] != $orderId) {
            throw new \RuntimeException("Voucher #$userVoucherId is already used for order #{$existing['order_id']}, cannot use for order #$orderId");
        }
        
        // If status is not 'active', provide a more specific error
        if ($existing['status'] !== 'active') {
            throw new \RuntimeException("Voucher #$userVoucherId cannot be marked as used. Current status: '{$existing['status']}' (expected: 'active')");
        }
        
        // Mark as used if it's currently active
        $stm = $this->db->prepare('UPDATE user_vouchers SET status = "used", order_id = ?, used_at = NOW() WHERE id = ? AND status = "active"');
        $stm->execute([$orderId, $userVoucherId]);
        
        // Verify the update was successful
        if ($stm->rowCount() === 0) {
            throw new \RuntimeException("Failed to mark voucher #$userVoucherId as used for order #$orderId. Voucher status may have changed.");
        }
    }

    /**
     * Mark a voucher as used by code (more reliable fallback)
     * Finds the most recently claimed active voucher with the given code for the user
     */
    public function markUsedByCode(int $userId, string $voucherCode, int $orderId): bool
    {
        // Find the most recently claimed active voucher with this code
        $stm = $this->db->prepare('SELECT uv.id, uv.order_id, uv.status 
                                   FROM user_vouchers uv 
                                   INNER JOIN vouchers v ON v.id = uv.voucher_id 
                                   WHERE uv.user_id = ? AND v.code = ? AND uv.status = "active" 
                                   ORDER BY uv.claimed_at DESC 
                                   LIMIT 1');
        $stm->execute([$userId, strtoupper($voucherCode)]);
        $voucher = $stm->fetch();
        
        if (!$voucher) {
            return false; // No active voucher found with this code
        }
        
        // If already marked for this order, no-op (idempotent)
        if ($voucher['order_id'] == $orderId && $voucher['status'] === 'used') {
            return true;
        }
        
        // If marked for a different order, that's an error
        if ($voucher['order_id'] != null && $voucher['order_id'] != $orderId) {
            throw new \RuntimeException("Voucher code '$voucherCode' is already used for order #{$voucher['order_id']}, cannot use for order #$orderId");
        }
        
        // Mark as used
        $stm = $this->db->prepare('UPDATE user_vouchers SET status = "used", order_id = ?, used_at = NOW() WHERE id = ? AND status = "active"');
        $stm->execute([$orderId, $voucher['id']]);
        
        return $stm->rowCount() > 0;
    }

    /**
     * Get total claim counts per voucher (all users).
     *
     * @return array<int,array{voucher_id:int,total_claims:int}>
     */
    public function countsByVoucher(): array
    {
        $sql = 'SELECT voucher_id, COUNT(*) AS total_claims
                FROM user_vouchers
                GROUP BY voucher_id';
        $stm = $this->db->prepare($sql);
        $stm->execute();
        return $stm->fetchAll();
    }
}


