<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT * FROM skills
     WHERE id=?"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$skill =
$stmt->get_result()->fetch_assoc();

if (!$skill) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $skill_name = trim($_POST['skill_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $level = trim($_POST['level'] ?? '');

    if ($skill_name === '' || $category === '' || $level === '') {
        $error = 'Skill name, category and level are required.';
    }

    if ($error === '') {
    $stmt = $conn->prepare(
        "UPDATE skills
         SET skill_name=?,
             category=?,
             level=?
         WHERE id=?"
    );

    $stmt->bind_param(
        "sssi",
        $skill_name,
        $category,
        $level,
        $id
    );

    $stmt->execute();

    header("Location: index.php");
    exit;
    }
}

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Edit Skill - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<section class="section admin-page">
<div class="container">
<a class="text-link admin-back-link" href="index.php">Back to skills</a>
<h1>Edit Skill</h1>
<p class="admin-help">Fields are required unless marked optional.</p>
<?php if ($error !== ''): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
<form method="post" class="admin-form">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
<div class="input-group">
<label for="skill_name">Skill name</label>
<input id="skill_name" type="text" name="skill_name" value="<?= e($_POST['skill_name'] ?? $skill['skill_name']) ?>" required>
</div>
<div class="input-group">
<label for="category">Category</label>
<input id="category" type="text" name="category" value="<?= e($_POST['category'] ?? $skill['category']) ?>" required>
</div>
<div class="input-group">
<label for="level">Level</label>
<select id="level" name="level" required>
<?php $selectedLevel = $_POST['level'] ?? $skill['level'];
$levels = ['Beginner', 'Intermediate', 'Advanced', 'Expert'];
if ($selectedLevel !== '' && !in_array($selectedLevel, $levels, true)) { $levels[] = $selectedLevel; }
foreach ($levels as $level): ?>
<option value="<?= e($level) ?>" <?= $selectedLevel === $level ? 'selected' : '' ?>><?= e($level) ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="admin-form-actions">
<button type="submit" class="btn">Save Changes</button>
<a href="index.php" class="btn secondary-btn">Cancel</a>
</div>
</form>

</div>
</section>

<?php include '../../includes/footer.php'; ?>
