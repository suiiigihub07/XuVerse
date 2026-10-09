<?php

require_once '../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';
require_once '../includes/functions.php';

function xuverse_count($conn, $table)
{
    return (int)$conn->query("SELECT COUNT(*) AS total FROM {$table}")->fetch_assoc()['total'];
}

$counts = [
    'experience' => xuverse_count($conn, 'experience'),
    'education' => xuverse_count($conn, 'education'),
    'skills' => xuverse_count($conn, 'skills'),
    'projects' => xuverse_count($conn, 'projects'),
    'articles' => xuverse_count($conn, 'articles'),
    'photos' => xuverse_count($conn, 'photos'),
    'videos' => xuverse_count($conn, 'videos'),
    'highlights' => xuverse_count($conn, 'highlights'),
    'music' => xuverse_table_exists($conn, 'music_tracks') ? xuverse_count($conn, 'music_tracks') : 0
];

$mediaCount = $counts['photos'] + $counts['videos'];
$adminName = $_SESSION['name'] ?? 'Admin';
$pageTitle = 'Control Center - XuVerse';
$pageDescription = 'XuVerse admin control center.';

require_once '../includes/functions.php';
$pageTitle = $pageTitle ?? 'Control Center - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include '../includes/header.php';
include '../includes/navbar.php';

?>

<section class="section admin-page">
<div class="container">

<div class="dashboard-heading">
<div>
<h1>Control Center</h1>
<p>Welcome back, <?= e($adminName) ?>. Edit your public website content here, review the preview, then use Publish to sync the same revision.</p>
</div>

<div class="dashboard-actions">
<a href="../index.php" class="btn secondary-btn">View Website</a>
</div>
</div>

<?php
require_once '../includes/dashboard-content.php';
$publicMedia=xuverse_content('media');
$publicStats=['Projects'=>count(xuverse_content('projects')),'Articles'=>count(xuverse_content('articles')),'Photographs'=>count(array_filter($publicMedia,fn($entry)=>$entry['type']==='photograph')),'Published posts'=>count(xuverse_content('published')),'Videos'=>count(array_filter($publicMedia,fn($entry)=>$entry['type']==='video'))];
?>
<div class="dashboard-grid">
<a class="stat-card" href="website/index.php"><h2>Website content</h2><p>Photos, ANCHOR galleries, videos, writing and every public content section</p></a>
<?php foreach($publicStats as $label=>$total): ?><div class="stat-card"><h2><?= $total ?></h2><p><?= e($label) ?></p></div><?php endforeach; ?>
</div>

<div class="quick-links">
<?php require_once '../includes/dashboard-content.php'; foreach (xuverse_editor_sections() as $key=>$label): ?>
<a href="website/index.php?collection=<?= e($key) ?>"><h2><?= e($label) ?></h2><p>Edit the content displayed on your public pages</p></a>
<?php endforeach; ?>
</div>
<details class="section"><summary>Historical records and account tools</summary>
<p>These retained database records are separate from the current public content.</p>
<div class="dashboard-grid">


<div class="stat-card">
<h2><?= $counts['experience'] ?></h2>
<p>Experience</p>
</div>

<div class="stat-card">
<h2><?= $counts['education'] ?></h2>
<p>Education</p>
</div>

<div class="stat-card">
<h2><?= $counts['skills'] ?></h2>
<p>Skills</p>
</div>

<div class="stat-card">
<h2><?= $counts['projects'] ?></h2>
<p>Projects</p>
</div>

<div class="stat-card">
<h2><?= $counts['articles'] ?></h2>
<p>Articles</p>
</div>

<div class="stat-card">
<h2><?= $mediaCount ?></h2>
<p>Media Items</p>
</div>

<div class="stat-card">
<h2><?= $counts['highlights'] ?></h2>
<p>Highlights</p>
</div>

<div class="stat-card">
<h2><?= $counts['music'] ?></h2>
<p>Music</p>
</div>

</div>


<div class="quick-links">

<a href="experience/index.php">
<h2>Manage Experience</h2>
<p><?= $counts['experience'] ?> entries</p>
</a>

<a href="education/index.php">
<h2>Manage Education</h2>
<p><?= $counts['education'] ?> entries</p>
</a>

<a href="skills/index.php">
<h2>Manage Skills</h2>
<p><?= $counts['skills'] ?> skills</p>
</a>

<a href="projects/index.php">
<h2>Manage Projects</h2>
<p><?= $counts['projects'] ?> projects</p>
</a>

<a href="articles/index.php">
<h2>Manage Articles</h2>
<p><?= $counts['articles'] ?> articles</p>
</a>

<a href="highlights/index.php">
<h2>Manage Highlights</h2>
<p><?= $counts['highlights'] ?> homepage cards</p>
</a>

<a href="media/index.php">
<h2>Manage Media</h2>
<p><?= $counts['photos'] ?> photos, <?= $counts['videos'] ?> videos</p>
</a>

<a href="music/index.php">
<h2>Manage Music</h2>
<p><?= $counts['music'] ?> local tracks</p>
</a>

<a href="settings/index.php">
<h2>Manage Settings</h2>
<p>Homepage, footer and social links</p>
</a>

<a href="../upload_avatar.php">
<h2>Upload Avatar</h2>
<p>Update your account photo</p>
</a>

</div>
</details>
</div>
</section>

<?php include '../includes/footer.php'; ?>
