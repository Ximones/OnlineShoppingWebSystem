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
        $user = auth_user();
        if (!$user) {
          
            http_response_code(401); 
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Login required to favorite items.']);
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

        if (!auth_user()) {
            
            header('Location: ?module=auth&action=login'); 

            exit; 
    
        }

        $userId = auth_user()['id'];
        
     
        $favoriteProducts = $this->favorites->getFavoritesByUserId($userId);

       
        $this->render('favorites/index', [
            'favoriteProducts' => $favoriteProducts,
            'title' => 'My Favorites'
        ]);
    }
}