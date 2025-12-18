<?php

namespace App\Models;

use PDO;
use function db; // Use the global db() function to get the PDO instance

class Favorite
{
    private PDO $db;
    private string $table = 'user_favorites'; // Define the table name here

    public function __construct()
    {
        $this->db = db(); // Initialize PDO connection via helper function
    }

    /**
     * Checks if a specific product has been favorited by a user.
     * @param int $userId
     * @param int $productId
     * @return bool True if the favorite record exists.
     */
    public function checkFavorite(int $userId, int $productId): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE user_id = :user_id AND product_id = :product_id LIMIT 1";
        $stm = $this->db->prepare($sql);
        
        $stm->execute([
            'user_id' => $userId, 
            'product_id' => $productId
        ]);

        return $stm->fetchColumn() !== false; // Returns true if a row was found
    }

    /**
     * Adds a product to the user's favorites list.
     * @param int $userId
     * @param int $productId
     * @return int|bool The ID of the new favorite record or false on failure.
     */
    public function addFavorite(int $userId, int $productId): int|bool
    {
        // Note: The database's UNIQUE KEY constraint handles preventing duplicates.
        $sql = "INSERT INTO {$this->table} (user_id, product_id) VALUES (:user_id, :product_id)";
        $stm = $this->db->prepare($sql);
        
        if ($stm->execute([
            'user_id' => $userId,
            'product_id' => $productId
        ])) {
            // Return the ID of the newly inserted row
            return (int) $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Removes a product from the user's favorites list.
     * @param int $userId
     * @param int $productId
     * @return bool True on successful deletion.
     */
    public function removeFavorite(int $userId, int $productId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id AND product_id = :product_id LIMIT 1";
        $stm = $this->db->prepare($sql);
        
        return $stm->execute([
            'user_id' => $userId, 
            'product_id' => $productId
        ]);
    }
    
    /**
     * Retrieves all favorited products for a given user, joining product data.
     * @param int $userId
     * @return array An array of product details.
     */
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
}