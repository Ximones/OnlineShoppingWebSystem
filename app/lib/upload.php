<?php

/**
 * Handle file upload and store it under public/uploads, returning the public URL.
 *
 * This is the original filesystem-based behaviour:
 * - validate mime type
 * - move the uploaded file into APP_UPLOAD_PATH
 * - return APP_UPLOAD_URL . filename for use in <img src="...">
 */
function handle_upload(string $field, array $options = []): ?string
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Failed to upload file.');
    }

    // Allow common image uploads. First try mime type, then fall back to file extension.
    $mime = @mime_content_type($file['tmp_name']) ?: '';
    $allowedPrefix = $options['allowed_prefix'] ?? 'image/';
    $isImageByMime = ($mime !== '' && strpos($mime, $allowedPrefix) === 0);

    if (!$isImageByMime) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExt = $options['allowed_ext'] ?? ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowedExt, true)) {
            throw new RuntimeException('Invalid file type.');
        }
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

