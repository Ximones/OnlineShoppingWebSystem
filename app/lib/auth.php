<?php

function auth_user(): ?array {
    return $_SESSION['auth_user'] ?? null;
}

function auth_id(): ?int {
    return auth_user()['id'] ?? null;
}

function auth_role(): ?string {
    return auth_user()['role'] ?? null;
}

function auth_login(array $user): void {
    $_SESSION['auth_user'] = [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'avatar' => $user['avatar'] ?? null,
    ];
}

function auth_logout(): void {
    unset($_SESSION['auth_user']);
}

function is_admin(): bool {
    return auth_role() === 'admin';
}

function require_auth(): void {
    if (!auth_user()) {
        flash('danger', 'Please login to continue.');
        redirect('?module=auth&action=login');
    }
}

function require_role(array $roles): void {
    require_auth();
    if (!in_array(auth_role(), $roles, true)) {
        flash('danger', 'You are not authorized to access this page.');
        redirect('?module=shop&action=home');
    }
}


