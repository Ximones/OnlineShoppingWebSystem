<?php

namespace App\Models;

use PDO;
use function db; 

class Favorite
{
    private PDO $db;
    private string $table = 'user_favorites'; 
    public function __construct()
    {
        $this->db = db(); 
    }

    public function checkFavorite(int $userId, int $productId): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE user_id = :user_id AND product_id = :product_id LIMIT 1";
        $stm = $this->db->prepare($sql);
        
        $stm->execute([
            'user_id' => $userId, 
            'product_id' => $productId
        ]);

        return $stm->fetchColumn() !== false; 
    }

  
    public function addFavorite(int $userId, int $productId): int|bool
    {
    
        $sql = "INSERT INTO {$this->table} (user_id, product_id) VALUES (:user_id, :product_id)";
        $stm = $this->db->prepare($sql);
        
        if ($stm->execute([
            'user_id' => $userId,
            'product_id' => $productId
        ])) {
           
            return (int) $this->db->lastInsertId();
        }
        return false;
    }

 
    public function removeFavorite(int $userId, int $productId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id AND product_id = :product_id LIMIT 1";
        $stm = $this->db->prepare($sql);
        
        return $stm->execute([
            'user_id' => $userId, 
            'product_id' => $productId
        ]);
    }
    
    
   public function getFavoritesByUserId(int $userId): array
    {
        $sql = "
            SELECT 
                p.*, 
                pp.photo_path,
                uf.created_at AS favorited_at 
            FROM products p
            JOIN {$this->table} uf ON p.id = uf.product_id
            LEFT JOIN product_photos pp ON p.id = pp.product_id AND pp.is_primary = 1
            WHERE uf.user_id = :user_id
            ORDER BY uf.created_at DESC
        ";
        $stm = $this->db->prepare($sql);
        $stm->execute(['user_id' => $userId]);
        
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function countFavoritesByUserId(int $userId): int
    {
    $sql = "SELECT COUNT(*) FROM {$this->table} WHERE user_id = :user_id";
    $stm = $this->db->prepare($sql);
    $stm->execute(['user_id' => $userId]);
    
    return (int)$stm->fetchColumn();
}


public function getPaginatedFavoritesByUserId(int $userId, int $limit, int $offset): array
{
    $sql = "
        SELECT 
            p.*, 
            pp.photo_path,
            uf.created_at AS favorited_at 
        FROM products p
        JOIN {$this->table} uf ON p.id = uf.product_id
        LEFT JOIN product_photos pp ON p.id = pp.product_id AND pp.is_primary = 1
        WHERE uf.user_id = :user_id
        ORDER BY uf.created_at DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $stm = $this->db->prepare($sql);
    $stm->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stm->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stm->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stm->execute();
    
    return $stm->fetchAll(PDO::FETCH_ASSOC);
}
}