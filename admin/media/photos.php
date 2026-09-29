<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';

$result = $conn->query(
    "SELECT * FROM photos
     ORDER BY uploaded_at DESC"
);

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Photos - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include '../../includes/header.php';
include '../../includes/navbar.php';

?>

<section class="section admin-page">
<div class="container">
<a class="text-link admin-back-link" href="index.php">Back to media</a>

<div class="section-heading with-actions">
<div>
<h1>Photos</h1>
<p>Upload, edit and remove photography entries.</p>
</div>

<a href="create.php?type=photo" class="btn">Upload Photo</a>
</div>

<?php if($result && $result->num_rows > 0): ?>

<div class="admin-list">

<?php while($row = $result->fetch_assoc()): ?>

<article class="card admin-media-item">

<?php if(!empty($row['image_path'])): ?>
<img
src="../../<?= htmlspecialchars($row['image_path']) ?>"
alt="<?= htmlspecialchars($row['title']) ?>">
<?php endif; ?>

<div>
<h2><?= htmlspecialchars($row['title']) ?></h2>
<p><?= htmlspecialchars($row['description'] ?? '') ?></p>
<p><?= htmlspecialchars($row['uploaded_at'] ?? '') ?></p>
</div>

<div class="admin-item-actions">
<a href="edit.php?type=photo&id=<?= $row['id'] ?>" class="btn secondary-btn">Edit</a>
<form method="post" action="delete.php" class="inline-delete" data-confirm="Delete photo?">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
<input type="hidden" name="type" value="photo">
<input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
<button type="submit" class="btn danger-btn">Delete</button>
</form>
</div>

</article>

<?php endwhile; ?>

</div>

<?php else: ?>

<div class="empty-state">
<h2>No Photos Yet</h2>
<p>Start by uploading your first photo.</p>
</div>

<?php endif; ?>

</div>
</section>

<?php include '../../includes/footer.php'; ?>
