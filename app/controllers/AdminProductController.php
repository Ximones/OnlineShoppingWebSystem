<?php

namespace App\Controllers;

use App\Core\AdminController;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPhoto;
use RuntimeException;

class AdminProductController extends AdminController
{
    private Product $products;
    private Category $categories;
    private ProductPhoto $productPhotos;

    public function __construct()
    {
        $this->products = new Product();
        $this->categories = new Category();
        $this->productPhotos = new ProductPhoto();
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

        // Add primary photo path to each product
        foreach ($products as &$product) {
            $primaryPhoto = $this->productPhotos->getPrimaryPhoto($product['id']);
            $product['primary_photo_path'] = $primaryPhoto['photo_path'] ?? null;
        }

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
            $productId = $this->products->create([
                'name' => post('name'),
                'sku' => post('sku'),
                'description' => post('description'),
                'price' => post('price'),
                'stock' => post('stock'),
                'category_id' => post('category_id'),
                'status' => post('status', 'active'),
            ]);

            $this->handlePhotoUploads($productId);

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
                $this->products->update($id, [
                    'name' => post('name'),
                    'sku' => post('sku'),
                    'description' => post('description'),
                    'price' => post('price'),
                    'stock' => post('stock'),
                    'category_id' => post('category_id'),
                    'status' => post('status', 'active'),
                ]);

                $this->handlePhotoUploads($id);

                flash('success', 'Product updated.');
                redirect('?module=admin&resource=products&action=index');
            }
        }

        $categories = $this->categories->all();
        $photos = $this->productPhotos->getByProductId($id);
        $this->render('admin/products/form', compact('product', 'categories', 'photos'));
    }

    public function delete(): void
    {
        $this->requireAdmin();
        $id = (int) post('id');
        $this->products->delete($id);
        flash('success', 'Product deleted.');
        redirect('?module=admin&resource=products&action=index');
    }

    public function batchDelete(): void
    {
        $this->requireAdmin();
        $ids = array_map('intval', post('ids', []));
        if (empty($ids)) {
            flash('danger', 'No products selected.');
            redirect('?module=admin&resource=products&action=index');
        }
        $this->products->batchDelete($ids);
        flash('success', count($ids) . ' product(s) deleted.');
        redirect('?module=admin&resource=products&action=index');
    }

    public function deletePhoto(): void
    {
        $this->requireAdmin();
        $photoId = (int) post('photo_id');
        $productId = (int) post('product_id');

        $this->productPhotos->delete($photoId);
        flash('success', 'Photo deleted.');
        redirect("?module=admin&resource=products&action=edit&id=$productId");
    }

    public function setPrimaryPhoto(): void
    {
        $this->requireAdmin();
        $photoId = (int) post('photo_id');
        $productId = (int) post('product_id');

        $this->productPhotos->setPrimary($photoId, $productId);

        flash('success', 'Primary photo updated.');
        redirect("?module=admin&resource=products&action=edit&id=$productId");
    }

    private function handlePhotoUploads(int $productId): void
    {
        if (empty($_FILES['photos']['name'][0])) {
            return;
        }

        $photos = $_FILES['photos'];

        // Check if product already has photos
        $existingPhotos = $this->productPhotos->getByProductId($productId);
        $isPrimary = empty($existingPhotos); // only first photo primary if no existing photos

        for ($i = 0; $i < count($photos['name']); $i++) {
            if (empty($photos['name'][$i])) {
                continue;
            }

            // Temporarily set single file for handle_upload
            $_FILES['temp_photo'] = [
                'name' => $photos['name'][$i],
                'type' => $photos['type'][$i],
                'tmp_name' => $photos['tmp_name'][$i],
                'error' => $photos['error'][$i],
                'size' => $photos['size'][$i],
            ];

            try {
                $photoPath = handle_upload('temp_photo');
                $this->productPhotos->create($productId, $photoPath, $isPrimary);
                $isPrimary = false; // only the very first uploaded photo can be primary
            } catch (\RuntimeException $ex) {
                flash('danger', $ex->getMessage());
            }
        }
    }
}
