<?php

namespace App\Controllers;

use App\Core\Controller; 
use App\Models\Favorite; 

class FavoriteController extends Controller
{
    private Favorite $favorites;

    public function __construct()
    {
        $this->favorites = new Favorite();
    }

    public function toggle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
        $user = auth_user();
        if (!$user) {
          
            flash('danger', 'Please login to continue.');

            http_response_code(401); 
            header('Content-Type: application/json');
            echo json_encode(['success' => false]);
            exit;
        }

        $userId = $user['id'];

        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);

        $productId = (int) ($data['product_id'] ?? 0); 

        if ($productId === 0) {
            http_response_code(400); 
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
            exit;
        }

        $isCurrentlyFavorited = $this->favorites->checkFavorite($userId, $productId);
        $success = false;
        $action = '';

        if ($isCurrentlyFavorited) {
          
            $success = $this->favorites->removeFavorite($userId, $productId);
            $action = 'removed';
        } else {
            
            $success = $this->favorites->addFavorite($userId, $productId);
            $action = 'added';
        }

     
        header('Content-Type: application/json');
        if ($success) {
            http_response_code(200);
            echo json_encode(['success' => true, 'action' => $action]);
        } else {
            http_response_code(500); 
            echo json_encode(['success' => false, 'message' => 'Failed to update database.']);
        }
        exit; 
    }
    
    public function index(): void
{
    $user = auth_user();
    if (!$user) {
        header('Location: ?module=auth&action=login'); 
        exit; 
    }

    $userId = $user['id'];

    $page = (int)get('page', 1);
    if ($page < 1) $page = 1;
    
    $perPage = 4; 
    $offset = ($page - 1) * $perPage;

    $totalFavorites = $this->favorites->countFavoritesByUserId($userId);
    $totalPages = ceil($totalFavorites / $perPage);
    $favoriteProducts = $this->favorites->getPaginatedFavoritesByUserId($userId, $perPage, $offset);

    if (get('ajax') === '1') {
        $this->render('favorites/index', [
            'favoriteProducts' => $favoriteProducts,
            'page' => $page,
            'totalPages' => $totalPages
        ], 'ajax_layout'); 
    }

    $this->render('favorites/index', [
        'favoriteProducts' => $favoriteProducts,
        'page' => $page,
        'totalPages' => $totalPages,
        'title' => 'My Favorites'
    ]);
}
}