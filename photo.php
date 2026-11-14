<?php
require_once 'includes/content.php';
require_once 'includes/media-gallery.php';
$publishedPost = xuverse_find_public('published', $_GET['slug'] ?? '', (int)($_GET['id'] ?? 0));
if ($publishedPost) {
    if (empty($_GET['slug'])) {
        header('Location: ' . xuverse_url('photos/' . $publishedPost['slug']), true, 301);
        exit;
    }
    require 'published.php';
    exit;
}
$photo = xuverse_find_public('media', $_GET['slug'] ?? '', (int)($_GET['id'] ?? 0));
if (!$photo || !in_array($photo['type'], ['photograph','card series'], true)) {
    http_response_code(404);
    require '404.php';
    exit;
}
if (empty($_GET['slug'])) {
    header('Location: ' . xuverse_url('photos/' . $photo['slug']), true, 301);
    exit;
}
$pageImage = xuverse_gallery_images($photo)[0];
$schemaData = [
    '@type' => 'ImageObject',
    'name' => $photo['title'],
    'description' => $photo['summary'],
    'contentUrl' => xuverse_absolute_url($pageImage)
];
xuverse_public_start($photo['title'], $photo['summary'], 'photos/' . $photo['slug'], $photo['type'] === 'card series' ? 'published' : 'photos');
?>

<section class="xw-media-detail xw-photo-detail xu-tone-dark media-detail-section">
<div class="container">
<article class="media-detail xw-visual-detail">
<div class="xw-detail-visual reveal">
<?php if ($photo['type'] === 'card series' || count(xuverse_gallery_images($photo)) > 1): ?>
<?php xuverse_gallery($photo, 'detail-gallery'); ?>
<?php else: ?>
<div class="xw-photo-frame xu-image-reveal">
<picture class="responsive-picture">
<?php $sizes = '(max-width: 560px) 90vw, 440px'; ?>
<?= xuverse_avif_source($photo['image'], $sizes) ?>
<img src="<?= e(xuverse_asset($photo['image'])) ?>" <?= xuverse_image_attributes($photo['image']) ?> <?= xuverse_responsive_image_attributes($photo['image'], $sizes) ?> alt="<?= e(trim($photo['alt'] ?? '') !== '' ? $photo['alt'] : $photo['title']) ?>" fetchpriority="high" decoding="async">
</picture>
</div>
<?php endif; ?>
</div>
<div class="media-detail-info reveal">
<h1><?= e($photo['title']) ?></h1>
<div class="prose"><?= xuverse_markdown(!empty($photo['description']) ? $photo['description'] : $photo['summary']) ?></div>
<?php if (!empty($photo['credit_note'])): ?><p class="media-credit"><?= e($photo['credit_note']) ?></p><?php endif; ?>
<div class="xw-media-actions">
<a href="<?= e(xuverse_asset($pageImage)) ?>" class="xw-media-action">View full size</a>
<a href="<?= e(xuverse_url($photo['type'] === 'card series' ? 'media#published' : 'media#photographs')) ?>" class="xw-media-link"><?= $photo['type'] === 'card series' ? 'All published work' : 'All photographs' ?></a>
</div>
</div>
</article>
</div>
</section>

<?php include 'includes/footer.php'; ?>
