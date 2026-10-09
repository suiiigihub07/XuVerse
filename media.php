<?php
require_once 'includes/content.php';
require_once 'includes/media-gallery.php';
$copy = xuverse_content('copy');
$site = xuverse_content('site');
$media = xuverse_published('media');
$photos = array_filter($media, fn($item) => $item['type'] === 'photograph');
$videos = array_filter($media, fn($item) => $item['type'] === 'video');
$published = xuverse_published('published');
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
</div>
<?php if ($published): ?>
<div class="published-subsection" id="published" aria-labelledby="published-title">
<div class="section-heading reveal anchor-series-heading"><h3 id="published-title"><?= e($site['published_heading']) ?></h3></div>
<div class="media-grid published-grid">
<?php foreach ($published as $post) xuverse_published_card($post); ?>
</div>
</div>
<?php endif; ?>
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
