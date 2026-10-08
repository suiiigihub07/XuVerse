<?php
require_once 'includes/content.php';
$copy = xuverse_content('copy');
$media = xuverse_content('media');
$photos = array_filter($media, fn($item) => $item['type'] === 'photograph');
$videos = array_filter($media, fn($item) => $item['type'] === 'video');
$cardSeries = array_filter($media, fn($item) => $item['type'] === 'card series');
xuverse_public_start('Media', $copy['media_intro'], 'media');
?>

<section class="page-hero media-page-hero">
<div class="container">
<p class="page-kicker">Visual Archive</p>
<h1>Media</h1>
<p><?= e($copy['media_intro']) ?></p>
<div class="project-buttons">
<a class="btn" href="#photographs">Photographs</a>
<a class="text-link" href="#videos">Videos</a>
</div>
</div>
</section>

<section class="section" id="photographs" aria-labelledby="photographs-title">
<div class="container">
<div class="section-heading reveal">
<h2 id="photographs-title">Photographs</h2>
</div>

<div class="media-grid photo-grid">
<?php foreach ($photos as $photo): ?>
<article class="media-card photo-card portrait-card reveal tilt-card">
<a href="<?= e(xuverse_url('photos/' . $photo['slug'])) ?>" class="media-link" data-full-image="<?= e(xuverse_asset($photo['image'])) ?>">
<picture class="responsive-picture">
<img src="<?= e(xuverse_asset($photo['image'])) ?>" <?= xuverse_image_attributes($photo['image']) ?> alt="<?= e($photo['alt']) ?>" loading="lazy" decoding="async">
</picture>
</a>
<div class="media-content">
<p class="page-kicker">Photograph</p>
<h3><?= e($photo['title']) ?></h3>
<p><?= e($photo['summary']) ?></p>
<a href="<?= e(xuverse_url('photos/' . $photo['slug'])) ?>" class="text-link">View photograph</a>
</div>
</article>
<?php endforeach; ?>
</div>

<?php foreach ($cardSeries as $series): ?>
<div class="section-heading reveal anchor-series-heading" id="<?= e($series['slug']) ?>">
<div>
<p class="page-kicker">Visual Communication</p>
<h3><?= e($series['title']) ?></h3>
<p><?= e($series['summary']) ?></p>
<p class="media-credit"><?= e($series['credit_note']) ?></p>
</div>
</div>
<div class="media-grid photo-grid card-series">
<?php foreach ($series['cards'] as $index => $card): ?>
<figure class="media-card photo-card anchor-card reveal tilt-card">
<a href="<?= e(xuverse_asset($card)) ?>" class="media-link" data-full-image="<?= e(xuverse_asset($card)) ?>">
<img src="<?= e(xuverse_asset($card)) ?>" <?= xuverse_image_attributes($card) ?> alt="ANCHOR Eco Delta Smart City card <?= $index + 1 ?>, with original embedded credits" loading="lazy" decoding="async">
</a>
<figcaption class="media-content">
<p class="page-kicker">Card <?= $index + 1 ?></p>
<p>Original credits retained. Open full size to read.</p>
<a href="<?= e(xuverse_asset($card)) ?>" class="text-link">Open full size</a>
</figcaption>
</figure>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</div>
</section>

<section class="section dark-band" id="videos" aria-labelledby="videos-title">
<div class="container">
<div class="section-heading reveal">
<div>
<h2 id="videos-title">Videos</h2>
<p>Watch the selected videos here, or open them on YouTube.</p>
</div>
</div>
<div class="media-grid video-grid">
<?php foreach ($videos as $video): $embedUrl = xuverse_youtube_embed($video['url']); ?>
<article class="media-card video-card reveal">
<div class="video-detail-frame media-player">
<iframe src="<?= e($embedUrl) ?>" title="<?= e($video['title']) ?>" width="1600" height="900" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>
<div class="media-content">
<p class="page-kicker">Video</p>
<h3><a href="<?= e(xuverse_url('videos/' . $video['slug'])) ?>"><?= e($video['title']) ?></a></h3>
<p><?= e($video['summary']) ?></p>
<?php if (!empty($video['credit_note'])): ?>
<p class="media-credit"><?= e($video['credit_note']) ?></p>
<?php endif; ?>
<div class="project-buttons">
<a href="<?= e(xuverse_url('videos/' . $video['slug'])) ?>" class="text-link">Video details</a>
<a href="<?= e($video['url']) ?>" target="_blank" rel="noopener noreferrer" class="text-link">YouTube ↗</a>
</div>
</div>
</article>
<?php endforeach; ?>
</div>
</div>
</section>

<?php include 'includes/footer.php'; ?>
