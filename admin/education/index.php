<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';

$result = $conn->query(
    "SELECT * FROM education
     ORDER BY start_year DESC"
);

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Education - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<section class="section admin-page">
<div class="container">
<a class="text-link admin-back-link" href="../dashboard.php">Back to dashboard</a>
<div class="section-heading with-actions">
<div><h1>Education</h1><p>Manage qualifications and ongoing studies.</p></div>
<a href="create.php" class="btn">Add Education</a>
</div>
<?php if ($result->num_rows > 0): ?>
<div class="admin-list">
<?php while ($row = $result->fetch_assoc()): ?>
<article class="admin-row">
<div>
<h2><?= e($row['institution']) ?></h2>
<p><?= e($row['degree']) ?></p>
<p><?= (int)$row['start_year'] ?> &ndash; <?= $row['end_year'] ? (int)$row['end_year'] : 'Present' ?></p>
<p><?= e(xuverse_excerpt($row['description'], 220)) ?></p>
</div>
<div class="admin-item-actions">
<a href="edit.php?id=<?= (int)$row['id'] ?>" class="btn secondary-btn">Edit</a>
<form method="post" action="delete.php" class="inline-delete" data-confirm="Delete this education?">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
<input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
<button type="submit" class="btn danger-btn">Delete</button>
</form>
</div>
</article>
<?php endwhile; ?>
</div>
<?php else: ?>
<div class="empty-state"><h2>No education yet</h2><p>Add your first education to get started.</p></div>
<?php endif; ?>
</div>
</section>

<?php include '../../includes/footer.php'; ?>
