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

    if (!empty($filters['sort'])) {
        switch ($filters['sort']) {
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
    } else {
        $sql .= ' ORDER BY p.created_at DESC';
    }

    $stm = $this->db->prepare($sql);
    $stm->execute($params);
    return $stm->fetchAll();
}


    public function find(int $id): ?array
    {
        $stm = $this->db->prepare('SELECT * FROM products WHERE id = ?');
        $stm->execute([$id]);
        return $stm->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stm = $this->db->prepare('INSERT INTO products (category_id, sku, name, description, price, stock, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
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
        $stm = $this->db->prepare('UPDATE products SET category_id = ?, sku = ?, name = ?, description = ?, price = ?, stock = ?, status = ?, updated_at = NOW() WHERE id = ?');
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
}