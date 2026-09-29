<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$result = $conn->query(
    "SELECT * FROM highlights
     ORDER BY category, display_order, id"
);

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Manage Highlights - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include '../../includes/header.php';
include '../../includes/navbar.php';

?>

<section class="section admin-page">
<div class="container">
<a class="text-link admin-back-link" href="../dashboard.php">Back to dashboard</a>

<div class="section-heading with-actions">
<div>
<p class="page-kicker">CMS</p>
<h1>Manage Highlights</h1>
<p>Edit services, proof points, goals and process cards used across the website.</p>
</div>

<a href="create.php" class="btn">Add Highlight</a>
</div>

<?php if($result && $result->num_rows > 0): ?>

<div class="admin-list">
<?php while($row = $result->fetch_assoc()): ?>
<article class="admin-row">
<div>
<p class="page-kicker"><?= e($row['category']) ?> <?= (int)$row['display_order'] ?></p>
<h2><?= e($row['title']) ?></h2>
<p><?= e(xuverse_excerpt($row['description'], 160)) ?></p>
</div>

<div class="admin-item-actions">
<a href="edit.php?id=<?= (int)$row['id'] ?>" class="btn secondary-btn">Edit</a>
<form method="post" action="delete.php" class="inline-delete" data-confirm="Delete this highlight?">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
<input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
<button type="submit" class="btn danger-btn">Delete</button>
</form>
</div>
</article>
<?php endwhile; ?>
</div>

<?php else: ?>

<div class="empty-state">
<h2>No Highlights Yet</h2>
<p>Add services, goals or proof cards to power the homepage sections.</p>
</div>

<?php endif; ?>

</div>
</section>

<?php include '../../includes/footer.php'; ?>
