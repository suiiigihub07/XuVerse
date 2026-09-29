<?php

function xuverse_project_root()
{
    return dirname(__DIR__);
}

function xuverse_orient_image($image, $orientation)
{
    // EXIF describes how the stored pixels must be transformed for display.
    $orientation = (int)$orientation;
    if (in_array($orientation, [2, 5, 7], true)) {
        imageflip($image, IMG_FLIP_HORIZONTAL);
    } elseif ($orientation === 4) {
        imageflip($image, IMG_FLIP_VERTICAL);
    }

    $angle = [3 => 180, 5 => 90, 6 => -90, 7 => -90, 8 => 90][$orientation] ?? 0;
    if ($angle !== 0) {
        $rotated = imagerotate($image, $angle, 0);
        if ($rotated !== false) {
            imagedestroy($image);
            $image = $rotated;
        }
    }

    return $image;
}

function xuverse_resize_upload_image($source, $maxDimension = 1600)
{
    $width = imagesx($source);
    $height = imagesy($source);
    $scale = min(1, $maxDimension / max($width, $height));
    $targetWidth = max(1, (int)round($width * $scale));
    $targetHeight = max(1, (int)round($height * $scale));
    $optimized = imagecreatetruecolor($targetWidth, $targetHeight);

    // Preserve transparent PNG backgrounds instead of compositing them on black.
    imagealphablending($optimized, false);
    imagesavealpha($optimized, true);
    imagefill($optimized, 0, 0, imagecolorallocatealpha($optimized, 0, 0, 0, 127));
    imagecopyresampled($optimized, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

    return $optimized;
}

function xuverse_upload_image($fieldName, &$error, $required = false, $folder = 'media')
{
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        if ($required) {
            $error = "Please choose an image to upload.";
        }

        return null;
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Upload failed. Please try again.";
        return null;
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        $error = "Images must be 2MB or smaller.";
        return null;
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        $error = 'Invalid upload.';
        return null;
    }
    $imageInfo = @getimagesize($file['tmp_name']);

    if ($imageInfo === false) {
        $error = "Only valid image files are allowed.";
        return null;
    }

    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png'
    ];

    if (!isset($allowedMimes[$imageInfo['mime']]) || (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) !== $imageInfo['mime']) {
        $error = "Only JPG and PNG images are allowed.";
        return null;
    }

    if ($imageInfo[0] * $imageInfo[1] > 20000000) {
        $error = 'Images must contain no more than 20 megapixels.';
        return null;
    }

    $safeFolder = preg_replace('/[^a-zA-Z0-9_-]/', '', $folder);

    if ($safeFolder === '') {
        $safeFolder = 'media';
    }

    $uploadDir = xuverse_project_root() . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $safeFolder;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.webp';
    $relativePath = 'uploads/' . $safeFolder . '/' . $filename;
    $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;

    $source = $imageInfo['mime'] === 'image/png'
        ? imagecreatefrompng($file['tmp_name'])
        : imagecreatefromjpeg($file['tmp_name']);

    if (!$source) {
        $error = "Could not process the uploaded image.";
        return null;
    }

    if ($imageInfo['mime'] === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($file['tmp_name'], 'IFD0');
        $source = xuverse_orient_image($source, $exif['Orientation'] ?? 1);
    }

    $optimized = xuverse_resize_upload_image($source);

    $saved = imagewebp($optimized, $destination, 80);
    imagedestroy($optimized);
    imagedestroy($source);

    if (!$saved) {
        $error = "Could not save the optimized image.";
        return null;
    }

    return $relativePath;
}

function xuverse_delete_uploaded_file($relativePath)
{
    if (empty($relativePath)) {
        return;
    }

    $normalized = str_replace('\\', '/', $relativePath);

    if (strpos($normalized, 'uploads/') !== 0) {
        return;
    }

    $fullPath = xuverse_project_root() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalized);
    $rootPath = realpath(xuverse_project_root() . '/uploads');
    $targetPath = realpath($fullPath);

    if ($rootPath && $targetPath && strpos($targetPath, $rootPath . DIRECTORY_SEPARATOR) === 0 && is_file($targetPath)) {
        unlink($targetPath);
    }
}

function xuverse_is_valid_media_type($type)
{
    return in_array($type, ['photo', 'video'], true);
}
