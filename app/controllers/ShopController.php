<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Product;

class ShopController extends Controller
{
    private Product $products;
    private Category $categories;

    public function __construct()
    {
        $this->products = new Product();
        $this->categories = new Category();
    }

    public function home(): void
    {
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

            // Other accessories bucket: bidets, seats, mats
            if (in_array($catName, ['bidets', 'bidet', 'seats', 'mats'], true)) {
                $otherProducts[] = $product;
                continue;
            }

            // Default: toilet bowls and other main fixtures
            $toiletProducts[] = $product;
        }

        // Only show latest few items in each section
        $toiletProducts = array_slice($toiletProducts, 0, 6);
        $accessoryProducts = array_slice($accessoryProducts, 0, 6);
        $otherProducts = array_slice($otherProducts, 0, 6);

        // Fallback: if we somehow have no toilet products but do have items,
        // show the latest overall products so the homepage is never empty.
        if (empty($toiletProducts) && !empty($allProducts)) {
            $toiletProducts = array_slice($allProducts, 0, 6);
        }

        $categories = $this->categories->all();
        $this->render('shop/home', [
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
        
        $filters = [
            'keyword' => get('keyword', ''),
            'category_id' => get('category_id', ''),
            'min_price' => (is_numeric($minPriceRaw) && $minPriceRaw >= 0) ? (float)$minPriceRaw : null,
            'max_price' => (is_numeric($maxPriceRaw) && $maxPriceRaw >= 0) ? (float)$maxPriceRaw : null,
        ];
        $products = $this->products->all($filters);
        $categories = $this->categories->all();
        $this->render('shop/catalog', compact('products', 'categories', 'filters'));
    }

    public function detail(): void
    {
        $id = (int) get('id');
        $product = $this->products->find($id);
        if (!$product) {
            flash('danger', 'Product not found.');
            redirect('?module=shop&action=catalog');
        }
        $this->render('shop/detail', compact('product'));
    }
}


