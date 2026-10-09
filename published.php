<?php
require_once 'includes/content.php';
require_once 'includes/media-gallery.php';
if (empty($_GET['slug'])) {
    header('Location: ' . xuverse_url('media#photographs'), true, 301);
    exit;
}
$post = $publishedPost ?? xuverse_find_public('published', $_GET['slug']);
if (!$post) { http_response_code(404); require '404.php'; exit; }
$images = xuverse_gallery_images($post);
if ($images) $pageImage = $images[0];
$schemaData = ['@type'=>'CreativeWork','name'=>$post['title'],'description'=>$post['summary'],'url'=>xuverse_absolute_url('photos/' . $post['slug'])];
xuverse_public_start($post['title'], $post['summary'], 'photos/' . $post['slug']);
?>
<section class="section media-detail-section">
<div class="container">
<article class="media-detail published-detail reveal">
<?php if ($images): ?><div class="published-detail-images"><?php xuverse_gallery($post, 'published-detail-gallery'); ?></div><?php endif; ?>
<?php if (!empty($post['video_urls'])): ?><div class="published-detail-videos">
<?php foreach ($post['video_urls'] as $index=>$url) xuverse_published_video($url, $post['title'] . (count($post['video_urls']) > 1 ? ' — video ' . ($index + 1) : '')); ?>
</div><?php endif; ?>
<div class="media-detail-info">
<h1><?= e($post['title']) ?></h1>
<?php $description = trim($post['description'] ?? '') ?: $post['summary']; if ($description !== ''): ?><div class="prose"><?= xuverse_markdown($description) ?></div><?php endif; ?>
<?php if (!empty($post['article_body'])): ?><div class="prose published-article-body"><?= xuverse_markdown($post['article_body']) ?></div><?php endif; ?>
<?php if (!empty($post['credit_note'])): ?><p class="media-credit"><?= e($post['credit_note']) ?></p><?php endif; ?>
<div class="project-buttons">
<a class="text-link" href="<?= e(xuverse_url('media#published')) ?>">← Back to published</a>
<a class="text-link" href="<?= e(xuverse_url()) ?>">Home</a>
</div>
</div>
</article>
</div>
</section>
<?php include 'includes/footer.php'; ?>
