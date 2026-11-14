<?php
require_once __DIR__ . '/includes/content.php';

$copy = xuverse_content('copy');
$site = xuverse_content('site');
$projects = xuverse_published('projects');
$featuredSlug = $site['featured_project'] ?? '';
usort($projects, static fn($a, $b) => ($b['slug'] === $featuredSlug) <=> ($a['slug'] === $featuredSlug));
xuverse_public_start($site['projects_page_heading'], $copy['projects_intro'], 'projects');
?>
<section class="xw-shell xu-tone-light">
<div class="xw-page xw-container">
    <header class="xw-index-head reveal">
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
        <article class="xw-case reveal" data-search="<?= e(implode(' ', array_merge([$item['status'] ?? ''], $item['technology'] ?? []))) ?>">
            <a class="xw-case-link<?= $image ? ' xw-case-with-image' : '' ?>" href="<?= e(xuverse_url('projects/' . $item['slug'])) ?>" aria-labelledby="<?= e('project-title-' . $item['slug']) ?>">
            <?php if ($image): ?>
            <figure class="xw-case-preview xu-image-reveal">
                <img src="<?= e(xuverse_asset($image)) ?>" <?= xuverse_image_attributes($image) ?> <?= xuverse_responsive_image_attributes($image, '(max-width: 760px) 90vw, (max-width: 1280px) 45vw, 580px') ?> alt="<?= e($item['image_alt'] ?? '') ?>" <?= $isLead ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async">
            </figure>
            <?php endif; ?>
            <div class="xw-case-copy">
                <h2 id="<?= e('project-title-' . $item['slug']) ?>"><span><?= e($item['title']) ?></span></h2>
                <p><?= e($item['summary']) ?></p>
            </div>
            </a>
        </article>
        <?php endforeach; ?>
    </div>
</div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
