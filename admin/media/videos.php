<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';

$result = $conn->query(
    "SELECT * FROM videos
     ORDER BY uploaded_at DESC"
);

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Videos - XuVerse';
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
<h1>Videos</h1>
<p>Add video links and manage their thumbnails.</p>
</div>

<a href="create.php?type=video" class="btn">Add Video</a>
</div>

<?php if($result && $result->num_rows > 0): ?>

<div class="admin-list">

<?php while($row = $result->fetch_assoc()): ?>

<article class="card admin-media-item">

<?php if(!empty($row['thumbnail_path'])): ?>
<img
src="../../<?= htmlspecialchars($row['thumbnail_path']) ?>"
alt="<?= htmlspecialchars($row['title']) ?>">
<?php else: ?>
<div class="admin-thumb-placeholder">Video</div>
<?php endif; ?>

<div>
<p class="page-kicker"><?= !empty($row['is_published']) ? 'Published' : 'Draft' ?></p>
<h2><?= htmlspecialchars($row['title']) ?></h2>
<p><?= htmlspecialchars($row['description'] ?? '') ?></p>
<p>
<a
href="<?= htmlspecialchars($row['video_url']) ?>"
target="_blank"
rel="noopener noreferrer">
<?= htmlspecialchars($row['video_url']) ?>
</a>
</p>
</div>

<div class="admin-item-actions">
<a href="edit.php?type=video&id=<?= $row['id'] ?>" class="btn secondary-btn">Edit</a>
<form method="post" action="delete.php" class="inline-delete" data-confirm="Delete video?">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
<input type="hidden" name="type" value="video">
<input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
<button type="submit" class="btn danger-btn">Delete</button>
</form>
</div>

</article>

<?php endwhile; ?>

</div>

<?php else: ?>

<div class="empty-state">
<h2>No Videos Yet</h2>
<p>Add your first video link to start building the media page.</p>
</div>

<?php endif; ?>

</div>
</section>

<?php include '../../includes/footer.php'; ?>
