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
    private array $checkInPoints = [
        1 => 1,
        2 => 5,
        3 => 10,
        4 => 15,
        5 => 20,
        6 => 25,
        7 => 100,
    ];

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
        $this->renderProfilePage($user, $savedAddresses);
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
        $this->renderProfilePage($user);
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
        $this->renderProfilePage($user);
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

    public function check_in(): void
    {
        $this->requireAuth();
        if (!is_post()) {
            redirect('?module=profile&action=index');
        }

        $result = $this->users->recordDailyCheckIn(auth_id());
        if ($result['status'] === 'checked_in') {
            flash('success', sprintf('Checked in! You earned %d reward points.', $result['points']));
        } elseif ($result['status'] === 'already_checked_in') {
            flash('info', 'You already checked in today. Come back tomorrow for more points.');
        } else {
            flash('danger', 'Unable to process your check-in. Please try again.');
        }

        redirect('?module=profile&action=index');
    }

    private function renderProfilePage(?array $user, ?array $savedAddresses = null): void
    {
        $savedAddresses ??= $this->savedAddresses->findByUser(auth_id());
        $checkInPoints = $this->checkInPoints;
        $this->render('profile/index', compact('user', 'savedAddresses', 'checkInPoints'));
    }
}


