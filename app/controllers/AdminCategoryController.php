<?php

namespace App\Controllers;

use App\Core\AdminController;
use App\Models\Category;
use function handle_upload;

class AdminCategoryController extends AdminController
{
    private Category $categories;

    public function __construct()
    {
        $this->categories = new Category();
    }

    public function index(): void
    {
        $this->requireAdmin();
        $categories = $this->categories->all();
        $this->render('admin/categories/index', compact('categories'));
    }

    public function create(): void
    {
        $this->requireAdmin();
        if (is_post() && validate([
            'name' => ['required' => 'Name is required.'],
        ])) {
            $imageUrl = null;
            if (!empty($_FILES['image']['name'])) {
                try {
                    $imageUrl = handle_upload('image');
                } catch (\RuntimeException $ex) {
                    flash('danger', $ex->getMessage());
                    $this->render('admin/categories/form');
                    return;
                }
            }

            $this->categories->create([
                'name' => post('name'),
                'description' => post('description'),
                'image_url' => $imageUrl,
            ]);

            flash('success', 'Category created.');
            redirect('?module=admin&resource=categories&action=index');
        }

        $this->render('admin/categories/form');
    }

    public function edit(): void
    {
        $this->requireAdmin();
        $id = (int) get('id');
        $category = $this->categories->find($id);
        if (!$category) {
            flash('danger', 'Category not found.');
            redirect('?module=admin&resource=categories&action=index');
        }

        if (is_post()) {
            if (validate([
                'name' => ['required' => 'Name is required.'],
            ])) {
                $data = [
                    'name' => post('name'),
                    'description' => post('description'),
                    'image_url' => $category['image_url'], // Keep existing image by default
                ];

                // Handle new image upload
                if (!empty($_FILES['image']['name'])) {
                    try {
                        $newImageUrl = handle_upload('image');
                        $data['image_url'] = $newImageUrl;
                    } catch (\RuntimeException $ex) {
                        flash('danger', $ex->getMessage());
                        $this->render('admin/categories/form', compact('category'));
                        return;
                    }
                }

                $this->categories->update($id, $data);

                flash('success', 'Category updated.');
                redirect('?module=admin&resource=categories&action=index');
            }
        }

        $this->render('admin/categories/form', compact('category'));
    }

    public function delete(): void
    {
        $this->requireAdmin();
        $id = (int) post('id');
        try {
            $this->categories->delete($id);
            flash('success', 'Category deleted.');
        } catch (\RuntimeException $ex) {
            flash('danger', $ex->getMessage());
        }
        redirect('?module=admin&resource=categories&action=index');
    }

    public function batchDelete(): void
    {
        $this->requireAdmin();
        $ids = array_map('intval', post('ids', []));
        if (empty($ids)) {
            flash('danger', 'No categories selected.');
            redirect('?module=admin&resource=categories&action=index');
        }
        $failed = $this->categories->batchDelete($ids);
        $successCount = count($ids) - count($failed);
        if ($successCount > 0) {
            flash('success', "$successCount category(ies) deleted.");
        }
        if (!empty($failed)) {
            $errors = array_map(fn($f) => "Category #{$f['id']}: {$f['error']}", $failed);
            flash('danger', implode('; ', $errors));
        }
        redirect('?module=admin&resource=categories&action=index');
    }
}

