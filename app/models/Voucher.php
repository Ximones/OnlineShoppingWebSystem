<?php

namespace App\Models;

use PDO;
use function db;

class Voucher
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function all(array $filters = []): array
    {
        $sql = 'SELECT * FROM vouchers WHERE 1=1';
        $params = [];

        if (!empty($filters['active_only'])) {
            $sql .= ' AND is_active = 1';
        }

        if (!empty($filters['search'])) {
            $sql .= ' AND (code LIKE ? OR name LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $sql .= ' ORDER BY created_at DESC';

        $stm = $this->db->prepare($sql);
        $stm->execute($params);
        return $stm->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stm = $this->db->prepare('SELECT * FROM vouchers WHERE id = ?');
        $stm->execute([$id]);
        return $stm->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stm = $this->db->prepare(
            'INSERT INTO vouchers (code, name, description, type, value, min_subtotal, max_discount, max_claims, is_shipping_only, is_first_order_only, start_at, end_at, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stm->execute([
            strtoupper($data['code']),
            $data['name'],
            $data['description'] ?? null,
            $data['type'],
            $data['value'],
            $data['min_subtotal'] ?? 0,
            $data['max_discount'] ?? null,
            $data['max_claims'] !== null && $data['max_claims'] !== '' ? (int) $data['max_claims'] : null,
            !empty($data['is_shipping_only']) ? 1 : 0,
            !empty($data['is_first_order_only']) ? 1 : 0,
            $data['start_at'] ?: null,
            $data['end_at'] ?: null,
            !empty($data['is_active']) ? 1 : 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stm = $this->db->prepare(
            'UPDATE vouchers
             SET code = ?, name = ?, description = ?, type = ?, value = ?, min_subtotal = ?, max_discount = ?, max_claims = ?, is_shipping_only = ?, is_first_order_only = ?, start_at = ?, end_at = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stm->execute([
            strtoupper($data['code']),
            $data['name'],
            $data['description'] ?? null,
            $data['type'],
            $data['value'],
            $data['min_subtotal'] ?? 0,
            $data['max_discount'] ?? null,
            $data['max_claims'] !== null && $data['max_claims'] !== '' ? (int) $data['max_claims'] : null,
            !empty($data['is_shipping_only']) ? 1 : 0,
            !empty($data['is_first_order_only']) ? 1 : 0,
            $data['start_at'] ?: null,
            $data['end_at'] ?: null,
            !empty($data['is_active']) ? 1 : 0,
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stm = $this->db->prepare('DELETE FROM vouchers WHERE id = ?');
        $stm->execute([$id]);
    }

    public function batchDelete(array $ids): void
    {
        if (empty($ids)) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stm = $this->db->prepare("DELETE FROM vouchers WHERE id IN ($placeholders)");
        $stm->execute($ids);
    }

        public function findByCode(string $code): ?array
    {
        $stm = $this->db->prepare('SELECT * FROM vouchers WHERE code = ? LIMIT 1');
        $stm->execute([$code]);
        return $stm->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
}


