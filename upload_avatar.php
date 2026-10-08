<?php

require_once 'includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once 'includes/db.php';
require_once 'includes/media_helpers.php';

$error = '';
$message = $_SESSION['avatar_message'] ?? '';
unset($_SESSION['avatar_message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $avatarPath = xuverse_upload_image('avatar', $error, true, 'avatars');

    if ($avatarPath && $error === '') {
        $stmt = $conn->prepare('UPDATE users SET avatar_path=? WHERE id=?');
        $stmt->bind_param('si', $avatarPath, $_SESSION['user_id']);
        $stmt->execute();
        $_SESSION['avatar_message'] = 'Profile photo updated successfully.';
        header('Location: upload_avatar.php');
        exit;
    }
}

$stmt = $conn->prepare('SELECT avatar_path, full_name FROM users WHERE id=? LIMIT 1');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

$pageTitle = 'Profile Photo - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include 'includes/header.php';
include 'includes/navbar.php';
?>

<section class="section admin-page">
<div class="container">
<a class="text-link admin-back-link" href="admin/dashboard.php">Back to dashboard</a>
<h1>Profile Photo</h1>
<p>Update your account photo. The public portrait is versioned in content and assets and changes through Publish.</p>

<?php if ($message !== ''): ?>
<p class="success" role="status"><?= e($message) ?></p>
<?php endif; ?>
<?php if ($error !== ''): ?>
<p class="error" role="alert"><?= e($error) ?></p>
<?php endif; ?>

<?php if (!empty($profile['avatar_path'])): ?>
<div class="media-preview">
<img src="<?= e(xuverse_asset($profile['avatar_path'])) ?>" alt="Current profile photo of <?= e($profile['full_name']) ?>">
</div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="admin-form">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
<div class="input-group">
<label for="profile-image">Profile image</label>
<input id="profile-image" type="file" name="avatar" accept=".jpg,.jpeg,.png" aria-describedby="profile-image-help" required>
<p id="profile-image-help" class="admin-help">Choose a JPG or PNG image, up to 2 MB. Images are optimized automatically.</p>
</div>
<div class="admin-form-actions">
<button type="submit" class="btn">Update Photo</button>
<a href="admin/dashboard.php" class="btn secondary-btn">Cancel</a>
</div>
</form>
</div>
</section>

<?php include 'includes/footer.php'; ?>
