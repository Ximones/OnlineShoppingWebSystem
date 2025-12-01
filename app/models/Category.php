<?php

namespace App\Models;

use PDO;
use function db;

class Category
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function all(): array
    {
        $stm = $this->db->query('SELECT * FROM categories ORDER BY name');
        return $stm->fetchAll();
    }
}


