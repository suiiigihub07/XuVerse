<?php
require_once __DIR__ . '/includes/content.php';

$copy = xuverse_content('copy');
$site = xuverse_content('site');
$projects = xuverse_published('projects');
$featuredSlug = $site['featured_project'] ?? '';
usort($projects, static fn($a, $b) => ($b['slug'] === $featuredSlug) <=> ($a['slug'] === $featuredSlug));
xuverse_public_start($site['projects_page_heading'], $copy['projects_intro'], 'projects');
?>
<section class="xw-page xw-container">
    <header class="xw-index-head">
        <h1><?= e($site['projects_page_heading']) ?></h1>
        <p><?= e($copy['projects_intro']) ?></p>
    </header>
    <div class="xw-collection-tools" data-collection-tools hidden>
        <div class="xw-search">
            <label for="content-search">Search projects</label>
            <div class="xw-search-fields">
                <input type="search" id="content-search" aria-controls="content-results">
                <button type="button" class="xw-control" data-clear-search>Clear</button>
            </div>
        </div>
        <p class="collection-count" role="status" aria-live="polite"></p>
    </div>
    <p class="xw-empty" data-collection-empty hidden>No matches. Try another word.</p>
    <div id="content-results" data-collection>
        <?php foreach ($projects as $index => $item):
            $image = $item['image'] ?? '';
            $isLead = $index === 0;
        ?>
        <article class="xw-case<?= $image ? ' xw-case-with-image' : '' ?>" data-search="<?= e(implode(' ', $item['technology'] ?? [])) ?>">
            <div class="xw-case-copy">
                <p class="xw-kicker"><?= e($item['status']) ?></p>
                <h2><a href="<?= e(xuverse_url('projects/' . $item['slug'])) ?>"><?= e($item['title']) ?><span aria-hidden="true">→</span></a></h2>
                <p><?= e($item['summary']) ?></p>
            </div>
            <?php if ($image): ?>
            <figure class="xw-case-preview">
                <img src="<?= e(xuverse_asset($image)) ?>" <?= xuverse_image_attributes($image) ?> <?= xuverse_responsive_image_attributes($image, '(max-width: 760px) 94vw, (max-width: 1280px) 52vw, 700px') ?> alt="<?= e($item['image_alt'] ?? '') ?>" <?= $isLead ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async">
            </figure>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
