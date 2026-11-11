<?php
require_once __DIR__ . '/includes/content.php';

$copy = xuverse_content('copy');
$site = xuverse_content('site');
$articles = xuverse_published('articles');
$featuredSlug = $site['featured_article'] ?? '';
if (!in_array($featuredSlug, array_column($articles, 'slug'), true)) {
    $featuredSlug = $articles[0]['slug'] ?? '';
}
usort($articles, static fn($a, $b) => ($b['slug'] === $featuredSlug) <=> ($a['slug'] === $featuredSlug));
xuverse_public_start($site['articles_page_heading'], $copy['articles_intro'], 'articles');
?>
<section class="xw-page xw-container">
    <header class="xw-index-head">
        <h1><?= e($site['articles_page_heading']) ?></h1>
        <p><?= e($copy['articles_intro']) ?></p>
    </header>
    <div class="xw-collection-tools" data-collection-tools hidden>
        <div class="xw-search">
            <label for="content-search">Search articles</label>
            <div class="xw-search-fields">
                <input type="search" id="content-search" aria-controls="content-results">
                <button type="button" class="xw-control" data-clear-search>Clear</button>
            </div>
        </div>
        <p class="collection-count" role="status" aria-live="polite"></p>
    </div>
    <p class="xw-empty" data-collection-empty hidden>No matches. Try another word.</p>
    <div id="content-results" data-collection>
        <?php foreach ($articles as $item):
            $isFeatured = $item['slug'] === $featuredSlug;
            $isResearch = !empty($item['study_type']);
            $image = ($item['image'] ?? '') ?: ($item['cover'] ?? '');
            $category = $item['category'] ?? $item['type'] ?? '';
            $dateTime = ($item['date_precision'] ?? 'year') === 'year' ? '' : ($item['written_date'] ?? '');
            $searchTerms = array_merge([$item['subtitle'] ?? '', $item['status'] ?? ''], $item['tags'] ?? []);
        ?>
        <article class="xw-entry<?= $isFeatured ? ' xw-entry-feature' : '' ?><?= $image ? ' xw-entry-has-image' : '' ?>" data-search="<?= e(implode(' ', $searchTerms)) ?>">
            <div class="xw-entry-meta">
                <?php if ($category): ?><p class="xw-kicker"><?= e($category) ?></p><?php endif; ?>
                <?php if (!empty($item['date_display']) || !empty($item['reading_time_minutes'])): ?>
                <p class="xw-date-line">
                    <?php if (!empty($item['date_display'])): ?>
                    <time<?= $dateTime === '' ? '' : ' datetime="' . e($dateTime) . '"' ?>><?= e($item['date_display']) ?></time>
                    <?php endif; ?>
                    <?php if (!empty($item['reading_time_minutes'])): ?>
                    <span><?= e($item['reading_time_minutes']) ?> min read</span>
                    <?php endif; ?>
                </p>
                <?php endif; ?>
            </div>
            <div class="xw-entry-copy">
                <h2><a href="<?= e(xuverse_url('articles/' . $item['slug'])) ?>"><?= e($item['title']) ?><span aria-hidden="true">→</span></a></h2>
                <p><?= e($item['summary']) ?></p>
                <?php if ($isResearch && !empty($item['status'])): ?>
                <p class="xw-entry-status"><?= e($item['status']) ?></p>
                <?php endif; ?>
            </div>
            <?php if ($image): ?>
            <figure class="xw-entry-image">
                <img src="<?= e(xuverse_asset($image)) ?>" <?= xuverse_image_attributes($image) ?> <?= xuverse_responsive_image_attributes($image, '(max-width: 760px) 82vw, 38vw') ?> alt="<?= e($item['image_alt'] ?? $item['cover_alt'] ?? '') ?>" loading="lazy" decoding="async">
            </figure>
            <?php elseif ($isFeatured): ?>
            <div class="xw-issue-art" aria-hidden="true"><span></span></div>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
