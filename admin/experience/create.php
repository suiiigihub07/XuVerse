<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $job_title = trim($_POST['job_title'] ?? '');
    $company_name = trim($_POST['company_name'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $end_date = $end_date === '' ? null : $end_date;
    $validStart = DateTime::createFromFormat('!Y-m-d', $start_date);
    $validEnd = $end_date === null ? null : DateTime::createFromFormat('!Y-m-d', $end_date);
    if ($job_title === '' || $company_name === '') {
        $error = 'Role and organization are required.';
    } elseif (mb_strlen($job_title) > 100 || mb_strlen($company_name) > 100) {
        $error = 'Role and organization must be 100 characters or fewer.';
    } elseif (!$validStart || $validStart->format('Y-m-d') !== $start_date || ($end_date !== null && (!$validEnd || $validEnd->format('Y-m-d') !== $end_date))) {
        $error = 'Enter valid dates, or leave the end date blank for a current role.';
    } elseif ($end_date !== null && $end_date < $start_date) {
        $error = 'The end date cannot be earlier than the start date.';
    }

    if ($error === '') {
    $stmt = $conn->prepare(
        "INSERT INTO experience
        (job_title, company_name, start_date, end_date, description)
        VALUES (?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "sssss",
        $job_title,
        $company_name,
        $start_date,
        $end_date,
        $description
    );

    $stmt->execute();

    header("Location: index.php");
    exit;
    }
}

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Add Experience - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<section class="section admin-page">
<div class="container">
<a class="text-link admin-back-link" href="index.php">Back to experience</a>
<h1>Add Experience</h1>
<p class="admin-help">Fields are required unless marked optional.</p>
<?php if ($error !== ''): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
<form method="post" class="admin-form">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
<div class="input-group">
<label for="job_title">Role or job title</label>
<input id="job_title" type="text" name="job_title" maxlength="100" value="<?= e($_POST['job_title'] ?? '') ?>" required>
</div>
<div class="input-group">
<label for="company_name">Organization or company</label>
<input id="company_name" type="text" name="company_name" maxlength="100" value="<?= e($_POST['company_name'] ?? '') ?>" required>
</div>
<div class="input-group">
<label for="start_date">Start date</label>
<input id="start_date" type="date" name="start_date" value="<?= e($_POST['start_date'] ?? '') ?>" required>
</div>
<div class="input-group">
<label for="end_date">End date (optional)</label>
<input id="end_date" type="date" name="end_date" value="<?= e($_POST['end_date'] ?? '') ?>">
<p class="admin-help">Leave blank for a current role.</p>
</div>
<div class="input-group">
<label for="description">Description (optional)</label>
<textarea id="description" name="description" rows="5"><?= e($_POST['description'] ?? '') ?></textarea>
</div>
<div class="admin-form-actions">
<button type="submit" class="btn">Add Experience</button>
<a href="index.php" class="btn secondary-btn">Cancel</a>
</div>
</form>

</div>
</section>

<?php include '../../includes/footer.php'; ?>
