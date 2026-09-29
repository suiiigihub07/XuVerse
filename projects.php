<?php

require_once 'includes/db.php';
require_once 'includes/functions.php';

$settings = $conn->query(
    "SELECT * FROM settings
     LIMIT 1"
)->fetch_assoc();

$result = $conn->query(
    "SELECT * FROM projects
     WHERE is_published=1
     ORDER BY display_order, id DESC"
);

$pageTitle = 'Projects | ' . xuverse_person_name($conn);
$pageDescription = 'Selected projects, creative work and case studies by ' . xuverse_person_name($conn) . '.';
$canonicalUrl = xuverse_base_url() . '/projects';

include 'includes/header.php';
include 'includes/navbar.php';

?>

<section class="page-hero">
<div class="container">
<p class="page-kicker">Selected work</p>
<h1>Projects</h1>
<p>A closer look at the ideas, process and work behind each project.</p>
</div>
</section>

<section class="section">
<div class="container">

<?php if($result && $result->num_rows > 0): ?>

<div class="projects-grid">

<?php while($row = $result->fetch_assoc()): ?>

<article class="project-card reveal tilt-card">

<?php if(!empty($row['image_path'])): ?>
<a href="<?= e(xuverse_url('projects/' . (int)$row['id'])) ?>" class="media-link">
<picture class="responsive-picture">
<?= xuverse_avif_source($row['image_path'], '(max-width: 720px) 94vw, 33vw') ?>
<img src="<?= e(xuverse_asset($row['image_path'])) ?>" <?= xuverse_image_attributes($row['image_path']) ?> <?= xuverse_responsive_image_attributes($row['image_path'], '(max-width: 720px) 94vw, 33vw') ?> class="project-image" alt="<?= e($row['title']) ?>" loading="lazy" decoding="async">
</picture>
</a>
<?php endif; ?>

<div class="project-content">
<p class="page-kicker"><?= e(trim($row['project_status'] ?? '') ?: 'Project') ?></p>
<h2><a href="<?= e(xuverse_url('projects/' . (int)$row['id'])) ?>"><?= e($row['title']) ?></a></h2>
<p><?= e(xuverse_excerpt($row['description'], 170)) ?></p>

<div class="project-buttons">
<a href="<?= e(xuverse_url('projects/' . (int)$row['id'])) ?>" class="btn">View project</a>

<?php if(!empty($row['github_link'])): ?>
<a href="<?= e($row['github_link']) ?>" target="_blank" rel="noopener noreferrer" class="btn secondary-btn">GitHub</a>
<?php endif; ?>

<?php if(!empty($row['demo_link'])): ?>
<a href="<?= e($row['demo_link']) ?>" target="_blank" rel="noopener noreferrer" class="btn secondary-btn">Demo</a>
<?php endif; ?>
</div>
</div>

</article>

<?php endwhile; ?>

</div>

<?php else: ?>

<div class="empty-state">
<h2>Projects coming soon</h2>
<p>A selection of work will appear here. In the meantime, feel free to get in touch.</p>
<a class="text-link" href="<?= e(xuverse_url('contact')) ?>">Connect</a>
</div>

<?php endif; ?>

</div>
</section>

<?php include 'includes/footer.php'; ?>
