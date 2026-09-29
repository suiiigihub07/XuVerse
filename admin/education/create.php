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

    $institution = trim($_POST['institution'] ?? '');
    $degree = trim($_POST['degree'] ?? '');
    $start_year = trim($_POST['start_year'] ?? '');
    $end_year = trim($_POST['end_year'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $end_year = $end_year === '' ? null : $end_year;
    if ($institution === '' || $degree === '') {
        $error = 'Institution and qualification are required.';
    } elseif (mb_strlen($institution) > 150 || mb_strlen($degree) > 150) {
        $error = 'Institution and qualification must be 150 characters or fewer.';
    } elseif (!ctype_digit($start_year) || (int)$start_year < 1901 || (int)$start_year > 2155 || ($end_year !== null && (!ctype_digit($end_year) || (int)$end_year < 1901 || (int)$end_year > 2155))) {
        $error = 'Enter a year between 1901 and 2155. Leave the end year blank if currently studying.';
    } elseif ($end_year !== null && (int)$end_year < (int)$start_year) {
        $error = 'The end year cannot be earlier than the start year.';
    }

    if ($error === '') {
    $stmt = $conn->prepare(
        "INSERT INTO education
        (institution, degree, start_year, end_year, description)
        VALUES (?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "ssiis",
        $institution,
        $degree,
        $start_year,
        $end_year,
        $description
    );

    $stmt->execute();

    header("Location: index.php");
    exit;
    }
}

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Add Education - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<section class="section admin-page">
<div class="container">
<a class="text-link admin-back-link" href="index.php">Back to education</a>
<h1>Add Education</h1>
<p class="admin-help">Fields are required unless marked optional.</p>
<?php if ($error !== ''): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
<form method="post" class="admin-form">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
<div class="input-group">
<label for="institution">Institution</label>
<input id="institution" type="text" name="institution" maxlength="150" value="<?= e($_POST['institution'] ?? '') ?>" required>
</div>
<div class="input-group">
<label for="degree">Degree or qualification</label>
<input id="degree" type="text" name="degree" maxlength="150" value="<?= e($_POST['degree'] ?? '') ?>" required>
</div>
<div class="input-group">
<label for="start_year">Start year</label>
<input id="start_year" type="number" name="start_year" value="<?= e($_POST['start_year'] ?? '') ?>" min="1901" max="2155" step="1" required>
</div>
<div class="input-group">
<label for="end_year">End year (optional)</label>
<input id="end_year" type="number" name="end_year" value="<?= e($_POST['end_year'] ?? '') ?>" min="1901" max="2155" step="1">
<p class="admin-help">Leave blank if currently studying.</p>
</div>
<div class="input-group">
<label for="description">Description (optional)</label>
<textarea id="description" name="description" rows="5"><?= e($_POST['description'] ?? '') ?></textarea>
</div>
<div class="admin-form-actions">
<button type="submit" class="btn">Add Education</button>
<a href="index.php" class="btn secondary-btn">Cancel</a>
</div>
</form>

</div>
</section>

<?php include '../../includes/footer.php'; ?>
