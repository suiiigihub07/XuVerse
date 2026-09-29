<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';
require_once '../../includes/media_helpers.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$type = $_POST['type'] ?? '';
$id = (int)($_POST['id'] ?? 0);

if (!xuverse_is_valid_media_type($type) || $id <= 0) {
    header("Location: index.php");
    exit;
}

if ($type === 'photo') {
    $stmt = $conn->prepare("SELECT image_path FROM photos WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    if ($item) {
        $delete = $conn->prepare("DELETE FROM photos WHERE id=?");
        $delete->bind_param("i", $id);
        $delete->execute();

        xuverse_delete_uploaded_file($item['image_path']);
    }

    header("Location: photos.php");
    exit;
}

$stmt = $conn->prepare("SELECT thumbnail_path FROM videos WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if ($item) {
    $delete = $conn->prepare("DELETE FROM videos WHERE id=?");
    $delete->bind_param("i", $id);
    $delete->execute();

    xuverse_delete_uploaded_file($item['thumbnail_path']);
}

header("Location: videos.php");
exit;
