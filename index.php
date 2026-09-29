<?php

require_once 'includes/db.php';
require_once 'includes/functions.php';

$settings = $conn->query(
    "SELECT * FROM settings
     LIMIT 1"
)->fetch_assoc();

$user = $conn->query(
    "SELECT * FROM users
     WHERE role='admin'
     LIMIT 1"
)->fetch_assoc();

$projectCount = (int)$conn->query("SELECT COUNT(*) AS total FROM projects WHERE is_published=1")->fetch_assoc()['total'];
$videoCount = (int)$conn->query("SELECT COUNT(*) AS total FROM videos WHERE is_published=1")->fetch_assoc()['total'];
$articleCount = (int)$conn->query("SELECT COUNT(*) AS total FROM articles WHERE published=1 AND LOWER(TRIM(title)) <> 'hi'")->fetch_assoc()['total'];

$featuredProjects = $conn->query(
    "SELECT * FROM projects
     WHERE is_published=1
     ORDER BY display_order, id DESC LIMIT 3"
);

$heroTitle = xuverse_setting($settings, 'hero_title', 'B K Suraj');
$heroSubtitle = xuverse_setting($settings, 'hero_subtitle');
$primaryCta = xuverse_setting($settings, 'hero_primary_cta', 'Connect');
$secondaryCta = xuverse_setting($settings, 'hero_secondary_cta', 'Explore my work');
$pageTitle = $heroTitle . ' | ' . xuverse_setting($settings, 'site_title', 'Portfolio');
$pageDescription = xuverse_setting($settings, 'meta_description', xuverse_setting($settings, 'hero_description'));
$canonicalUrl = xuverse_base_url() . '/';
$pageImage = $user['avatar_path'] ?? 'uploads/avatars/6a2184f69a336.webp';

include 'includes/header.php';
include 'includes/navbar.php';

?>

<section class="landing">
<div class="container landing-grid">

<div class="landing-copy reveal">
<p class="eyebrow"><?= e(xuverse_setting($settings, 'hero_kicker', 'Personal portfolio')) ?></p>
<h1><?= e($heroTitle) ?></h1>
<p class="landing-line"><?= e($heroSubtitle) ?></p>
<p class="hero-proof"><?= e(xuverse_setting($settings, 'hero_description')) ?></p>

<div class="action-row">
<a href="<?= e(xuverse_url('contact')) ?>" class="vx-btn primary"><?= e($primaryCta) ?></a>
<a href="#worlds" class="vx-btn"><?= e($secondaryCta) ?></a>
<a href="<?= e(xuverse_url('resume')) ?>" class="vx-btn ghost">View resume</a>
</div>

</div>

<aside class="identity-panel reveal magnetic-card">
<?php if(!empty($user['avatar_path'])): ?>
<picture class="responsive-picture">
<?= xuverse_avif_source($user['avatar_path'], '(max-width: 900px) 220px, 340px') ?>
<img src="<?= e(xuverse_asset($user['avatar_path'])) ?>" <?= xuverse_image_attributes($user['avatar_path']) ?> <?= xuverse_responsive_image_attributes($user['avatar_path'], '(max-width: 900px) 220px, 340px') ?> alt="<?= e($heroTitle) ?>" loading="eager" fetchpriority="high" decoding="async">
</picture>
<?php endif; ?>
</aside>

</div>
</section>

<section class="section nav-deck-section" id="worlds">
<div class="container">
<div class="nav-deck">
<a href="<?= e(xuverse_url('projects')) ?>" class="deck-card reveal">
<span>01</span>
<strong>Projects & ideas</strong>
<em><?= $projectCount ?> selected projects</em>
</a>

<a href="<?= e(xuverse_url('media')) ?>" class="deck-card reveal">
<span>02</span>
<strong>Photo & video</strong>
<em>Visual stories + <?= $videoCount ?> videos</em>
</a>

<a href="<?= e(xuverse_url('articles')) ?>" class="deck-card reveal">
<span>03</span>
<strong>Writing & learning</strong>
<em><?= $articleCount ?> published notes</em>
</a>

<a href="<?= e(xuverse_url('contact')) ?>" class="deck-card hot reveal">
<span>04</span>
<strong>Community & contact</strong>
<em>Campus life + collaboration</em>
</a>
</div>
</div>
</section>

<section class="section work-snapshot">
<div class="container">
<div class="section-head-compact reveal">
<p class="eyebrow">Selected Work</p>
<h2>Selected projects and creative systems</h2>
</div>

<div class="snap-grid">
<?php while($project = $featuredProjects->fetch_assoc()): ?>
<a href="<?= e(xuverse_url('projects/' . (int)$project['id'])) ?>" class="snap-card reveal magnetic-card">
<?php if(!empty($project['image_path'])): ?>
<picture class="responsive-picture">
<?= xuverse_avif_source($project['image_path'], '(max-width: 720px) 94vw, 33vw') ?>
<img src="<?= e(xuverse_asset($project['image_path'])) ?>" <?= xuverse_image_attributes($project['image_path']) ?> <?= xuverse_responsive_image_attributes($project['image_path'], '(max-width: 720px) 94vw, 33vw') ?> alt="<?= e($project['title']) ?>" loading="lazy" decoding="async">
</picture>
<?php endif; ?>
<span>Project</span>
<strong><?= e($project['title']) ?></strong>
</a>
<?php endwhile; ?>

</div>
</div>
</section>

<?php include 'includes/footer.php'; ?>
