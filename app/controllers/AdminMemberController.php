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
            'email' => ['required' => 'Email is required.', 'email' => 'Invalid email.'],
            'phone' => ['required' => 'Phone is required.'],
            'password' => ['required' => 'Password is required.', 'min:8' => 'Minimum 8 characters.'],
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
}


