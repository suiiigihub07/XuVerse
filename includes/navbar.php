<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

require_once __DIR__ . '/content.php';
$navCopy = xuverse_content('site');
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

$profileName = $navUser['full_name'] ?? ($_SESSION['name'] ?? 'Admin');
$profileInitial = strtoupper(substr(trim($profileName) ?: 'X', 0, 1));
$primaryLinks = [
    ['home', '', ['index.php']],
    ['projects', 'projects', ['projects.php', 'project.php']],
    ['articles', 'articles', ['articles.php', 'article.php']],
    ['media', 'media', ['media.php', 'gallery.php', 'photo.php', 'video.php', 'published.php']],
    ['about', 'about', ['about.php']],
    ['resume', 'resume', ['resume.php']],
    ['contact', 'contact', ['contact.php']],
];

?>

<nav class="navbar" aria-label="Primary navigation">
<div class="container">
<div class="nav-wrapper">

<a href="<?= e(xuverse_url()) ?>" class="logo">
<span><?= e($navCopy['site_title']) ?></span>
</a>

<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-links">
<span class="xu-menu-mark" aria-hidden="true"></span>
<span>Menu</span>
</button>

<div class="nav-links" id="primary-links">

<?php foreach ($primaryLinks as [$key, $path, $scripts]):
    $active = !$isAdminArea && in_array($currentPage, $scripts, true);
    $classes = trim(($key === 'contact' ? 'nav-action ' : '') . ($active ? 'active' : ''));
?>
<a href="<?= e(xuverse_url($path)) ?>"<?= $classes ? ' class="' . e($classes) . '"' : '' ?><?= $active ? ' aria-current="' . ($currentPage === $scripts[0] ? 'page' : 'location') . '"' : '' ?>><?= e($navCopy['nav_' . $key]) ?></a>
<?php endforeach; ?>
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
<a href="<?= e(xuverse_url('logout.php')) ?>">Logout</a>
</div>
</div>
<?php endif; ?>

</div>

</div>
</div>
</nav>

<div id="page-shell" class="page-shell">
<main id="main-content" tabindex="-1">
