<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$error = '';
$categories = ['service', 'proof', 'goal', 'process'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $meta = trim($_POST['meta'] ?? '');
    $link_url = trim($_POST['link_url'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!in_array($category, $categories, true)) {
        $error = 'Choose a valid category.';
    } elseif ($link_url !== '' && (!filter_var($link_url, FILTER_VALIDATE_URL) || !in_array(strtolower((string)parse_url($link_url, PHP_URL_SCHEME)), ['http', 'https'], true))) {
        $error = 'Links must use HTTP or HTTPS.';
    } elseif ($title === '') {
        $error = 'Title is required.';
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO highlights
             (category, title, description, meta, link_url, display_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param("sssssii", $category, $title, $description, $meta, $link_url, $display_order, $is_active);
        $stmt->execute();

        header("Location: index.php");
        exit;
    }
}

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Add Highlight - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include '../../includes/header.php';
include '../../includes/navbar.php';

?>

<section class="section admin-page">
<div class="container">
<a class="text-link admin-back-link" href="index.php">Back to highlights</a>

<div class="section-heading">
<p class="page-kicker">CMS</p>
<h1>Add Highlight</h1>
</div>

<?php if($error !== ''): ?>
<p class="error" role="alert"><?= e($error) ?></p>
<?php endif; ?>

<form method="POST" class="admin-form">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">

<div class="input-group">
<label for="field-category-1">Category</label>
<select id="field-category-1" name="category" required>
<?php foreach($categories as $category): ?>
<option value="<?= e($category) ?>" <?= ($_POST['category'] ?? '') === $category ? 'selected' : '' ?>>
<?= ucfirst($category) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="input-group">
<label for="field-title-2">Title</label>
<input id="field-title-2" type="text" name="title" value="<?= e($_POST['title'] ?? '') ?>" required>
</div>

<div class="input-group">
<label for="field-description-3">Description</label>
<textarea id="field-description-3" name="description" rows="5"><?= e($_POST['description'] ?? '') ?></textarea>
</div>

<div class="input-group">
<label for="field-meta-4">Meta Label</label>
<input id="field-meta-4" type="text" name="meta" value="<?= e($_POST['meta'] ?? '') ?>">
</div>

<div class="input-group">
<label for="field-link-url-5">Link URL</label>
<input id="field-link-url-5" type="text" name="link_url" value="<?= e($_POST['link_url'] ?? '') ?>">
</div>

<div class="input-group">
<label for="field-display-order-6">Display Order</label>
<input id="field-display-order-6" type="number" name="display_order" value="<?= e($_POST['display_order'] ?? '0') ?>">
</div>

<label class="check-row">
<input type="checkbox" name="is_active" checked>
Active
</label>

<div class="admin-form-actions">
<button class="btn" type="submit">Save Highlight</button>
<a href="index.php" class="btn secondary-btn">Cancel</a>
</div>
</form>

</div>
</section>

<?php include '../../includes/footer.php'; ?>
