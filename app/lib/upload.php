<?php

function handle_upload(string $field, array $options = []): ?string {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Failed to upload file.');
    }

    $allowed = $options['allowed'] ?? ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array(mime_content_type($file['tmp_name']), $allowed, true)) {
        throw new RuntimeException('Invalid file type.');
    }

    $name = uniqid('upload_', true) . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file['name']);
    $path = APP_UPLOAD_PATH . $name;

    if (!is_dir(APP_UPLOAD_PATH)) {
        mkdir(APP_UPLOAD_PATH, 0777, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $path)) {
        throw new RuntimeException('Cannot move uploaded file.');
    }

    return APP_UPLOAD_URL . $name;
}


