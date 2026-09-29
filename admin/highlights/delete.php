<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$id = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM highlights WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: index.php");
exit;
