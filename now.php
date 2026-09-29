<?php

require_once 'includes/db.php';
require_once 'includes/functions.php';

$settings = $conn->query(
    "SELECT * FROM settings
     LIMIT 1"
)->fetch_assoc();

$latestProject = $conn->query(
    "SELECT id, title, description
     FROM projects
     WHERE is_published=1
     ORDER BY id DESC
     LIMIT 1"
)->fetch_assoc();

$latestArticle = $conn->query(
    "SELECT id, title, content
     FROM articles
     WHERE published=1
       AND LOWER(TRIM(title)) <> 'hi'
     ORDER BY id DESC
     LIMIT 1"
)->fetch_assoc();

$latestVideo = $conn->query(
    "SELECT id, title, description
     FROM videos
     WHERE is_published=1
     ORDER BY uploaded_at DESC
     LIMIT 1"
)->fetch_assoc();

$pageTitle = 'Now | ' . xuverse_person_name($conn);
$pageDescription = xuverse_setting($settings, 'current_focus', 'Current work and interests.');
$canonicalUrl = xuverse_base_url() . '/now';

include 'includes/header.php';
include 'includes/navbar.php';

?>

<section class="page-hero now-hero">
<div class="container">
<p class="page-kicker">Now</p>
<h1>What I’m working on now.</h1>
<p><?= e(xuverse_setting($settings, 'current_focus', 'Building, learning and creating from Busan.')) ?></p>
</div>
</section>

<section class="section">
<div class="container now-grid">

<article class="content-card reveal">
<p class="page-kicker">Location</p>
<h2><?= e(xuverse_setting($settings, 'location_text')) ?></h2>
<p><?= e(xuverse_setting($settings, 'hero_description')) ?></p>
</article>

<article class="content-card reveal">
<p class="page-kicker">Status</p>
<h2>Currently building</h2>
<p><?= e(xuverse_setting($settings, 'availability_text', 'Code, media, campus life and creative experiments.')) ?></p>
</article>

<?php if($latestProject): ?>
<a href="<?= e(xuverse_url('projects/' . (int)$latestProject['id'])) ?>" class="content-card reveal">
<p class="page-kicker">Latest Project</p>
<h2><?= e($latestProject['title']) ?></h2>
<p><?= e(xuverse_excerpt($latestProject['description'], 130)) ?></p>
</a>
<?php endif; ?>

<?php if($latestArticle): ?>
<a href="<?= e(xuverse_url('articles/' . (int)$latestArticle['id'])) ?>" class="content-card reveal">
<p class="page-kicker">Latest Article</p>
<h2><?= e($latestArticle['title']) ?></h2>
<p><?= e(xuverse_excerpt($latestArticle['content'], 130)) ?></p>
</a>
<?php endif; ?>

<?php if($latestVideo): ?>
<a href="<?= e(xuverse_url('videos/' . (int)$latestVideo['id'])) ?>" class="content-card reveal">
<p class="page-kicker">Latest Video</p>
<h2><?= e($latestVideo['title']) ?></h2>
<p><?= e(xuverse_excerpt($latestVideo['description'], 130)) ?></p>
</a>
<?php endif; ?>

</div>
</section>

<?php include 'includes/footer.php'; ?>
