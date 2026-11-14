<?php
require_once __DIR__ . '/includes/content.php';

$article = xuverse_find_public('articles', $_GET['slug'] ?? '', (int)($_GET['id'] ?? 0));
if (!$article) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$path = 'articles/' . $article['slug'];
if (empty($_GET['slug'])) {
    header('Location: ' . xuverse_url($path), true, 301);
    exit;
}

$site = xuverse_content('site');
$isResearch = !empty($article['study_type']);
$category = $article['category'] ?? $article['type'] ?? '';
$dateTime = ($article['date_precision'] ?? 'year') === 'year' ? '' : ($article['written_date'] ?? '');
$cover = ($article['image'] ?? '') ?: ($article['cover'] ?? '');
$pageType = 'article';
$schemaData = [
    '@type' => 'Article',
    'headline' => $article['title'],
    'description' => $article['summary'],
];
if (!empty($article['author'])) {
    $schemaData['author'] = ['@type' => 'Person', 'name' => $article['author']];
}
if (!empty($article['published_at'])) {
    $schemaData['datePublished'] = $article['published_at'];
}
xuverse_public_start($article['title'], $article['summary'], $path);
?>
<div class="xw-shell xu-tone-light">
<article class="xw-page xw-container xw-article-page<?= $isResearch ? ' xw-article-research' : '' ?>">
    <header class="xw-reading-head reveal">
        <?php if ($category): ?><p class="xw-kicker"><?= e($category) ?></p><?php endif; ?>
        <h1><?= e($article['title']) ?></h1>
        <?php if (!empty($article['subtitle'])): ?>
        <p class="xw-reading-lead"><?= e($article['subtitle']) ?></p>
        <?php endif; ?>
        <?php if (!empty($article['author']) || !empty($article['date_display']) || !empty($article['reading_time_minutes'])): ?>
        <div class="xw-reading-meta">
            <?php if (!empty($article['author'])): ?><span><?= e($article['author']) ?></span><?php endif; ?>
            <?php if (!empty($article['date_display'])): ?>
            <time<?= $dateTime === '' ? '' : ' datetime="' . e($dateTime) . '"' ?>><?= e($article['date_display']) ?></time>
            <?php endif; ?>
            <?php if (!empty($article['reading_time_minutes'])): ?><span><?= e($article['reading_time_minutes']) ?> min read</span><?php endif; ?>
        </div>
        <?php endif; ?>
    </header>

    <?php if ($cover): ?>
    <figure class="xw-article-cover xu-image-reveal reveal">
        <img src="<?= e(xuverse_asset($cover)) ?>" <?= xuverse_image_attributes($cover) ?> <?= xuverse_responsive_image_attributes($cover, '(max-width: 1015px) 88vw, 928px') ?> alt="<?= e($article['image_alt'] ?? $article['cover_alt'] ?? '') ?>" decoding="async">
    </figure>
    <?php endif; ?>

    <div class="xw-reading-content">
        <?php if ($isResearch): ?>
        <dl class="xw-research-facts">
            <div><dt>Study type</dt><dd><?= e($article['study_type']) ?></dd></div>
            <?php if (!empty($article['status'])): ?>
            <div><dt>Publication status</dt><dd><?= e($article['status']) ?></dd></div>
            <?php endif; ?>
            <?php if (!empty($article['contribution'])): ?>
            <div><dt>Contribution</dt><dd><?= e($article['contribution']) ?></dd></div>
            <?php endif; ?>
        </dl>
        <?php endif; ?>

        <div class="xw-reading-actions">
            <?php if (!empty($article['pdf_path'])): ?>
            <a class="xw-control" href="<?= e(xuverse_url($article['pdf_path'])) ?>" download>Download PDF</a>
            <?php endif; ?>
            <button type="button" class="xw-control share-button" data-share-url="<?= e(xuverse_base_url() . '/' . $path) ?>" data-share-title="<?= e($article['title']) ?>" hidden>Share</button>
            <p class="xw-share-status" role="status" data-share-status></p>
        </div>

        <div class="article-body"><?= xuverse_markdown(xuverse_body($article)) ?></div>

        <?php if (!empty($article['references'])): ?>
        <aside class="xw-source-notes" aria-labelledby="source-access">
            <h2 id="source-access"><?= e($site['source_access_heading']) ?></h2>
            <p><?= e($article['source_note'] ?? $site['research_source_note']) ?></p>
            <ul>
                <?php foreach ($article['references'] as $reference): ?>
                <li>
                    <a href="<?= e($reference['url']) ?>"><?= e($reference['citation']) ?></a>
                    <p><?= e($reference['access']) ?></p>
                </li>
                <?php endforeach; ?>
            </ul>
        </aside>
        <?php endif; ?>

        <?php $related = array_filter(xuverse_published('articles'), static fn($item) => $item['slug'] !== $article['slug']); ?>
        <footer class="xw-reading-footer reveal">
            <?php if ($related): ?>
            <h2>Related reading</h2>
            <ul>
                <?php foreach ($related as $item): ?>
                <li><a href="<?= e(xuverse_url('articles/' . $item['slug'])) ?>"><?= e($item['title']) ?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <a class="xw-return" href="<?= e(xuverse_url('articles')) ?>">All articles</a>
        </footer>
    </div>
</article>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
