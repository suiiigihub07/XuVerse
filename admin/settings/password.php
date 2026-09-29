<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$message = '';
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $stmt = $conn->prepare('SELECT password FROM users WHERE id=? LIMIT 1');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || !password_verify($currentPassword, $user['password'])) {
        $error = 'The current password is incorrect.';
    } elseif (strlen($newPassword) < 14) {
        $error = 'Use at least 14 characters for the new password.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'The new passwords do not match.';
    } else {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = $conn->prepare('UPDATE users SET password=? WHERE id=?');
        $update->bind_param('si', $hash, $_SESSION['user_id']);
        $update->execute();
        session_regenerate_id(true);
        $message = 'Password updated successfully.';
    }
}

$pageTitle = 'Change Password - XuVerse';
$pageDescription = 'Update the private XuVerse admin password.';
$robots = xuverse_noindex();

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Change Password - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<section class="section admin-page">
<div class="container">
<a class="text-link admin-back-link" href="../dashboard.php">Back to dashboard</a>
<div class="card">
<p class="page-kicker">Account security</p>
<h1>Change password</h1>

<?php if ($message): ?><p class="success" role="status"><?= e($message) ?></p><?php endif; ?>
<?php if ($error): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>

<form method="post" class="admin-form">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
<div class="input-group">
<label for="current-password">Current password</label>
<input id="current-password" type="password" name="current_password" autocomplete="current-password" required>
</div>
<div class="input-group">
<label for="new-password">New password</label>
<input id="new-password" type="password" name="new_password" autocomplete="new-password" minlength="14" required>
</div>
<div class="input-group">
<label for="confirm-password">Confirm new password</label>
<input id="confirm-password" type="password" name="confirm_password" autocomplete="new-password" minlength="14" required>
</div>
<div class="admin-form-actions">
<button type="submit" class="btn">Update password</button>
<a href="index.php" class="btn secondary-btn">Cancel</a>
</div>
</form>
</div>
</div>
</section>

<?php include '../../includes/footer.php'; ?>
