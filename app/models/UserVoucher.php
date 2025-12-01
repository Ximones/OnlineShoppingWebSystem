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
                WHERE uv.user_id = ? AND uv.status = "active" AND v.is_active = 1';
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
        // Prevent duplicate claims (any status)
        $stm = $this->db->prepare('SELECT COUNT(*) FROM user_vouchers WHERE user_id = ? AND voucher_id = ?');
        $stm->execute([$userId, $voucherId]);
        if ($stm->fetchColumn() > 0) {
            return 'duplicate';
        }

        if ($maxClaims !== null) {
            $stm = $this->db->prepare('SELECT COUNT(*) FROM user_vouchers WHERE voucher_id = ?');
            $stm->execute([$voucherId]);
            $totalClaims = (int) $stm->fetchColumn();
            if ($totalClaims >= $maxClaims) {
                return 'sold_out';
            }
        }

        $stm = $this->db->prepare('INSERT INTO user_vouchers (user_id, voucher_id, status) VALUES (?, ?, "active")');
        $stm->execute([$userId, $voucherId]);
        return 'ok';
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

    public function markUsed(int $userVoucherId, int $orderId): void
    {
        $stm = $this->db->prepare('UPDATE user_vouchers SET status = "used", order_id = ?, used_at = NOW() WHERE id = ?');
        $stm->execute([$orderId, $userVoucherId]);
    }
}


