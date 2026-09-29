<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$error = '';
$categories = ['service', 'proof', 'goal', 'process'];

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM highlights WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$highlight = $stmt->get_result()->fetch_assoc();

if (!$highlight) {
    header("Location: index.php");
    exit;
}

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
            "UPDATE highlights
             SET category=?,
                 title=?,
                 description=?,
                 meta=?,
                 link_url=?,
                 display_order=?,
                 is_active=?
             WHERE id=?"
        );

        $stmt->bind_param("sssssiii", $category, $title, $description, $meta, $link_url, $display_order, $is_active, $id);
        $stmt->execute();

        header("Location: index.php");
        exit;
    }
}

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Edit Highlight - XuVerse';
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
<h1>Edit Highlight</h1>
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
<option value="<?= e($category) ?>" <?= ($_POST['category'] ?? $highlight['category']) === $category ? 'selected' : '' ?>>
<?= ucfirst($category) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="input-group">
<label for="field-title-2">Title</label>
<input id="field-title-2" type="text" name="title" value="<?= e($_POST['title'] ?? $highlight['title']) ?>" required>
</div>

<div class="input-group">
<label for="field-description-3">Description</label>
<textarea id="field-description-3" name="description" rows="5"><?= e($_POST['description'] ?? $highlight['description']) ?></textarea>
</div>

<div class="input-group">
<label for="field-meta-4">Meta Label</label>
<input id="field-meta-4" type="text" name="meta" value="<?= e($_POST['meta'] ?? $highlight['meta']) ?>">
</div>

<div class="input-group">
<label for="field-link-url-5">Link URL</label>
<input id="field-link-url-5" type="text" name="link_url" value="<?= e($_POST['link_url'] ?? $highlight['link_url']) ?>">
</div>

<div class="input-group">
<label for="field-display-order-6">Display Order</label>
<input id="field-display-order-6" type="number" name="display_order" value="<?= e($_POST['display_order'] ?? $highlight['display_order']) ?>">
</div>

<label class="check-row">
<input type="checkbox" name="is_active" <?= (int)($_POST['is_active'] ?? $highlight['is_active']) === 1 ? 'checked' : '' ?>>
Active
</label>

<div class="admin-form-actions">
<button class="btn" type="submit">Update Highlight</button>
<a href="index.php" class="btn secondary-btn">Cancel</a>
</div>
</form>

</div>
</section>

<?php include '../../includes/footer.php'; ?>
