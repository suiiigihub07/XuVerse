<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';

$result = $conn->query(
    "SELECT * FROM skills
     ORDER BY category, skill_name"
);

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Skills - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<section class="section admin-page">
<div class="container">
<a class="text-link admin-back-link" href="../dashboard.php">Back to dashboard</a>
<div class="section-heading with-actions">
<div><h1>Skills</h1><p>Manage the skills and categories shown on your resume.</p></div>
<a href="create.php" class="btn">Add Skill</a>
</div>
<?php if ($result->num_rows > 0): ?>
<div class="admin-list">
<?php while ($row = $result->fetch_assoc()): ?>
<article class="admin-row">
<div>
<h2><?= e($row['skill_name']) ?></h2>
<p><?= e($row['category']) ?> &middot; <?= e($row['level']) ?></p>
</div>
<div class="admin-item-actions">
<a href="edit.php?id=<?= (int)$row['id'] ?>" class="btn secondary-btn">Edit</a>
<form method="post" action="delete.php" class="inline-delete" data-confirm="Delete this skill?">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
<input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
<button type="submit" class="btn danger-btn">Delete</button>
</form>
</div>
</article>
<?php endwhile; ?>
</div>
<?php else: ?>
<div class="empty-state"><h2>No skills yet</h2><p>Add your first skill to get started.</p></div>
<?php endif; ?>
</div>
</section>

<?php include '../../includes/footer.php'; ?>
