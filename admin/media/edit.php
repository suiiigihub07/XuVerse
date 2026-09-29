<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';
require_once '../../includes/media_helpers.php';

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$error = '';

if (!xuverse_is_valid_media_type($type) || $id <= 0) {
    header("Location: index.php");
    exit;
}

$table = $type === 'photo' ? 'photos' : 'videos';
$backPage = $type === 'photo' ? 'photos.php' : 'videos.php';

$stmt = $conn->prepare("SELECT * FROM {$table} WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    header("Location: {$backPage}");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '') {
        $error = "Title is required.";
    }

    if ($type === 'photo' && $error === '') {
        $imagePath = $item['image_path'];
        $newImagePath = xuverse_upload_image('image', $error, false);

        if ($newImagePath) {
            $imagePath = $newImagePath;
        }

        if ($error === '') {
            $update = $conn->prepare(
                "UPDATE photos
                 SET title=?, description=?, image_path=?
                 WHERE id=?"
            );

            $update->bind_param("sssi", $title, $description, $imagePath, $id);
            $update->execute();

            if ($newImagePath) {
                xuverse_delete_uploaded_file($item['image_path']);
            }

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
            $thumbnailPath = $item['thumbnail_path'];
            $newThumbnailPath = xuverse_upload_image('thumbnail', $error, false);

            if ($newThumbnailPath) {
                $thumbnailPath = $newThumbnailPath;
            }

            if ($error === '') {
                $update = $conn->prepare(
                    "UPDATE videos
                     SET title=?, description=?, video_url=?, thumbnail_path=?, is_published=?
                     WHERE id=?"
                );

                $update->bind_param("ssssii", $title, $description, $videoUrl, $thumbnailPath, $isPublished, $id);
                $update->execute();

                if ($newThumbnailPath) {
                    xuverse_delete_uploaded_file($item['thumbnail_path']);
                }

                header("Location: videos.php");
                exit;
            }
        }
    }
}

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Edit Media - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include '../../includes/header.php';
include '../../includes/navbar.php';

?>

<section class="section admin-page">
<div class="container">
<a class="text-link admin-back-link" href="index.php">Back to media</a>

<h1>Edit <?= $type === 'photo' ? 'Photo' : 'Video' ?></h1>

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
value="<?= htmlspecialchars($_POST['title'] ?? $item['title']) ?>"
required>
</div>

<div class="input-group">
<label for="field-description-2">Description</label>
<textarea id="field-description-2"
name="description"
rows="4"><?= htmlspecialchars($_POST['description'] ?? $item['description'] ?? '') ?></textarea>
</div>

<?php if($type === 'photo'): ?>

<?php if(!empty($item['image_path'])): ?>
<div class="media-preview">
<img
src="../../<?= htmlspecialchars($item['image_path']) ?>"
alt="<?= htmlspecialchars($item['title']) ?>">
</div>
<?php endif; ?>

<div class="input-group">
<label for="field-image-3">Replace Image</label>
<input id="field-image-3"
type="file"
name="image"
accept=".jpg,.jpeg,.png">
</div>

<?php else: ?>

<div class="input-group">
<label for="field-video-url-4">YouTube or Video URL</label>
<input id="field-video-url-4"
type="url"
name="video_url"
value="<?= htmlspecialchars($_POST['video_url'] ?? $item['video_url']) ?>"
required>
</div>

<?php if(!empty($item['thumbnail_path'])): ?>
<div class="media-preview">
<img
src="../../<?= htmlspecialchars($item['thumbnail_path']) ?>"
alt="<?= htmlspecialchars($item['title']) ?>">
</div>
<?php endif; ?>

<div class="input-group">
<label for="field-thumbnail-5">Replace Thumbnail</label>
<input id="field-thumbnail-5"
type="file"
name="thumbnail"
accept=".jpg,.jpeg,.png">
</div>

<label class="check-row"><input type="checkbox" name="is_published" value="1" <?= ($_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['is_published']) : !empty($item['is_published'])) ? 'checked' : '' ?>> Publish this video</label>
<p class="admin-help">Keep unchecked while preparing a video. Published videos appear in the public media archive.</p>

<?php endif; ?>

<div class="admin-form-actions">
<button class="btn" type="submit">
Save Changes
</button>

<a href="<?= htmlspecialchars($backPage) ?>" class="btn secondary-btn">
Cancel
</a>

</div>
</form>

</div>
</section>

<?php include '../../includes/footer.php'; ?>
