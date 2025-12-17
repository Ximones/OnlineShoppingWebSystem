<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\SavedAddress;
use App\Models\User;
use RuntimeException;

class ProfileController extends Controller
{
    private User $users;
    private SavedAddress $savedAddresses;

    public function __construct()
    {
        $this->users = new User();
        $this->savedAddresses = new SavedAddress();
    }

    public function index(): void
    {
        $this->requireAuth();
        $userId = auth_id();
        $user = $this->users->find($userId);
        $savedAddresses = $this->savedAddresses->findByUser($userId);
        $this->render('profile/index', compact('user', 'savedAddresses'));
    }

    public function update(): void
    {
        $this->requireAuth();
        if (is_post() && validate([
            'name' => ['required' => 'Name is required.'],
            'phone' => ['required' => 'Phone is required.'],
            'address' => ['required' => 'Address is required.'],
        ])) {
            $this->users->updateProfile(auth_id(), [
                'name' => post('name'),
                'phone' => post('phone'),
                'address' => post('address'),
            ]);
            flash('success', 'Profile updated.');
            redirect('?module=profile&action=index');
        }

        $user = $this->users->find(auth_id());
        $this->render('profile/index', compact('user'));
    }

    public function password(): void
    {
        $this->requireAuth();
        if (is_post() && validate([
            'current_password' => ['required' => 'Current password is required.'],
            'password' => ['required' => 'New password is required.', 'min:8' => 'At least 8 characters.'],
            'confirm_password' => ['same:password' => 'Passwords do not match.'],
        ])) {
            $user = $this->users->find(auth_id());
            if (!password_verify(post('current_password'), $user['password_hash'])) {
                flash('danger', 'Current password incorrect.');
            } else {
                $this->users->updatePassword(auth_id(), password_hash(post('password'), PASSWORD_DEFAULT));
                flash('success', 'Password updated.');
                redirect('?module=profile&action=index');
            }
        }
        $user = $this->users->find(auth_id());
        $this->render('profile/index', compact('user'));
    }

    public function photo(): void
    {
        $this->requireAuth();
        if ($_FILES['avatar']['name'] ?? false) {
            try {
                $path = handle_upload('avatar');
                $this->users->updateAvatar(auth_id(), $path);
                flash('success', 'Profile photo updated.');
            } catch (RuntimeException $ex) {
                flash('danger', $ex->getMessage());
            }
        }
        redirect('?module=profile&action=index');
    }

    public function save_address(): void
    {
        $this->requireAuth();
        if (is_post() && validate([
            'label' => ['required' => 'Address label is required.'],
            'name' => ['required' => 'Recipient name is required.'],
            'phone' => ['required' => 'Phone is required.'],
            'address' => ['required' => 'Address is required.'],
        ])) {
            $this->savedAddresses->create([
                'user_id' => auth_id(),
                'label' => post('label'),
                'name' => post('name'),
                'phone' => post('phone'),
                'address' => post('address'),
                'is_default' => post('is_default') ? 1 : 0,
            ]);
            flash('success', 'Address saved.');
        }
        redirect('?module=profile&action=index');
    }

}


