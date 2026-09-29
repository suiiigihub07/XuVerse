<?php

require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM videos WHERE id=? AND is_published=1");
$stmt->bind_param("i", $id);
$stmt->execute();
$video = $stmt->get_result()->fetch_assoc();

if (!$video) {
    require '404.php';
    exit;
}

$pageTitle = $video['title'] . ' | ' . xuverse_person_name($conn);
$pageDescription = xuverse_excerpt($video['description'] ?? $video['title'], 155);
$pageImage = $video['thumbnail_path'] ?: 'uploads/avatars/6a2184f69a336.webp';
$canonicalUrl = xuverse_absolute_url('videos/' . (int)$video['id']);
$embedUrl = xuverse_youtube_embed($video['video_url'] ?? '');
$schemaData = [
    '@type' => 'VideoObject',
    'name' => $video['title'],
    'description' => $pageDescription,
    'thumbnailUrl' => [xuverse_absolute_url($pageImage)],
    'uploadDate' => date('c', strtotime($video['uploaded_at'] ?? 'now')),
    'contentUrl' => $video['video_url'],
    'embedUrl' => $embedUrl
];

include 'includes/header.php';
include 'includes/navbar.php';

?>

<section class="section media-detail-section">
<div class="container">

<article class="media-detail">
<div class="media-detail-frame video-detail-frame">
<?php if($embedUrl !== ''): ?>
<iframe
src="<?= e($embedUrl) ?>"
title="<?= e($video['title']) ?>"
allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
allowfullscreen></iframe>
<?php elseif(!empty($video['thumbnail_path'])): ?>
<img src="<?= e(xuverse_asset($video['thumbnail_path'])) ?>" <?= xuverse_image_attributes($video['thumbnail_path']) ?> alt="<?= e($video['title']) ?>" loading="eager" decoding="async">
<?php else: ?>
<div class="video-placeholder">Video</div>
<?php endif; ?>
</div>

<div class="media-detail-info">
<p class="page-kicker">Video</p>
<h1><?= e($video['title']) ?></h1>
<p>
<?= !empty($video['description'])
    ? nl2br(e($video['description']))
    : ''
?>
</p>

<div class="project-buttons">
<a href="<?= e($video['video_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn">Watch video</a>
<a href="<?= e(xuverse_url('media')) ?>" class="text-link">Back to media</a>
</div>
</div>
</article>

</div>
</section>

<?php include 'includes/footer.php'; ?>
