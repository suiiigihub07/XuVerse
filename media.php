<?php

require_once 'includes/db.php';
require_once 'includes/functions.php';

$settings = $conn->query(
    "SELECT * FROM settings
     LIMIT 1"
)->fetch_assoc();

$photos = $conn->query(
    "SELECT p.*
     FROM photos p
     INNER JOIN (
         SELECT MIN(id) AS id
         FROM photos
         WHERE image_path IS NOT NULL
           AND TRIM(image_path) <> ''
         GROUP BY image_path
     ) unique_photos ON unique_photos.id = p.id
     ORDER BY p.uploaded_at DESC"
);

$videos = $conn->query(
    "SELECT * FROM videos
     WHERE is_published=1
     ORDER BY uploaded_at DESC"
);

$mediaIntro = xuverse_setting($settings, 'media_intro', 'Photography, videography, content creation and digital storytelling.');
$pageTitle = 'Media | ' . xuverse_person_name($conn);
$pageDescription = $mediaIntro;
$canonicalUrl = xuverse_base_url() . '/media';

include 'includes/header.php';
include 'includes/navbar.php';

?>

<section class="page-hero media-page-hero">
<div class="container">
<p class="page-kicker">Visual Archive</p>
<h1>Media</h1>
<p><?= e($mediaIntro) ?></p>
</div>
</section>

<section class="section">
<div class="container">

<div class="section-heading with-actions reveal">
<div>
<h2>Photography</h2>
<p>Moments, places and people through my lens.</p>
</div>
</div>

<?php if($photos && $photos->num_rows > 0): ?>

<div class="media-grid photo-grid">

<?php while($row = $photos->fetch_assoc()): ?>

<article class="media-card photo-card reveal tilt-card">

<?php if(!empty($row['image_path'])): ?>
<a href="<?= e(xuverse_url('photos/' . (int)$row['id'])) ?>" class="media-link" data-full-image="<?= e(xuverse_asset($row['image_path'])) ?>">
<picture class="responsive-picture">
<?= xuverse_avif_source($row['image_path'], '(max-width: 720px) 94vw, 33vw') ?>
<img src="<?= e(xuverse_asset($row['image_path'])) ?>" <?= xuverse_image_attributes($row['image_path']) ?> <?= xuverse_responsive_image_attributes($row['image_path'], '(max-width: 720px) 94vw, 33vw') ?> alt="<?= e($row['title']) ?>" loading="lazy" decoding="async">
</picture>
</a>
<?php endif; ?>

<div class="media-content">
<p class="page-kicker">Photo</p>
<h3><?= e($row['title']) ?></h3>
<a href="<?= e(xuverse_url('photos/' . (int)$row['id'])) ?>" class="text-link">View photo</a>
</div>

</article>

<?php endwhile; ?>

</div>

<?php else: ?>

<div class="empty-state">
<h3>Photography coming soon</h3>
<p>New photographs will appear here as the collection grows.</p>
</div>

<?php endif; ?>

</div>
</section>

<section class="section dark-band">
<div class="container">

<div class="section-heading reveal">
<h2>Video</h2>
<p>Stories, events and creative work in motion.</p>
</div>

<?php if($videos && $videos->num_rows > 0): ?>

<div class="media-grid">

<?php while($row = $videos->fetch_assoc()): ?>

<article class="media-card video-card reveal">

<?php if(!empty($row['thumbnail_path'])): ?>
<a href="<?= e(xuverse_url('videos/' . (int)$row['id'])) ?>" class="media-link">
<img src="<?= e(xuverse_asset($row['thumbnail_path'])) ?>" <?= xuverse_image_attributes($row['thumbnail_path']) ?> class="video-thumb" alt="<?= e($row['title']) ?>" loading="lazy" decoding="async">
</a>
<?php else: ?>
<a href="<?= e(xuverse_url('videos/' . (int)$row['id'])) ?>" class="media-link">
<div class="video-placeholder"><span>Video</span></div>
</a>
<?php endif; ?>

<div class="media-content">
<p class="page-kicker">Watch</p>
<h3><?= e($row['title']) ?></h3>
<?php if(!empty($row['description'])): ?>
<p><?= e(xuverse_excerpt($row['description'], 150)) ?></p>
<?php endif; ?>

<a href="<?= e(xuverse_url('videos/' . (int)$row['id'])) ?>" class="btn">View video</a>
</div>

</article>

<?php endwhile; ?>

</div>

<?php else: ?>

<div class="empty-state">
<h3>Videos coming soon</h3>
<p>New videos will appear here as they are published.</p>
</div>

<?php endif; ?>

</div>
</section>

<?php include 'includes/footer.php'; ?>
