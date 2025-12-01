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
        $products = $this->products->all(['status' => 'active']);
        $categories = $this->categories->all();
        $this->render('shop/home', compact('products', 'categories'));
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


