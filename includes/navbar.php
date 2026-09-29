<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$navSettings = $conn->query(
    "SELECT site_title, nav_cta_label FROM settings
     LIMIT 1"
)->fetch_assoc();

$siteTitle = $navSettings['site_title'] ?? 'XuVerse';
$navCtaLabel = $navSettings['nav_cta_label'] ?? 'Connect';
$currentScript = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$currentPage = basename($currentScript);
$isAdminArea = strpos($currentScript, '/admin/') !== false;
$navUser = null;

if (isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT full_name, avatar_path FROM users WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $navUser = $stmt->get_result()->fetch_assoc();
}

function nav_active($pages, $currentPage, $isAdminArea = false)
{
    if ($isAdminArea) {
        return '';
    }

    return in_array($currentPage, (array)$pages, true) ? 'active' : '';
}

$profileName = $navUser['full_name'] ?? ($_SESSION['name'] ?? 'Admin');
$profileInitial = strtoupper(substr(trim($profileName) ?: 'X', 0, 1));

?>

<nav class="navbar" aria-label="Primary navigation">
<div class="container">
<div class="nav-wrapper">

<a href="<?= e(xuverse_url()) ?>" class="logo">
<span><?= e($siteTitle) ?></span>
<small><?= e(xuverse_person_name($conn)) ?></small>
</a>

<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-links">
<span>Menu</span>
</button>

<div class="nav-links" id="primary-links">

<a class="<?= nav_active('index.php', $currentPage, $isAdminArea) ?>" href="<?= e(xuverse_url()) ?>">Home</a>
<a class="<?= nav_active('resume.php', $currentPage, $isAdminArea) ?>" href="<?= e(xuverse_url('resume')) ?>">Resume</a>
<a class="<?= nav_active(['projects.php', 'project.php'], $currentPage, $isAdminArea) ?>" href="<?= e(xuverse_url('projects')) ?>">Projects</a>
<a class="<?= nav_active(['media.php', 'photo.php', 'video.php'], $currentPage, $isAdminArea) ?>" href="<?= e(xuverse_url('media')) ?>">Media</a>
<a class="<?= nav_active(['articles.php', 'article.php'], $currentPage, $isAdminArea) ?>" href="<?= e(xuverse_url('articles')) ?>">Articles</a>
<a class="nav-action <?= nav_active('contact.php', $currentPage, $isAdminArea) ?>" href="<?= e(xuverse_url('contact')) ?>"><?= e($navCtaLabel) ?></a>

<?php if(isset($_SESSION['user_id'])): ?>
<div class="profile-menu">
<button
type="button"
class="profile-trigger <?= $isAdminArea ? 'active' : '' ?>"
aria-expanded="false"
aria-label="Account menu"
aria-haspopup="true">
<?php if(!empty($navUser['avatar_path'])): ?>
<img src="<?= e(xuverse_asset($navUser['avatar_path'])) ?>" <?= xuverse_image_attributes($navUser['avatar_path']) ?> <?= xuverse_responsive_image_attributes($navUser['avatar_path'], '40px') ?> alt="<?= e($profileName) ?>">
<?php else: ?>
<span><?= e($profileInitial) ?></span>
<?php endif; ?>
</button>

<div class="profile-dropdown">
<p><?= e($profileName) ?></p>
<a href="<?= e(xuverse_url('admin/dashboard.php')) ?>">Dashboard</a>
<a href="<?= e(xuverse_url('admin/settings/index.php')) ?>">Settings</a>
<a href="<?= e(xuverse_url('admin/settings/password.php')) ?>">Password</a>
<a href="<?= e(xuverse_url('admin/music/index.php')) ?>">Music</a>
<a href="<?= e(xuverse_url('logout.php')) ?>">Logout</a>
</div>
</div>
<?php endif; ?>

</div>

</div>
</div>
</nav>

<div id="page-shell" class="page-shell">
<main id="main-content">
