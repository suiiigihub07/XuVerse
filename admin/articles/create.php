<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';
require_once '../../includes/media_helpers.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        $error = "Title and content are required.";
    }

    $imagePath = null;

    if ($error === '') {
        $imagePath = xuverse_upload_image('article_image', $error, false, 'articles');
    }

    if ($error === '') {
        $stmt = $conn->prepare(
            "INSERT INTO articles
            (title, content, image_path)
            VALUES (?, ?, ?)"
        );

        $stmt->bind_param("sss", $title, $content, $imagePath);
        $stmt->execute();

        header("Location: index.php");
        exit;
    }
}

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Create Article - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include '../../includes/header.php';
include '../../includes/navbar.php';

?>

<section class="section admin-page">
<div class="container">
<a class="text-link admin-back-link" href="index.php">Back to articles</a>

<h1>Create Article</h1>

<?php if($error !== ''): ?>
<p class="error" role="alert"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="admin-form">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">

<div class="input-group">
<label for="field-title-1">Title</label>
<input id="field-title-1"
type="text"
name="title"
value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
required>
</div>

<div class="input-group">
<label for="field-article-image-2">Article Image</label>
<input id="field-article-image-2"
type="file"
name="article_image"
accept=".jpg,.jpeg,.png">
</div>

<div class="input-group">
<label for="field-content-3">Content</label>
<textarea id="field-content-3"
name="content"
rows="10"
aria-describedby="article-content-help"
required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
<p id="article-content-help" class="admin-help">Leave a blank line between paragraphs. Start a heading with ##, a subheading with ###, or a bullet with - followed by a space. Text is formatted automatically; HTML is not needed.</p>
</div>

<div class="admin-form-actions">
<button class="btn" type="submit">
Publish Article
</button>
<a href="index.php" class="btn secondary-btn">Cancel</a>
</div>
</form>

</div>
</section>

<?php include '../../includes/footer.php'; ?>
