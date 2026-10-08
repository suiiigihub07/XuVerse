<?php
require_once 'includes/content.php';
require_once 'includes/media-gallery.php';
$copy = xuverse_content('copy');
$site = xuverse_content('site');
$media = xuverse_published('media');
$photos = array_filter($media, fn($item) => $item['type'] === 'photograph');
$videos = array_filter($media, fn($item) => $item['type'] === 'video');
$cardSeries = array_filter($media, fn($item) => $item['type'] === 'card series');
xuverse_public_start('Media', $copy['media_intro'], 'media');
?>

<section class="page-hero media-page-hero">
<div class="container">
<p class="page-kicker"><?= e($site['media_page_kicker']) ?></p>
<h1><?= e($site['media_page_heading']) ?></h1>
<p><?= e($copy['media_intro']) ?></p>
<div class="project-buttons">
<a class="btn" href="#photographs"><?= e($site['photographs_heading']) ?></a>
<a class="text-link" href="#videos"><?= e($site['videos_heading']) ?></a>
</div>
</div>
</section>

<section class="section" id="photographs" aria-labelledby="photographs-title">
<div class="container">
<div class="section-heading reveal">
<h2 id="photographs-title"><?= e($site['photographs_heading']) ?></h2>
</div>

<div class="media-grid photo-grid">
<?php foreach ($photos as $photo): ?>
<article class="media-card photo-card portrait-card compact-media-card reveal tilt-card">
<a href="<?= e(xuverse_url('photos/' . $photo['slug'])) ?>" class="media-link" data-full-image="<?= e(xuverse_asset($photo['image'])) ?>">
<picture class="responsive-picture">
<img src="<?= e(xuverse_asset($photo['image'])) ?>" <?= xuverse_image_attributes($photo['image']) ?> alt="<?= e(trim($photo['alt'] ?? '') !== '' ? $photo['alt'] : $photo['title']) ?>" loading="lazy" decoding="async">
</picture>
</a>
<div class="media-content">
<h3><a href="<?= e(xuverse_url('photos/' . $photo['slug'])) ?>"><?= e($photo['title']) ?></a></h3>
</div>
</article>
<?php endforeach; ?>
<?php foreach ($cardSeries as $series): $images = xuverse_gallery_images($series); $modalId = 'gallery-' . $series['slug']; ?>
<article class="media-card gallery-post compact-media-card reveal" id="<?= e($series['slug']) ?>">
<a href="<?= e(xuverse_url('photos/' . $series['slug'])) ?>" class="gallery-post-cover" data-open-gallery="<?= e($modalId) ?>" aria-label="Browse <?= count($images) ?> images: <?= e($series['title']) ?>">
<img src="<?= e(xuverse_asset($images[0])) ?>" <?= xuverse_image_attributes($images[0]) ?> alt="<?= e(!empty($series['alt']) ? $series['alt'] : $series['title']) ?>" loading="lazy" decoding="async">
<span class="gallery-count" aria-hidden="true">1 / <?= count($images) ?></span>
</a>
<div class="media-content"><h3><a href="<?= e(xuverse_url('photos/' . $series['slug'])) ?>"><?= e($series['title']) ?></a></h3></div>
</article>
<dialog class="gallery-modal" id="<?= e($modalId) ?>" aria-label="<?= e($series['title']) ?> gallery">
<div class="gallery-modal-toolbar"><a href="<?= e(xuverse_url('photos/' . $series['slug'])) ?>"><?= e($series['title']) ?> →</a><button class="gallery-close" type="button">Close</button></div>
<?php xuverse_gallery($series, $modalId . '-track'); ?>
<a class="text-link" href="<?= e(xuverse_url()) ?>">Home</a>
</dialog>
<?php endforeach; ?>
</div>
</div>
</section>

<section class="section dark-band" id="videos" aria-labelledby="videos-title">
<div class="container">
<div class="section-heading reveal">
<div>
<h2 id="videos-title"><?= e($site['videos_heading']) ?></h2>
</div>
</div>
<div class="media-grid video-grid">
<?php foreach ($videos as $video): $embedUrl = xuverse_youtube_embed($video['url']); ?>
<article class="media-card video-card compact-media-card reveal">
<div class="video-detail-frame media-player">
<iframe src="<?= e($embedUrl) ?>" title="<?= e($video['title']) ?>" width="1600" height="900" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>
<div class="media-content">
<h3><a href="<?= e(xuverse_url('videos/' . $video['slug'])) ?>"><?= e($video['title']) ?></a></h3>
</div>
</article>
<?php endforeach; ?>
</div>
</div>
</section>

<?php include 'includes/footer.php'; ?>
