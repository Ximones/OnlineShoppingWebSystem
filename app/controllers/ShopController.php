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
        foreach ($allProducts as $product) {
            if (strcasecmp($product['category_name'] ?? '', 'Accessories') === 0) {
                $accessoryProducts[] = $product;
            } else {
                $toiletProducts[] = $product;
            }
        }

        // Only show latest few items in each section
        $toiletProducts = array_slice($toiletProducts, 0, 6);
        $accessoryProducts = array_slice($accessoryProducts, 0, 6);

        // Fallback: if we somehow have no toilet products but do have items,
        // show the latest overall products so the homepage is never empty.
        if (empty($toiletProducts) && !empty($allProducts)) {
            $toiletProducts = array_slice($allProducts, 0, 6);
        }

        $categories = $this->categories->all();
        $this->render('shop/home', [
            'toiletProducts' => $toiletProducts,
            'accessoryProducts' => $accessoryProducts,
            'categories' => $categories,
        ]);
    }

    public function catalog(): void
    {
        $filters = [
            'keyword' => get('keyword', ''),
            'category_id' => get('category_id', ''),
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


