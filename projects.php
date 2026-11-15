<?php
require_once __DIR__ . '/includes/content.php';

$copy = xuverse_content('copy');
$site = xuverse_content('site');
$projects = xuverse_published('projects');
$featuredSlug = $site['featured_project'] ?? '';
usort($projects, static fn($a, $b) => ($b['slug'] === $featuredSlug) <=> ($a['slug'] === $featuredSlug));
xuverse_public_start($site['projects_page_heading'], $copy['projects_intro'], 'projects');
?>
<section class="xw-shell xw-project-index xu-tone-dark">
    <div class="xw-page xw-container xw-project-index-heading">
    <header class="xw-index-head reveal">
        <h1><?= e($site['projects_page_heading']) ?></h1>
        <p><?= e($copy['projects_intro']) ?></p>
    </header>
    <div class="xw-collection-tools" data-collection-tools hidden>
        <div class="xw-search">
            <label for="content-search"><?= e(xuverse_site_label('work_search_projects', 'Search projects')) ?></label>
            <div class="xw-search-fields">
                <input type="search" id="content-search" aria-controls="content-results">
                <button type="button" class="xw-control" data-clear-search><?= e(xuverse_site_label('work_search_clear', 'Clear')) ?></button>
            </div>
        </div>
        <p class="collection-count" role="status" aria-live="polite"></p>
    </div>
    <p class="xw-empty" data-collection-empty hidden><?= e(xuverse_site_label('work_search_empty', 'No matches. Try another word.')) ?></p>
    </div>
    <div id="content-results" data-collection>
        <?php foreach ($projects as $index => $item):
            $image = $item['image'] ?? '';
            $isLead = $index === 0;
            $category = ($item['category'] ?? '') ?: ($item['status'] ?? '');
            $summary = ($item['home_summary'] ?? '') ?: $item['summary'];
            $searchTerms = array_merge([$category, $item['status'] ?? '', $item['summary']], $item['technology'] ?? []);
        ?>
        <article class="xw-case" data-search="<?= e(implode(' ', $searchTerms)) ?>">
            <a class="xw-case-link xw-container reveal<?= $image ? ' xw-case-with-image' : '' ?>" href="<?= e(xuverse_url('projects/' . $item['slug'])) ?>" aria-labelledby="<?= e('project-title-' . $item['slug']) ?>">
                <?php if ($image): ?>
                <figure class="xw-case-preview xu-image-reveal">
                    <img src="<?= e(xuverse_asset($image)) ?>" <?= xuverse_image_attributes($image) ?> <?= xuverse_responsive_image_attributes($image, '(max-width: 760px) 90vw, (max-width: 1440px) 55vw, 780px') ?> alt="<?= e($item['image_alt'] ?? '') ?>" <?= $isLead ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async">
                </figure>
                <?php endif; ?>
                <div class="xw-case-copy">
                    <?php if ($category !== ''): ?><p class="xw-kicker"><?= e($category) ?></p><?php endif; ?>
                    <h2 id="<?= e('project-title-' . $item['slug']) ?>"><span><?= e($item['title']) ?></span></h2>
                    <p class="xw-case-summary"><?= e($summary) ?></p>
                </div>
            </a>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
