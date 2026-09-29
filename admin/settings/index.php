<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$settings = $conn->query(
    "SELECT * FROM settings
     LIMIT 1"
)->fetch_assoc();

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Site Settings - XuVerse';
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
<h1>Site Settings</h1>
<p>Review the main public copy and edit the personal website identity from one place.</p>
</div>

<?php if($settings): ?>
<a href="edit.php?id=<?= (int)$settings['id'] ?>" class="btn">Edit Settings</a>
<?php endif; ?>
</div>

<?php if($settings): ?>

<div class="card settings-summary">

<div>
<p><strong>Site Title</strong></p>
<p><?= e($settings['site_title']) ?></p>
</div>

<div>
<p><strong>Hero</strong></p>
<p><?= e($settings['hero_title']) ?> - <?= e($settings['hero_subtitle']) ?></p>
</div>

<div>
<p><strong>Description</strong></p>
<p><?= nl2br(e($settings['hero_description'])) ?></p>
</div>

<div>
<p><strong>Availability</strong></p>
<p><?= e($settings['availability_text'] ?? '') ?></p>
</div>

<div>
<p><strong>Current Focus</strong></p>
<p><?= nl2br(e($settings['current_focus'])) ?></p>
</div>

<div>
<p><strong>Contact Email</strong></p>
<p><?= e($settings['contact_email'] ?? '') ?></p>
</div>

</div>

<?php else: ?>

<div class="empty-state">
<h2>No Settings Row Found</h2>
<p>Create a settings row in the database so the public pages can render editable site content.</p>
</div>

<?php endif; ?>

</div>
</section>

<?php include '../../includes/footer.php'; ?>
