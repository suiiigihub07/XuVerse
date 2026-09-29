<?php

require_once 'includes/db.php';
require_once 'includes/functions.php';

$settings = $conn->query(
    "SELECT * FROM settings
     LIMIT 1"
)->fetch_assoc();

$result = $conn->query(
    "SELECT * FROM articles
     WHERE published = 1
       AND LOWER(TRIM(title)) <> 'hi'
     ORDER BY id DESC"
);

$pageTitle = 'Articles | ' . xuverse_person_name($conn);
$pageDescription = 'Writing and learning notes from ' . xuverse_person_name($conn) . ' about projects, creative work and life.';
$canonicalUrl = xuverse_base_url() . '/articles';

include 'includes/header.php';
include 'includes/navbar.php';

?>

<section class="page-hero">
<div class="container">
<p class="page-kicker">Writing</p>
<h1>Articles</h1>
<p>What I am learning, how I build, and the technical, creative and campus experiences behind the work.</p>
</div>
</section>

<section class="section">
<div class="container">

<?php if($result && $result->num_rows > 0): ?>

<div class="article-grid">

<?php while($row = $result->fetch_assoc()): ?>

<article class="article-card reveal">
<?php if(!empty($row['image_path'])): ?>
<a href="<?= e(xuverse_url('articles/' . (int)$row['id'])) ?>" class="article-image-link">
<img src="<?= e(xuverse_asset($row['image_path'])) ?>" <?= xuverse_image_attributes($row['image_path']) ?> <?= xuverse_responsive_image_attributes($row['image_path'], '(max-width: 720px) 94vw, 48vw') ?> class="article-image" alt="<?= e($row['title']) ?>" loading="lazy" decoding="async">
</a>
<?php endif; ?>

<div class="article-card-body">
<div class="article-meta">Published <?= e(date('M j, Y', strtotime($row['created_at'] ?? 'now'))) ?></div>
<h2><a href="<?= e(xuverse_url('articles/' . (int)$row['id'])) ?>"><?= e($row['title']) ?></a></h2>
<p class="article-preview"><?= e(xuverse_excerpt($row['content'], 240)) ?></p>
<a href="<?= e(xuverse_url('articles/' . (int)$row['id'])) ?>" class="text-link">Read article</a>
</div>
</article>

<?php endwhile; ?>

</div>

<?php else: ?>

<div class="empty-state">
<h2>Articles coming soon</h2>
<p>New writing and learning notes will appear here.</p>
</div>

<?php endif; ?>

</div>
</section>

<?php include 'includes/footer.php'; ?>
