<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';
require_once '../../includes/media_helpers.php';

$type = $_GET['type'] ?? 'photo';
$error = '';

if (!xuverse_is_valid_media_type($type)) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '') {
        $error = "Title is required.";
    }

    if ($type === 'photo' && $error === '') {
        $imagePath = xuverse_upload_image('image', $error, true);

        if ($imagePath && $error === '') {
            $stmt = $conn->prepare(
                "INSERT INTO photos
                (title, description, image_path)
                VALUES (?, ?, ?)"
            );

            $stmt->bind_param("sss", $title, $description, $imagePath);
            $stmt->execute();

            header("Location: photos.php");
            exit;
        }
    }

    if ($type === 'video' && $error === '') {
        $videoUrl = trim($_POST['video_url'] ?? '');
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        if (!filter_var($videoUrl, FILTER_VALIDATE_URL) || !in_array(strtolower((string)parse_url($videoUrl, PHP_URL_SCHEME)), ['http', 'https'], true)) {
            $error = "Please enter a valid video URL.";
        } else {
            $thumbnailPath = xuverse_upload_image('thumbnail', $error, false);

            if ($error === '') {
                $stmt = $conn->prepare(
                    "INSERT INTO videos
                    (title, description, video_url, thumbnail_path, is_published)
                    VALUES (?, ?, ?, ?, ?)"
                );

                $stmt->bind_param("ssssi", $title, $description, $videoUrl, $thumbnailPath, $isPublished);
                $stmt->execute();

                header("Location: videos.php");
                exit;
            }
        }
    }
}

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Add Media - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include '../../includes/header.php';
include '../../includes/navbar.php';

?>

<section class="section admin-page">
<div class="container">
<a class="text-link admin-back-link" href="index.php">Back to media</a>

<?php if($type === 'photo'): ?>

<h1>Upload Photo</h1>

<?php else: ?>

<h1>Add Video</h1>

<?php endif; ?>

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
<label for="field-description-2">Description</label>
<textarea id="field-description-2"
name="description"
rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
</div>

<?php if($type === 'photo'): ?>

<div class="input-group">
<label for="field-image-3">Image</label>
<input id="field-image-3"
type="file"
name="image"
accept=".jpg,.jpeg,.png"
required>
</div>

<?php else: ?>

<div class="input-group">
<label for="field-video-url-4">YouTube or Video URL</label>
<input id="field-video-url-4"
type="url"
name="video_url"
value="<?= htmlspecialchars($_POST['video_url'] ?? '') ?>"
required>
</div>

<div class="input-group">
<label for="field-thumbnail-5">Thumbnail</label>
<input id="field-thumbnail-5"
type="file"
name="thumbnail"
accept=".jpg,.jpeg,.png">
</div>

<label class="check-row"><input type="checkbox" name="is_published" value="1" <?= ($_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['is_published']) : false) ? 'checked' : '' ?>> Publish this video</label>
<p class="admin-help">Keep unchecked while preparing a video. Published videos appear in the public media archive.</p>



<?php endif; ?>

<div class="admin-form-actions">
<button class="btn" type="submit"><?= $type === 'photo' ? 'Upload Photo' : 'Save Video' ?></button>
<a href="<?= $type === 'photo' ? 'photos.php' : 'videos.php' ?>" class="btn secondary-btn">Cancel</a></div>
</form>

</div>
</section>

<?php include '../../includes/footer.php'; ?>
