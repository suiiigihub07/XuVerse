<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';
require_once '../../includes/media_helpers.php';

$id = (int)($_GET['id'] ?? 0);
$error = '';

$stmt = $conn->prepare(
    "SELECT * FROM articles
     WHERE id=?"
);

$stmt->bind_param("i", $id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

if (!$article) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $imagePath = $article['image_path'];

    if ($title === '' || $content === '') {
        $error = "Title and content are required.";
    }

    $newImagePath = null;

    if ($error === '') {
        $newImagePath = xuverse_upload_image('article_image', $error, false, 'articles');

        if ($newImagePath) {
            $imagePath = $newImagePath;
        }
    }

    if ($error === '') {
        $stmt = $conn->prepare(
            "UPDATE articles
             SET title=?,
                 content=?,
                 image_path=?
             WHERE id=?"
        );

        $stmt->bind_param("sssi", $title, $content, $imagePath, $id);
        $stmt->execute();

        if ($newImagePath) {
            xuverse_delete_uploaded_file($article['image_path']);
        }

        header("Location: index.php");
        exit;
    }
}

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Edit Article - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include '../../includes/header.php';
include '../../includes/navbar.php';

?>

<section class="section admin-page">
<div class="container">
<a class="text-link admin-back-link" href="index.php">Back to articles</a>

<h1>Edit Article</h1>

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
value="<?= htmlspecialchars($_POST['title'] ?? $article['title']) ?>"
required>
</div>

<?php if(!empty($article['image_path'])): ?>
<div class="media-preview">
<img
src="../../<?= htmlspecialchars($article['image_path']) ?>"
alt="<?= htmlspecialchars($article['title']) ?>">
</div>
<?php endif; ?>

<div class="input-group">
<label for="field-article-image-2">Replace Article Image</label>
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
required><?= htmlspecialchars($_POST['content'] ?? $article['content']) ?></textarea>
<p id="article-content-help" class="admin-help">Leave a blank line between paragraphs. Start a heading with ##, a subheading with ###, or a bullet with - followed by a space. Text is formatted automatically; HTML is not needed.</p>
</div>

<div class="admin-form-actions">
<button class="btn" type="submit">
Update Article
</button>
<a href="index.php" class="btn secondary-btn">Cancel</a>
</div>
</form>

</div>
</section>

<?php include '../../includes/footer.php'; ?>
