<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\PasswordReset;
use App\Models\User;

class AuthController extends Controller
{
    private User $users;
    private PasswordReset $resets;

    public function __construct()
    {
        $this->users = new User();
        $this->resets = new PasswordReset();
    }

    public function login(): void
    {
        if (is_post() && validate([
            'email' => ['required' => 'Email is required.', 'email' => 'Email format invalid.'],
            'password' => ['required' => 'Password is required.'],
        ])) {
            $user = $this->users->findByEmail(post('email'));
            if (!$user || !password_verify(post('password'), $user['password_hash'])) {
                flash('danger', 'Invalid credentials.');
            } else {
                auth_login($user);
                flash('success', 'Welcome back, ' . $user['name'] . '!');
                redirect('?module=shop&action=home');
            }
        }

        $this->render('auth/login');
    }

    public function logout(): void
    {
        auth_logout();
        flash('success', 'You have been logged out.');
        redirect('?module=shop&action=home');
    }

    public function register(): void
    {
        if (is_post() && validate([
            'name' => ['required' => 'Name is required.'],
            'email' => ['required' => 'Email is required.', 'email' => 'Email format invalid.'],
            'phone' => ['required' => 'Phone is required.'],
            'password' => ['required' => 'Password is required.', 'min:8' => 'Password must be at least 8 characters.'],
            'confirm_password' => ['same:password' => 'Passwords do not match.'],
        ])) {
            if ($this->users->findByEmail(post('email'))) {
                flash('danger', 'Email already registered.');
            } else {
                $id = $this->users->create([
                    'name' => post('name'),
                    'email' => post('email'),
                    'phone' => post('phone'),
                    'password_hash' => password_hash(post('password'), PASSWORD_DEFAULT),
                    'role' => 'member',
                ]);
                $user = $this->users->find($id);
                auth_login($user);
                flash('success', 'Registration successful.');
                redirect('?module=profile&action=index');
            }
        }

        $this->render('auth/register');
    }

    public function forgot(): void
    {
        if (is_post() && validate([
            'email' => ['required' => 'Email is required.', 'email' => 'Email format invalid.'],
        ])) {
            $user = $this->users->findByEmail(post('email'));
            if ($user) {
                $token = $this->resets->create($user['id']);
                // For template purposes we simply flash the token.
                flash('success', "Reset token: $token");
            } else {
                flash('danger', 'Account not found.');
            }
        }

        $this->render('auth/forgot');
    }

    public function reset(): void
    {
        $token = get('token', '');
        if (!$token) {
            flash('danger', 'Reset token missing.');
            redirect('?module=auth&action=forgot');
        }

        if (is_post() && validate([
            'password' => ['required' => 'Password is required.', 'min:8' => 'Password too short.'],
            'confirm_password' => ['same:password' => 'Passwords do not match.'],
        ])) {
            $row = $this->resets->consume($token);
            if (!$row) {
                flash('danger', 'Invalid or expired token.');
                redirect('?module=auth&action=forgot');
            } else {
                $this->users->updatePassword($row['user_id'], password_hash(post('password'), PASSWORD_DEFAULT));
                flash('success', 'Password updated. You can login now.');
                redirect('?module=auth&action=login');
            }
        }

        $this->render('auth/reset', compact('token'));
    }
}


