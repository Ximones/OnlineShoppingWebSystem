<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPhoto;
use App\Models\Favorite;

class ShopController extends Controller
{
    private Product $products;
    private Category $categories;
    private Favorite $favorites;

    public function __construct()
    {
        $this->products = new Product();
        $this->categories = new Category();
        $this->favorites = new Favorite();
    }
    
    public function home(): void
    
    {
        
        $topSellers = $this->products->getTopSellers(5);
        $allProducts = $this->products->all(['status' => 'active']);
         $toiletProducts = [];
         $accessoryProducts = [];
         $otherProducts = [];
         
         foreach ($allProducts as $product) {
            $catName = strtolower($product['category_name'] ?? '');

              // Brushes / cleaning bucket
               if (in_array($catName, ['cleaning', 'brushes'], true)) {
                $accessoryProducts[] = $product;
                continue;
             }
             
             if (in_array($catName, ['bidets', 'bidet', 'seats', 'mats'], true)) {
                 $otherProducts[] = $product;
                continue;
            }
        $toiletProducts[] = $product;
    }

    $toiletProducts = array_slice($toiletProducts, 0, 6);
    $accessoryProducts = array_slice($accessoryProducts, 0, 6);
    $otherProducts = array_slice($otherProducts, 0, 6);

    if (empty($toiletProducts) && !empty($allProducts)) {
        $toiletProducts = array_slice($allProducts, 0, 6);
    }

    $categories = $this->categories->all();

    $this->render('shop/home', [
        'topSellers' => $topSellers, 
        'toiletProducts' => $toiletProducts,
        'accessoryProducts' => $accessoryProducts,
        'otherProducts' => $otherProducts,
        'categories' => $categories,
    ]);
}

    public function catalog(): void
    {
        $minPriceRaw = get('min_price');
        $maxPriceRaw = get('max_price');
        $sort = get('sort', ''); 
        $page = (int)get('page', 1);
        if ($page < 1) $page = 1;
        
        $perPage = 12; 
        $offset = ($page - 1) * $perPage;
        
        $filters = [
            'keyword' => get('keyword', ''),
            'category_id' => get('category_id', ''),
            'min_price' => get('min_price'),
            'max_price' => get('max_price'),
            'sort' => $sort,
            'limit' => $perPage,
            'offset' => $offset
        ];

        $totalProducts = $this->products->countAll($filters); 
        $totalPages = ceil($totalProducts / $perPage);
        $products = $this->products->all($filters);
        $categories = $this->categories->all();
        $user = auth_user();

        if ($user && !empty($products)) {
            $userId = $user['id'];
            foreach ($products as &$product) {
                $product['is_favorited'] = $this->favorites->checkFavorite($userId, $product['id']);
            }
            unset($product);
        }
        
        if (get('ajax') === '1') {
            $productPhotoModel = new \App\Models\ProductPhoto();
            
            extract(compact('products', 'productPhotoModel', 'totalPages', 'page')); 
            ob_start();
            require __DIR__ . '/../views/shop/productgrid.php'; 
            echo ob_get_clean();
            exit;
        }
        
        $this->render('shop/catalog', compact('products', 'categories', 'filters', 'totalPages', 'page'));
    }

    public function detail(): void
    {
        $id = (int) get('id');
        $product = $this->products->find($id);

        if (!$product) {
            flash('danger', 'Product not found.');
            redirect('?module=shop&action=catalog');
        }

        $productPhotos = new ProductPhoto();
        $photos = $productPhotos->getByProductId($id);
        $isFavorited = false;
        $user = auth_user(); 
        
        if ($user) {
            $isFavorited = $this->favorites->checkFavorite($user['id'], $product['id']);
        }

        $reviewModel = new \App\Models\Review();
        $reviews = $reviewModel->forProduct($id);
        
        $this->render('shop/detail', compact('product', 'photos', 'isFavorited', 'reviews'));
    }
}
