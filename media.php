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

<div class="personal-page personal-media">
    <header class="container personal-media-header">
        <h1 class="personal-title"><?= e($site['media_page_heading']) ?></h1>
        <nav class="media-categories" aria-label="Media categories">
            <a href="#photographs" aria-current="true">Photographs</a>
            <a href="#videos">Videos</a>
            <a href="#published">Published Work</a>
        </nav>
    </header>

    <section class="media-category" id="photographs" aria-labelledby="photographs-title">
        <div class="container">
            <div class="section-heading"><h2 id="photographs-title"><?= e($site['photographs_heading']) ?></h2></div>
            <div class="personal-photo-layout">
            <?php foreach ($photos as $index => $photo): ?>
                <?php $photoSizes = '(max-width: 720px) 94vw, (max-width: 1040px) 88vw, 900px'; ?>
                <article class="photo-card personal-photo">
                    <a href="<?= e(xuverse_url('photos/' . $photo['slug'])) ?>" class="media-link" data-full-image="<?= e(xuverse_asset($photo['image'])) ?>">
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
                    <h3><a href="<?= e(xuverse_url('photos/' . $photo['slug'])) ?>"><?= e($photo['title']) ?><span aria-hidden="true">↗</span></a></h3>
                </article>
            <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="media-category" id="videos" aria-labelledby="videos-title">
        <div class="container">
            <div class="section-heading"><h2 id="videos-title"><?= e($site['videos_heading']) ?></h2></div>
            <div class="personal-video-layout">
            <?php foreach ($videos as $index => $video): ?>
                <?php
                $videoUrl = xuverse_url('videos/' . $video['slug']);
                $posterSizes = $index === array_key_first($videos) ? '(max-width: 1040px) 94vw, 1000px' : '(max-width: 720px) 94vw, 46vw';
                ?>
                <article class="personal-video">
                    <?php xuverse_inline_video($video, $videoUrl, $posterSizes); ?>
                    <h3><a href="<?= e($videoUrl) ?>"><?= e($video['title']) ?><span aria-hidden="true">↗</span></a></h3>
                </article>
            <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="media-category" id="published" aria-labelledby="published-title">
        <div class="container">
            <div class="section-heading"><h2 id="published-title"><?= e($site['published_heading']) ?></h2></div>
            <div class="personal-published-layout">
                <?php foreach ($published as $post) xuverse_published_card($post); ?>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
