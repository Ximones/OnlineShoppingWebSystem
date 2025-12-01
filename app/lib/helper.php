<?php

function is_get(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

function is_post(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function get($key, $default = null) {
    $value = $_GET[$key] ?? $default;
    return is_array($value) ? array_map('trim', $value) : trim((string) $value);
}

function post($key, $default = null) {
    $value = $_POST[$key] ?? $default;
    return is_array($value) ? array_map('trim', $value) : trim((string) $value);
}

function req($key, $default = null) {
    $value = $_REQUEST[$key] ?? $default;
    return is_array($value) ? array_map('trim', $value) : trim((string) $value);
}

function redirect($path = null) {
    $path = $path ?? $_SERVER['REQUEST_URI'];
    header("Location: $path");
    exit;
}

function temp(string $key, $value = null) {
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
        return;
    }

    if (isset($_SESSION["temp_$key"])) {
        $value = $_SESSION["temp_$key"];
        unset($_SESSION["temp_$key"]);
        return $value;
    }

    return null;
}

function flash(string $type, string $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$type] = $message;
        return;
    }

    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }

    return null;
}

function asset(string $path): string {
    return APP_BASE_URL . ltrim($path, '/');
}

function csrf_token(): string {
    if (!isset($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_verify() {
    if (!hash_equals($_SESSION['_csrf_token'] ?? '', post('_token', ''))) {
        throw new RuntimeException('Invalid CSRF token.');
    }
}

function array_pluck(array $items, string $key, string $value): array {
    $result = [];
    foreach ($items as $item) {
        $result[$item[$key]] = $item[$value];
    }
    return $result;
}

$GLOBALS['_genders'] = [
    'F' => 'Female',
    'M' => 'Male',
];


