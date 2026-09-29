<?php

require_once 'includes/db.php';
require_once 'includes/resume-data.php';

$resume = xuverse_resume_data($conn);
$settings = $resume['settings'];
$user = $resume['user'];
$experience = $resume['experience'];
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

<section class="section">
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
<?php if ($headline !== ''): ?>
<p class="resume-title"><?= e($headline) ?></p>
<?php endif; ?>

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

<?php if ($summary !== ''): ?>
<div class="resume-block">
<h2>Profile</h2>
<p><?= nl2br(htmlspecialchars($summary)) ?></p>
</div>
<?php endif; ?>

<?php if($skillsByCategory): ?>
<div class="resume-block">
<h2>Skills</h2>

<?php foreach($skillsByCategory as $category => $skills): ?>
<h3><?= htmlspecialchars($category) ?></h3>
<div class="skills-grid">
<?php foreach($skills as $skillName): ?>
<span class="resume-chip"><?= htmlspecialchars($skillName) ?></span>
<?php endforeach; ?>
</div>
<?php endforeach; ?>

</div>
<?php endif; ?>

<?php if ($resume['focus'] !== ''): ?>
<div class="resume-block">
<h2>Focus</h2>
<p><?= nl2br(e($resume['focus'])) ?></p>
</div>
<?php endif; ?>

</aside>

<div class="resume-main">

<?php if($experience): ?>
<div class="resume-block">
<h2>Experience</h2>

<?php foreach($experience as $row): ?>
<article class="resume-item">
<h3><?= htmlspecialchars($row['job_title']) ?></h3>
<p class="meta">
<?= htmlspecialchars($row['company_name']) ?>
<?php if (!empty($row['start_date'])): ?>
 |
<?= e(xuverse_resume_date($row['start_date'], '')) ?> - <?= e(xuverse_resume_date($row['end_date'] ?? '')) ?>
<?php endif; ?>
</p>
<p><?= nl2br(htmlspecialchars($row['description'])) ?></p>
</article>
<?php endforeach; ?>

</div>
<?php endif; ?>

<?php if($education): ?>
<div class="resume-block">
<h2>Education</h2>

<?php foreach($education as $row): ?>
<article class="resume-item">
<h3><?= htmlspecialchars($row['institution']) ?></h3>
<p class="meta">
<?= htmlspecialchars($row['degree']) ?>
<?php if (!empty($row['start_year'])): ?>
 |
<?= htmlspecialchars($row['start_year']) ?> - <?= !empty($row['end_year']) ? htmlspecialchars($row['end_year']) : 'Present' ?>
<?php endif; ?>
</p>
<p><?= nl2br(htmlspecialchars($row['description'])) ?></p>
</article>
<?php endforeach; ?>

</div>
<?php endif; ?>

<?php if($projects): ?>
<div class="resume-block">
<h2>Selected Projects</h2>

<ul class="resume-list">
<?php foreach($projects as $row): ?>
<li>
<strong><a href="<?= e(xuverse_url('projects/' . (int)$row['id'])) ?>"><?= e($row['title']) ?></a></strong>
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
