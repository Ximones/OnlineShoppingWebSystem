<?php

namespace App\Controllers;

use App\Core\AdminController;
use App\Models\User;

class AdminAdminController extends AdminController
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function index(): void
    {
        $this->requireSuperAdmin();
        $keyword = get('keyword', '');
        $admins = $this->users->listAdmins($keyword);
        $this->render('admin/admins/index', compact('admins', 'keyword'));
    }

    public function show(): void
    {
        $this->requireSuperAdmin();
        $id = (int) get('id');
        $admin = $this->users->find($id);
        if (!$admin || ($admin['role'] !== 'admin' && $admin['role'] !== 'superadmin')) {
            flash('danger', 'Admin not found.');
            redirect('?module=admin&resource=admins&action=index');
        }
        $this->render('admin/admins/show', compact('admin'));
    }

    public function create(): void
    {
        $this->requireSuperAdmin();
        if (is_post() && validate([
            'name' => ['required' => 'Name is required.'],
            'email' => ['required' => 'Email is required.', 'email' => 'Email format is invalid.'],
            'phone' => ['required' => 'Phone is required.', 'phone' => 'Phone format is invalid. Please use Malaysian format (e.g., 012-3456789 or 0123456789).'],
            'password' => ['required' => 'Password is required.', 'min:8' => 'Password must be at least 8 characters.'],
        ])) {
            if ($this->users->findByEmail(post('email'))) {
                flash('danger', 'Email already exists.');
            } else {
                $this->users->create([
                    'name' => post('name'),
                    'email' => post('email'),
                    'phone' => post('phone'),
                    'password_hash' => password_hash(post('password'), PASSWORD_DEFAULT),
                    'role' => 'admin',
                ]);
                flash('success', 'Admin created successfully.');
                redirect('?module=admin&resource=admins&action=index');
            }
        }
        $this->render('admin/admins/create');
    }

    public function delete(): void
    {
        $this->requireSuperAdmin();
        $id = (int) post('id');
        try {
            $this->users->deleteAdmin($id);
            flash('success', 'Admin deleted successfully.');
        } catch (\RuntimeException $ex) {
            flash('danger', $ex->getMessage());
        }
        redirect('?module=admin&resource=admins&action=index');
    }

    public function batchDelete(): void
    {
        $this->requireSuperAdmin();
        $ids = array_map('intval', post('ids', []));
        if (empty($ids)) {
            flash('danger', 'No admins selected.');
            redirect('?module=admin&resource=admins&action=index');
        }
        $failed = [];
        foreach ($ids as $id) {
            try {
                $this->users->deleteAdmin($id);
            } catch (\RuntimeException $e) {
                $failed[] = ['id' => $id, 'error' => $e->getMessage()];
            }
        }
        $successCount = count($ids) - count($failed);
        if ($successCount > 0) {
            flash('success', "$successCount admin(s) deleted.");
        }
        if (!empty($failed)) {
            $errors = array_map(fn($f) => "Admin #{$f['id']}: {$f['error']}", $failed);
            flash('danger', implode('; ', $errors));
        }
        redirect('?module=admin&resource=admins&action=index');
    }

    public function block(): void
    {
        $this->requireSuperAdmin();
        $id = (int) post('id');
        try {
            $this->users->blockAdmin($id);
            flash('success', 'Admin blocked successfully.');
        } catch (\RuntimeException $ex) {
            flash('danger', $ex->getMessage());
        }
        redirect('?module=admin&resource=admins&action=index');
    }

    public function unblock(): void
    {
        $this->requireSuperAdmin();
        $id = (int) post('id');
        try {
            $this->users->unblock($id);
            flash('success', 'Admin unblocked successfully.');
        } catch (\RuntimeException $ex) {
            flash('danger', $ex->getMessage());
        }
        redirect('?module=admin&resource=admins&action=index');
    }
}

