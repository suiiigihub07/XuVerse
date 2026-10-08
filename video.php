<?php
require_once 'includes/content.php';
$video = xuverse_find_public('media', $_GET['slug'] ?? '', (int)($_GET['id'] ?? 0));
if (!$video || $video['type'] !== 'video') {
    http_response_code(404);
    require '404.php';
    exit;
}
if (empty($_GET['slug'])) {
    header('Location: ' . xuverse_url('videos/' . $video['slug']), true, 301);
    exit;
}
$embedUrl = xuverse_youtube_embed($video['url']);
$videoId = basename(parse_url($embedUrl, PHP_URL_PATH) ?? '');
$pageImage = 'https://i.ytimg.com/vi/' . $videoId . '/hqdefault.jpg';
$schemaData = [
    '@type' => 'VideoObject',
    'name' => $video['title'],
    'description' => $video['summary'],
    'thumbnailUrl' => [$pageImage],
    'contentUrl' => $video['url'],
    'embedUrl' => $embedUrl
];
xuverse_public_start($video['title'], $video['summary'], 'videos/' . $video['slug']);
?>

<section class="section media-detail-section">
<div class="container">
<article class="media-detail reveal">
<div class="media-detail-frame video-detail-frame media-player">
<iframe src="<?= e($embedUrl) ?>" title="<?= e($video['title']) ?>" width="1600" height="900" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>
<div class="media-detail-info">
<h1><?= e($video['title']) ?></h1>
<div class="prose"><?= xuverse_markdown(!empty($video['description']) ? $video['description'] : $video['summary']) ?></div>
<?php if (!empty($video['credit_note'])): ?>
<p class="media-credit"><?= e($video['credit_note']) ?></p>
<?php endif; ?>
<div class="project-buttons">
<a href="<?= e($video['url']) ?>" target="_blank" rel="noopener noreferrer" class="btn">Watch on YouTube</a>
<a href="<?= e(xuverse_url('media#videos')) ?>" class="text-link">Back to videos</a>
</div>
</div>
</article>
</div>
</section>

<?php include 'includes/footer.php'; ?>
