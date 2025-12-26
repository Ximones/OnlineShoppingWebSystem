<?php

namespace App\Controllers;

use App\Core\AdminController;
use App\Models\User;

class AdminMemberController extends AdminController
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function index(): void
    {
        $this->requireAdmin();
        $keyword = get('keyword', '');
        $members = $this->users->listMembers($keyword);
        $this->render('admin/members/index', compact('members', 'keyword'));
    }

    public function show(): void
    {
        $this->requireAdmin();
        $id = (int) get('id');
        $member = $this->users->find($id);
        if (!$member) {
            flash('danger', 'Member not found.');
            redirect('?module=admin&resource=members&action=index');
        }
        $this->render('admin/members/show', compact('member'));
    }

    public function create(): void
    {
        $this->requireAdmin();
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
                    'role' => 'member',
                ]);
                flash('success', 'Member registered.');
                redirect('?module=admin&resource=members&action=index');
            }
        }
        $this->render('admin/members/create');
    }

    public function delete(): void
    {
        $this->requireAdmin();
        $id = (int) post('id');
        try {
            $this->users->deleteMember($id);
            flash('success', 'Member deleted.');
        } catch (\RuntimeException $ex) {
            flash('danger', $ex->getMessage());
        }
        redirect('?module=admin&resource=members&action=index');
    }

    public function batchDelete(): void
    {
        $this->requireAdmin();
        $ids = array_map('intval', post('ids', []));
        if (empty($ids)) {
            flash('danger', 'No members selected.');
            redirect('?module=admin&resource=members&action=index');
        }
        $failed = $this->users->batchDelete($ids);
        $successCount = count($ids) - count($failed);
        if ($successCount > 0) {
            flash('success', "$successCount member(s) deleted.");
        }
        if (!empty($failed)) {
            $errors = array_map(fn($f) => "Member #{$f['id']}: {$f['error']}", $failed);
            flash('danger', implode('; ', $errors));
        }
        redirect('?module=admin&resource=members&action=index');
    }

    public function block(): void
    {
        $this->requireAdmin();
        $id = (int) post('id');
        try {
            $this->users->block($id);
            flash('success', 'Member blocked successfully.');
        } catch (\RuntimeException $ex) {
            flash('danger', $ex->getMessage());
        }
        redirect('?module=admin&resource=members&action=index');
    }

    public function unblock(): void
    {
        $this->requireAdmin();
        $id = (int) post('id');
        try {
            $this->users->unblock($id);
            flash('success', 'Member unblocked successfully.');
        } catch (\RuntimeException $ex) {
            flash('danger', $ex->getMessage());
        }
        redirect('?module=admin&resource=members&action=index');
    }
}


