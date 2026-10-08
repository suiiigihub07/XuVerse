<?php
require_once 'includes/content.php';
$photo = xuverse_find_public('media', $_GET['slug'] ?? '', (int)($_GET['id'] ?? 0));
if (!$photo || $photo['type'] !== 'photograph') {
    http_response_code(404);
    require '404.php';
    exit;
}
if (empty($_GET['slug'])) {
    header('Location: ' . xuverse_url('photos/' . $photo['slug']), true, 301);
    exit;
}
$pageImage = $photo['image'];
$schemaData = [
    '@type' => 'ImageObject',
    'name' => $photo['title'],
    'description' => $photo['summary'],
    'contentUrl' => xuverse_absolute_url($photo['image'])
];
xuverse_public_start($photo['title'], $photo['summary'], 'photos/' . $photo['slug']);
?>

<section class="section media-detail-section">
<div class="container">
<article class="media-detail reveal">
<div class="media-detail-frame photograph-detail-frame">
<picture class="responsive-picture">
<img src="<?= e(xuverse_asset($photo['image'])) ?>" <?= xuverse_image_attributes($photo['image']) ?> alt="<?= e($photo['alt']) ?>" loading="eager" decoding="async">
</picture>
</div>
<div class="media-detail-info">
<p class="page-kicker">Photograph</p>
<h1><?= e($photo['title']) ?></h1>
<p><?= e($photo['summary']) ?></p>
<div class="project-buttons">
<a href="<?= e(xuverse_asset($photo['image'])) ?>" class="btn">View full size</a>
<a href="<?= e(xuverse_url('media#photographs')) ?>" class="text-link">Back to photographs</a>
</div>
</div>
</article>
</div>
</section>

<?php include 'includes/footer.php'; ?>
