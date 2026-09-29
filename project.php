<?php

require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM projects WHERE id=? AND is_published=1");
$stmt->bind_param("i", $id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

if (!$project) {
    http_response_code(404);
    require '404.php';
    exit;
}

$pageTitle = $project['title'] . ' | ' . xuverse_person_name($conn);
$pageDescription = xuverse_excerpt($project['description'], 155);
$pageImage = $project['image_path'] ?: 'uploads/avatars/6a2184f69a336.webp';
$canonicalUrl = xuverse_base_url() . '/projects/' . (int)$project['id'];
$schemaData = [
    '@type' => 'CreativeWork',
    'name' => $project['title'],
    'description' => $pageDescription,
    'image' => xuverse_absolute_url($pageImage),
    'url' => $canonicalUrl
];

$caseGallery = [
    6 => [
        ['src' => 'assets/images/projects/xuverse-home-20260728b.webp', 'avif' => 'assets/images/projects/xuverse-home-20260728b.avif', 'alt' => 'XuVerse homepage with portfolio positioning and calls to action', 'caption' => 'Public homepage and positioning'],
        ['src' => 'assets/images/projects/xuverse-resume-20260728.webp', 'avif' => 'assets/images/projects/xuverse-resume-20260728.avif', 'alt' => 'XuVerse responsive web resume interface', 'caption' => 'Database-backed web resume'],
        ['src' => 'assets/images/projects/xuverse-projects-20260728b.webp', 'avif' => 'assets/images/projects/xuverse-projects-20260728b.avif', 'alt' => 'XuVerse selected project case study grid', 'caption' => 'Selected project system']
    ]
];

$facts = [
    'scope' => trim($project['project_scope'] ?? ''),
    'focus' => trim($project['project_focus'] ?? ''),
    'status' => trim($project['project_status'] ?? '') ?: 'Project',
    'evidence' => trim($project['project_evidence'] ?? '')
];

include 'includes/header.php';
include 'includes/navbar.php';

?>

<section class="section detail-section">
<div class="container">

<article class="detail-layout <?= empty($project['image_path']) ? 'detail-layout-text' : '' ?> project-detail-<?= (int)$project['id'] ?>">

<?php if(!empty($project['image_path'])): ?>
<div class="detail-media">
<picture class="responsive-picture">
<?= xuverse_avif_source($project['image_path'], '(max-width: 720px) 94vw, 48vw') ?>
<img
src="<?= e(xuverse_asset($project['image_path'])) ?>"
<?= xuverse_image_attributes($project['image_path']) ?>
<?= xuverse_responsive_image_attributes($project['image_path'], '(max-width: 720px) 94vw, 48vw') ?>
alt="<?= e($project['title']) ?>"
loading="eager"
decoding="async">
</picture>
</div>
<?php endif; ?>

<div class="detail-content">
<p class="page-kicker"><?= e($facts['status']) ?></p>
<h1><?= e($project['title']) ?></h1>
<p><?= nl2br(e($project['description'])) ?></p>

<dl class="case-facts">
<?php foreach (['scope' => 'Scope', 'focus' => 'Focus', 'evidence' => 'Project notes'] as $key => $label): ?>
<?php if ($facts[$key] !== ''): ?>
<div><dt><?= e($label) ?></dt><dd><?= e($facts[$key]) ?></dd></div>
<?php endif; ?>
<?php endforeach; ?>
</dl>

<?php if (trim($project['case_study'] ?? '') !== ''): ?>
<section class="case-section prose" aria-label="Case study">
<?= xuverse_prose($project['case_study']) ?>
</section>
<?php endif; ?>

<div class="project-buttons">
<?php if(!empty($project['github_link'])): ?>
<a href="<?= e($project['github_link']) ?>" target="_blank" rel="noopener noreferrer" class="btn">GitHub</a>
<?php endif; ?>

<?php if(!empty($project['demo_link'])): ?>
<a href="<?= e($project['demo_link']) ?>" target="_blank" rel="noopener noreferrer" class="btn secondary-btn">View project</a>
<?php endif; ?>

<a href="<?= e(xuverse_url('projects')) ?>" class="text-link">Back to all projects</a>
</div>
</div>

</article>

<?php if(!empty($caseGallery[(int)$project['id']])): ?>
<section class="case-gallery" aria-labelledby="interface-evidence">
<div class="section-heading">
<p class="page-kicker">Interface gallery</p>
<h2 id="interface-evidence">Screens from the working application</h2>
<p>Screenshots from the application’s development.</p>
</div>
<div class="case-gallery-grid">
<?php foreach($caseGallery[(int)$project['id']] as $image): ?>
<figure>
<picture>
<source srcset="<?= e(xuverse_asset($image['avif'])) ?>" type="image/avif">
<img src="<?= e(xuverse_asset($image['src'])) ?>" <?= xuverse_image_attributes($image['src']) ?> alt="<?= e($image['alt']) ?>" loading="lazy" decoding="async">
</picture>
<figcaption><?= e($image['caption']) ?></figcaption>
</figure>
<?php endforeach; ?>
</div>
</section>
<?php endif; ?>

</div>
</section>

<?php include 'includes/footer.php'; ?>
