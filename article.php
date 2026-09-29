<?php

require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM articles WHERE id=? AND published=1 AND LOWER(TRIM(title)) <> 'hi'");
$stmt->bind_param("i", $id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

if (!$article) {
    http_response_code(404);
    require '404.php';
    exit;
}

$pageTitle = $article['title'] . ' | ' . xuverse_person_name($conn);
$pageDescription = xuverse_excerpt($article['content'], 155);
$pageType = 'article';
$pageImage = $article['image_path'] ?: 'uploads/avatars/6a2184f69a336.webp';
$canonicalUrl = xuverse_base_url() . '/articles/' . (int)$article['id'];
$readingTime = xuverse_estimated_reading_time($article['content']);
$schemaData = [
    '@type' => 'Article',
    'headline' => $article['title'],
    'description' => $pageDescription,
    'image' => xuverse_absolute_url($pageImage),
    'datePublished' => date('c', strtotime($article['created_at'] ?? 'now')),
    'author' => [
        '@type' => 'Person',
        'name' => xuverse_person_name($conn)
    ],
    'mainEntityOfPage' => $canonicalUrl
];

include 'includes/header.php';
include 'includes/navbar.php';

?>

<section class="section article-detail-section">
<div class="container">

<article class="article-detail">
<?php if(!empty($article['image_path'])): ?>
<picture class="responsive-picture">
<?= xuverse_avif_source($article['image_path'], '94vw') ?>
<img
src="<?= e(xuverse_asset($article['image_path'])) ?>"
<?= xuverse_image_attributes($article['image_path']) ?>
<?= xuverse_responsive_image_attributes($article['image_path'], '94vw') ?>
class="article-detail-image"
alt="<?= e($article['title']) ?>"
loading="eager"
decoding="async">
</picture>
<?php endif; ?>

<p class="page-kicker">Article</p>
<h1><?= e($article['title']) ?></h1>
<div class="article-toolbar">
<p class="article-date"><time datetime="<?= e(date('Y-m-d', strtotime($article['created_at'] ?? 'now'))) ?>"><?= e(date('M j, Y', strtotime($article['created_at'] ?? 'now'))) ?></time> · <?= $readingTime ?> min read</p>
<button type="button" class="share-button" data-share-url="<?= e($canonicalUrl) ?>" data-share-title="<?= e($article['title']) ?>">Share</button>
</div>

<div class="article-body">
<?= xuverse_prose($article['content']) ?>
</div>

<a href="<?= e(xuverse_url('articles')) ?>" class="text-link">Back to articles</a>
</article>

</div>
</section>

<?php include 'includes/footer.php'; ?>
