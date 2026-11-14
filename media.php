<?php
require_once __DIR__ . '/includes/content.php';
require_once __DIR__ . '/includes/media-gallery.php';
$site = xuverse_content('site');
$media = xuverse_published('media');
$photos = array_filter($media, fn($item) => $item['type'] === 'photograph');
$videos = array_filter($media, fn($item) => $item['type'] === 'video');
$published = xuverse_published('published');
// Older CMS card series keep their photo URLs, but belong alongside publications.
// Prefer the dedicated collection when both sources share an existing address.
$publishedSlugs = array_column($published, 'slug');
foreach ($media as $item) {
    if ($item['type'] === 'card series' && !in_array($item['slug'], $publishedSlugs, true)) {
        $published[] = $item;
        $publishedSlugs[] = $item['slug'];
    }
}
xuverse_public_start('Media', 'Photographs, videos, and selected published work by B K Suraj.', 'media');
?>

<div class="xw-media xu-tone-dark">
    <header class="container xw-media-header reveal">
        <h1><?= e($site['media_page_heading']) ?></h1>
        <nav class="media-categories" aria-label="Media categories">
            <a href="#photographs" aria-current="true">Photographs</a>
            <a href="#videos">Videos</a>
            <a href="#published">Published Work</a>
        </nav>
    </header>

    <section class="media-category" id="photographs" aria-labelledby="photographs-title">
        <div class="container">
            <h2 class="xw-media-label" id="photographs-title"><?= e($site['photographs_heading']) ?></h2>
            <div class="xw-photo-layout">
            <?php foreach ($photos as $index => $photo): ?>
                <?php $photoSizes = '(max-width: 560px) 90vw, 380px'; ?>
                <article class="photo-card xw-photo reveal">
                    <a href="<?= e(xuverse_url('photos/' . $photo['slug'])) ?>" class="media-link xu-image-reveal" data-full-image="<?= e(xuverse_asset($photo['image'])) ?>">
                        <picture>
                            <?= xuverse_avif_source($photo['image'], $photoSizes) ?>
                            <img src="<?= e(xuverse_asset($photo['image'])) ?>"
                                <?= xuverse_image_attributes($photo['image']) ?>
                                <?= xuverse_responsive_image_attributes($photo['image'], $photoSizes) ?>
                                alt="<?= e(trim($photo['alt'] ?? '') !== '' ? $photo['alt'] : $photo['title']) ?>"
                                <?php if ($index === array_key_first($photos)): ?>fetchpriority="high"<?php else: ?>loading="lazy"<?php endif; ?>
                                decoding="async">
                        </picture>
                    </a>
                    <h3><a class="xw-media-link" href="<?= e(xuverse_url('photos/' . $photo['slug'])) ?>"><?= e($photo['title']) ?></a></h3>
                </article>
            <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="media-category" id="videos" aria-labelledby="videos-title">
        <div class="container">
            <h2 class="xw-media-label" id="videos-title"><?= e($site['videos_heading']) ?></h2>
            <div class="xw-video-layout">
            <?php foreach ($videos as $index => $video): ?>
                <?php
                $videoUrl = xuverse_url('videos/' . $video['slug']);
                $posterSizes = $index === array_key_first($videos) ? '(max-width: 760px) 90vw, 64vw' : '(max-width: 760px) 90vw, 28vw';
                ?>
                <article class="xw-video reveal">
                    <?php xuverse_inline_video($video, $videoUrl, $posterSizes); ?>
                    <h3><a class="xw-media-link" href="<?= e($videoUrl) ?>"><?= e($video['title']) ?></a></h3>
                </article>
            <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="media-category" id="published" aria-labelledby="published-title">
        <div class="container">
            <h2 class="xw-media-label" id="published-title"><?= e($site['published_heading']) ?></h2>
            <div class="xw-published-list">
                <?php foreach ($published as $post) xuverse_published_card($post); ?>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
