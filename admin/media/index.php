<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Media Manager - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include '../../includes/header.php';
include '../../includes/navbar.php';

?>

<section class="section admin-page">

<div class="container">
<a class="text-link admin-back-link" href="../dashboard.php">Back to dashboard</a>

<h1>Media Manager</h1>

<p>
Manage photos and videos displayed on XuVerse.
</p>



<div class="quick-links">

<a href="photos.php">
    <h2>Manage Photos</h2>
    <p>Upload and organize photography.</p>
</a>

<a href="videos.php">
    <h2>Manage Videos</h2>
    <p>Manage YouTube videos and media content.</p>
</a>

</div>

</div>

</section>

<?php include '../../includes/footer.php'; ?>
