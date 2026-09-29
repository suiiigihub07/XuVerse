<?php

require_once __DIR__ . '/functions.php';

function xuverse_upload_audio($field, &$error, $required = true)
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        if ($required) {
            $error = 'Please choose an audio file.';
        }

        return '';
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'The audio upload failed.';
        return '';
    }

    $allowedExtensions = ['mp3', 'wav', 'ogg', 'm4a'];
    $allowedMimes = [
        'audio/mpeg',
        'audio/wav',
        'audio/x-wav',
        'audio/ogg',
        'audio/mp4',
        'audio/x-m4a'
    ];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);

    if (!in_array($extension, $allowedExtensions, true) || !in_array($mime, $allowedMimes, true)) {
        $error = 'Allowed audio formats: MP3, WAV, OGG, M4A.';
        return '';
    }

    if ($file['size'] > 25 * 1024 * 1024) {
        $error = 'Audio files must be 25MB or smaller.';
        return '';
    }

    $uploadDir = __DIR__ . '/../uploads/music/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    $target = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        $error = 'Could not save the audio file.';
        return '';
    }

    return 'uploads/music/' . $filename;
}

function xuverse_delete_local_file($path)
{
    $path = trim((string)$path);

    if ($path === '' || preg_match('/^(https?:)?\/\//', $path)) {
        return;
    }

    require_once __DIR__ . '/media_helpers.php';
    xuverse_delete_uploaded_file($path);
}
