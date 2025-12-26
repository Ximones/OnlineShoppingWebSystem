<?php

namespace App\Models;

use PDO;
use function db;

class Product
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function all(array $filters = []): array
    {
        $sql = 'SELECT p.*, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE 1=1';
        $params = [];

        $this->applyFilters($sql, $params, $filters);

        switch ($filters['sort'] ?? '') {
            case 'price_asc':
                $sql .= ' ORDER BY p.price ASC';
                break;
            case 'price_desc':
                $sql .= ' ORDER BY p.price DESC';
                break;
            case 'name_asc':
                $sql .= ' ORDER BY p.name ASC';
                break;
            case 'name_desc':
                $sql .= ' ORDER BY p.name DESC';
                break;
            default:
                $sql .= ' ORDER BY p.created_at DESC';
        }


        if (isset($filters['limit'], $filters['offset'])) {
            $sql .= ' LIMIT ? OFFSET ?';
        }

        $stm = $this->db->prepare($sql);

        // 1. Bind the standard filter parameters (Keyword, Category, etc.)
        foreach ($params as $index => $value) {
            $stm->bindValue($index + 1, $value);
        }

        // 2. Bind the LIMIT and OFFSET explicitly as INTEGERS
        if (isset($filters['limit'], $filters['offset'])) {
            $paramCount = count($params);
            $stm->bindValue($paramCount + 1, (int)$filters['limit'], PDO::PARAM_INT);
            $stm->bindValue($paramCount + 2, (int)$filters['offset'], PDO::PARAM_INT);
        }

        $stm->execute();
        return $stm->fetchAll();
    }

    public function countAll(array $filters = []): int
    {
        $sql = 'SELECT COUNT(*) 
                FROM products p
                WHERE 1=1';
        $params = [];

        $this->applyFilters($sql, $params, $filters);

        $stm = $this->db->prepare($sql);
        $stm->execute($params);

        return (int) $stm->fetchColumn();
    }

    private function applyFilters(string &$sql, array &$params, array $filters): void
    {
        if (!empty($filters['keyword'])) {
            $sql .= ' AND (p.name LIKE ? OR p.sku LIKE ?)';
            $params[] = '%' . $filters['keyword'] . '%';
            $params[] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['status'])) {
            $sql .= ' AND p.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['category_id'])) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $filters['category_id'];
        }

        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $sql .= ' AND p.price >= ?';
            $params[] = $filters['min_price'];
        }

        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $sql .= ' AND p.price <= ?';
            $params[] = $filters['max_price'];
        }
    }

    public function find(int $id): ?array
    {
        $stm = $this->db->prepare('SELECT * FROM products WHERE id = ?');
        $stm->execute([$id]);
        return $stm->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stm = $this->db->prepare(
            'INSERT INTO products (category_id, sku, name, description, price, stock, status)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $stm->execute([
            $data['category_id'],
            $data['sku'],
            $data['name'],
            $data['description'],
            $data['price'],
            $data['stock'],
            $data['status'] ?? 'active',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stm = $this->db->prepare(
            'UPDATE products
             SET category_id = ?, sku = ?, name = ?, description = ?, price = ?, stock = ?, status = ?, updated_at = NOW()
             WHERE id = ?'
        );

        $stm->execute([
            $data['category_id'],
            $data['sku'],
            $data['name'],
            $data['description'],
            $data['price'],
            $data['stock'],
            $data['status'],
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stm = $this->db->prepare('DELETE FROM products WHERE id = ?');
        $stm->execute([$id]);
    }

    public function batchDelete(array $ids): void
    {
        if (empty($ids)) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stm = $this->db->prepare("DELETE FROM products WHERE id IN ($placeholders)");
        $stm->execute($ids);
    }

    public function getTopSellers(int $limit = 5): array
    {
        $sql = "SELECT p.*, SUM(oi.quantity) as total_sold
            FROM products p
            JOIN order_items oi ON p.id = oi.product_id
            WHERE p.status = 'active'
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT ?";

        $stm = $this->db->prepare($sql);
        // We use PARAM_INT to ensure the LIMIT works correctly in SQL
        $stm->bindValue(1, $limit, PDO::PARAM_INT);
        $stm->execute();

        return $stm->fetchAll();
    }

    public function reduceStock(int $productId, int $quantity): bool
    {
        $sql = "UPDATE products 
            SET stock = stock - ? 
            WHERE id = ? AND stock >= ?";

        $stm = $this->db->prepare($sql);
        $stm->execute([$quantity, $productId, $quantity]);

        return $stm->rowCount() > 0;
    }

    public function restoreStock(int $productId, int $quantity): bool
    {
        $sql = "UPDATE products 
            SET stock = stock + ? 
            WHERE id = ?";

        $stm = $this->db->prepare($sql);
        $stm->execute([$quantity, $productId]);

        return $stm->rowCount() > 0;
    }

    public function skuExists(string $sku, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM products WHERE sku = ?';
        $params = [$sku];

        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $stm = $this->db->prepare($sql);
        $stm->execute($params);

        return $stm->fetch() !== false;
    }
}
