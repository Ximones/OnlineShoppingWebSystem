<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Product;

class AdminProductController extends Controller
{
    private Product $products;
    private Category $categories;

    public function __construct()
    {
        $this->products = new Product();
        $this->categories = new Category();
    }

    public function index(): void
    {
        $this->requireAdmin();
        $filters = [
            'keyword' => get('keyword', ''),
            'status' => get('status', ''),
            'category_id' => get('category_id', ''),
        ];
        $products = $this->products->all($filters);
        $categories = $this->categories->all();
        $this->render('admin/products/index', compact('products', 'filters', 'categories'));
    }

    public function create(): void
    {
        $this->requireAdmin();
        if (is_post() && validate([
            'name' => ['required' => 'Name is required.'],
            'sku' => ['required' => 'SKU is required.'],
            'price' => ['required' => 'Price is required.'],
            'stock' => ['required' => 'Stock is required.'],
            'category_id' => ['required' => 'Category is required.'],
        ])) {
            $photo = null;
            if (!empty($_FILES['photo']['name'])) {
                try {
                    $photo = handle_upload('photo');
                } catch (RuntimeException $ex) {
                    flash('danger', $ex->getMessage());
                    redirect('?module=admin&resource=products&action=create');
                }
            }
            $this->products->create([
                'name' => post('name'),
                'sku' => post('sku'),
                'description' => post('description'),
                'price' => post('price'),
                'stock' => post('stock'),
                'category_id' => post('category_id'),
                'photo' => $photo,
                'status' => post('status', 'active'),
            ]);
            flash('success', 'Product created.');
            redirect('?module=admin&resource=products&action=index');
        }

        $categories = $this->categories->all();
        $this->render('admin/products/form', compact('categories'));
    }

    public function edit(): void
    {
        $this->requireAdmin();
        $id = (int) get('id');
        $product = $this->products->find($id);
        if (!$product) {
            flash('danger', 'Product not found.');
            redirect('?module=admin&resource=products&action=index');
        }

        if (is_post()) {
            if (validate([
                'name' => ['required' => 'Name is required.'],
                'sku' => ['required' => 'SKU is required.'],
                'price' => ['required' => 'Price is required.'],
                'stock' => ['required' => 'Stock is required.'],
                'category_id' => ['required' => 'Category is required.'],
            ])) {
                $photo = $product['photo'];
                if (!empty($_FILES['photo']['name'])) {
                    try {
                        $photo = handle_upload('photo');
                    } catch (RuntimeException $ex) {
                        flash('danger', $ex->getMessage());
                        redirect("?module=admin&resource=products&action=edit&id=$id");
                    }
                }
                $this->products->update($id, [
                    'name' => post('name'),
                    'sku' => post('sku'),
                    'description' => post('description'),
                    'price' => post('price'),
                    'stock' => post('stock'),
                    'category_id' => post('category_id'),
                    'photo' => $photo,
                    'status' => post('status', 'active'),
                ]);
                flash('success', 'Product updated.');
                redirect('?module=admin&resource=products&action=index');
            }
        }

        $categories = $this->categories->all();
        $this->render('admin/products/form', compact('product', 'categories'));
    }

    public function delete(): void
    {
        $this->requireAdmin();
        $id = (int) post('id');
        $this->products->delete($id);
        flash('success', 'Product deleted.');
        redirect('?module=admin&resource=products&action=index');
    }
}


