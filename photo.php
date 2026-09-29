<?php

require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM photos WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$photo = $stmt->get_result()->fetch_assoc();

if (!$photo) {
    require '404.php';
    exit;
}

$pageTitle = $photo['title'] . ' | ' . xuverse_person_name($conn);
$pageDescription = xuverse_excerpt($photo['description'] ?? $photo['title'], 155);
$pageImage = $photo['image_path'] ?: 'uploads/avatars/6a2184f69a336.webp';
$canonicalUrl = xuverse_absolute_url('photos/' . (int)$photo['id']);
$schemaData = [
    '@type' => 'ImageObject',
    'name' => $photo['title'],
    'description' => $pageDescription,
    'contentUrl' => xuverse_absolute_url($pageImage),
    'uploadDate' => date('c', strtotime($photo['uploaded_at'] ?? 'now'))
];

include 'includes/header.php';
include 'includes/navbar.php';

?>

<section class="section media-detail-section">
<div class="container">

<article class="media-detail">
<div class="media-detail-frame">
<?php if(!empty($photo['image_path'])): ?>
<picture class="responsive-picture">
<?= xuverse_avif_source($photo['image_path'], '94vw') ?>
<img
src="<?= e(xuverse_asset($photo['image_path'])) ?>"
<?= xuverse_image_attributes($photo['image_path']) ?>
<?= xuverse_responsive_image_attributes($photo['image_path'], '94vw') ?>
alt="<?= e($photo['title']) ?>"
loading="eager"
decoding="async">
</picture>
<?php endif; ?>
</div>

<div class="media-detail-info">
<p class="page-kicker">Photo</p>
<h1><?= e($photo['title']) ?></h1>
<?php if(!empty($photo['description'])): ?>
<p><?= nl2br(e($photo['description'])) ?></p>
<?php endif; ?>
<a href="<?= e(xuverse_url('media')) ?>" class="text-link">Back to media</a>
</div>
</article>

</div>
</section>

<?php include 'includes/footer.php'; ?>
