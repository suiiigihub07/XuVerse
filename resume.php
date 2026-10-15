<?php

require_once 'includes/db.php';
require_once 'includes/resume-data.php';

$resume = xuverse_resume_data($conn);
$settings = $resume['settings'];
$user = $resume['user'];
$education = $resume['education'];
$projects = $resume['projects'];
$skillsByCategory = $resume['skills'];
$name = $resume['name'];
$contactEmail = $resume['email'];
$headline = $resume['headline'];
$summary = $resume['summary'];
$pageTitle = 'Resume - ' . $name;
$pageDescription = xuverse_excerpt($summary, 155);
$canonicalUrl = xuverse_base_url() . '/resume';
$pageImage = $user['avatar_path'] ?? 'uploads/avatars/6a2184f69a336.webp';

include 'includes/header.php';
include 'includes/navbar.php';

?>

<section class="section resume-section">
<div class="container">

<div class="resume-paper">

<div class="resume-top">

<?php if(!empty($user['avatar_path'])): ?>
<picture class="responsive-picture">
<?= xuverse_avif_source($user['avatar_path'], '(max-width: 720px) 104px, 118px') ?>
<img
src="<?= e(xuverse_asset($user['avatar_path'])) ?>"
<?= xuverse_image_attributes($user['avatar_path']) ?>
<?= xuverse_responsive_image_attributes($user['avatar_path'], '(max-width: 720px) 104px, 118px') ?>
class="resume-photo"
alt="<?= htmlspecialchars($name) ?>"
loading="eager"
decoding="async">
</picture>
<?php endif; ?>

<div>
<p class="page-kicker">Resume</p>
<h1 class="resume-name"><?= e($name) ?></h1>
<?php if ($headline !== '' && !str_starts_with(mb_strtolower($summary), mb_strtolower($headline))): ?>
<p class="resume-title"><?= e($headline) ?></p>
<?php endif; ?>

<?php if ($summary !== ''): ?><p class="resume-summary"><?= e($summary) ?></p><?php endif; ?>

<div class="resume-meta">
<?php if($contactEmail !== ''): ?>
<a href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a>
<?php endif; ?>

<?php if(!empty($settings['github_url'])): ?>
<a href="<?= e($settings['github_url']) ?>" target="_blank" rel="noopener noreferrer">GitHub</a>
<?php endif; ?>

<?php if(!empty($settings['linkedin_url'])): ?>
<a href="<?= e($settings['linkedin_url']) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
<?php endif; ?>

<a class="print-resume" href="<?= e(xuverse_url('resume/download')) ?>" download>Download PDF</a>
<button type="button" class="print-resume print-only-control" data-print-resume>Print</button>
</div>
</div>

</div>

<div class="resume-layout">

<aside class="resume-side">

<?php if($skillsByCategory): ?>
<div class="resume-block">
<h2>Skills / Areas of Work</h2>

<div class="resume-skill-groups">
<?php foreach($skillsByCategory as $category => $skills): ?>
<div class="resume-skill-group">
<h3><?= htmlspecialchars($category) ?></h3>
<div class="skills-grid">
<?php foreach($skills as $skillName): ?>
<span class="resume-chip"><?= htmlspecialchars($skillName) ?></span>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; ?>
</div>

</div>
<?php endif; ?>

</aside>

<div class="resume-main">
<div class="resume-columns">
<div class="resume-column">

<?php if($education): ?>
<div class="resume-block">
<h2>Education</h2>

<?php foreach($education as $row): ?>
<article class="resume-item">
<h3><?= htmlspecialchars($row['institution']) ?></h3>
<p class="resume-program"><?= e($row['degree']) ?></p>
<p class="meta resume-entry-meta"><span><?= e($row['location']) ?></span><span><?= e($row['start_year']) ?> – <?= e($row['end_year'] ?: 'Present') ?></span></p>
<p class="resume-semester"><?= e($row['semester']) ?></p>
<p><?= nl2br(htmlspecialchars($row['description'])) ?></p>
</article>
<?php endforeach; ?>

</div>
<?php endif; ?>

<?php foreach ($resume['role_groups'] as $heading => $roles): if (!$roles || $heading !== 'Experience / Work') continue; ?>
<div class="resume-block <?= $heading === 'Experience / Work' ? 'resume-work' : 'resume-leadership' ?>">
<h2><?= e($heading) ?></h2>

<?php foreach($roles as $row): ?>
<article class="resume-item">
<h3><?= htmlspecialchars($row['job_title']) ?></h3>
<p class="meta resume-entry-meta"><span><?= e($row['company_name']) ?></span><span><?= e($row['dates']) ?></span></p>
<?php if ($row['description'] !== ''): ?><p><?= nl2br(e($row['description'])) ?></p><?php endif; ?>
</article>
<?php endforeach; ?>

</div>
<?php endforeach; ?>

</div>
<div class="resume-column">
<?php foreach ($resume['role_groups'] as $heading => $roles): if (!$roles || $heading !== 'Leadership & Creative Roles') continue; ?>
<div class="resume-block resume-leadership">
<h2><?= e($heading) ?></h2>
<?php foreach($roles as $row): ?>
<article class="resume-item">
<h3><?= e($row['job_title']) ?></h3>
<p class="meta resume-entry-meta"><span><?= e($row['company_name']) ?></span><span><?= e($row['dates']) ?></span></p>
<?php if ($row['description'] !== ''): ?><p><?= nl2br(e($row['description'])) ?></p><?php endif; ?>
</article>
<?php endforeach; ?>
</div>
<?php endforeach; ?>

<?php if ($resume['achievement'] !== ''): ?>
<div class="resume-block">
<h2>Selected Achievements</h2>
<p><?= e($resume['achievement']) ?></p>
<p class="resume-note"><?= e($resume['achievement_note']) ?></p>
</div>
<?php endif; ?>
</div>
</div>

<?php if($projects): ?>
<div class="resume-block">
<h2>Selected Projects</h2>

<ul class="resume-list">
<?php foreach($projects as $row): ?>
<li>
<strong><a href="<?= e(xuverse_url('projects/' . $row['slug'])) ?>"><?= e($row['title']) ?></a></strong>
<br>
<span><?= htmlspecialchars($row['description']) ?></span>
</li>
<?php endforeach; ?>
</ul>

</div>
<?php endif; ?>

</div>

</div>

</div>

</div>
</section>

<?php include 'includes/footer.php'; ?>
