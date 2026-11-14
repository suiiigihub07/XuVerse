<?php
require_once __DIR__ . '/content.php';

function xuverse_gallery_images($item) {
    return array_slice($item['cards'] ?? $item['images'] ?? (empty($item['image']) ? [] : [$item['image']]), 0, 10);
}

function xuverse_gallery($item, $id) {
    $images = xuverse_gallery_images($item);
    if (!$images) return;
    ?>
    <div class="post-gallery" data-post-gallery aria-label="<?= e($item['title']) ?> images">
      <div class="post-gallery-track" id="<?= e($id) ?>" tabindex="0" aria-label="Swipe images or use arrow keys">
      <?php foreach ($images as $i => $path): ?>
        <figure class="post-gallery-slide" aria-label="Image <?= $i + 1 ?> of <?= count($images) ?>">
          <img src="<?= e(xuverse_asset($path)) ?>" <?= xuverse_image_attributes($path) ?> alt="<?= e((!empty($item['alt']) ? $item['alt'] : $item['title']) . (count($images) > 1 ? ' — image ' . ($i + 1) : '')) ?>" loading="<?= $i ? 'lazy' : 'eager' ?>" decoding="async">
        </figure>
      <?php endforeach; ?>
      </div>
      <?php if (count($images) > 1): ?>
      <div class="post-gallery-controls" data-gallery-controls>
        <button type="button" data-gallery-previous aria-controls="<?= e($id) ?>" aria-label="Previous image">←</button>
        <span data-gallery-counter aria-live="polite">1 / <?= count($images) ?></span>
        <button type="button" data-gallery-next aria-controls="<?= e($id) ?>" aria-label="Next image">→</button>
      </div>
      <?php endif; ?>
    </div>
    <?php
}

function xuverse_published_card($post) {
    $images = xuverse_gallery_images($post);
    $url = xuverse_url('photos/' . $post['slug']);
    $modalId = 'published-gallery-' . $post['slug'];
    ?>
    <article class="xw-published-entry<?= $images || !empty($post['video_urls']) ? ' xw-published-has-preview' : '' ?> reveal" id="<?= e($post['slug']) ?>">
    <?php if ($images): ?>
      <a href="<?= e($url) ?>" class="gallery-post-cover xu-image-reveal" data-open-gallery="<?= e($modalId) ?>" aria-label="Browse <?= count($images) ?> images: <?= e($post['title']) ?>">
        <img src="<?= e(xuverse_asset($images[0])) ?>" <?= xuverse_image_attributes($images[0]) ?> <?= xuverse_responsive_image_attributes($images[0], '(max-width: 560px) 76vw, 250px') ?> alt="<?= e($post['alt'] ?? '') ?>" loading="lazy" decoding="async">
        <?php if (count($images) > 1): ?><span class="gallery-count" aria-hidden="true">1 / <?= count($images) ?></span><?php endif; ?>
      </a>
    <?php elseif (!empty($post['video_urls'])): ?>
      <?php xuverse_inline_video(['url' => $post['video_urls'][0], 'title' => $post['title']], $url, '(max-width: 560px) 85vw, 320px'); ?>
    <?php endif; ?>
      <div class="xw-published-copy">
        <h3><a class="xw-media-link" href="<?= e($url) ?>"><?= e($post['title']) ?></a></h3>
        <?php if (!empty($post['summary'])): ?><p><?= e($post['summary']) ?></p><?php endif; ?>
      </div>
    </article>
    <?php if ($images): ?>
    <dialog class="gallery-modal" id="<?= e($modalId) ?>" aria-label="<?= e($post['title']) ?> gallery">
      <div class="gallery-modal-toolbar"><a class="xw-media-link" href="<?= e($url) ?>"><?= e($post['title']) ?></a><button class="gallery-close" type="button">Close</button></div>
      <?php xuverse_gallery($post, $modalId . '-track'); ?>
    </dialog>
    <?php endif;
}

function xuverse_published_video($url, $title) {
    if (xuverse_youtube_embed($url) === '') return;
    xuverse_inline_video(['url' => $url, 'title' => $title], $url, '(max-width: 760px) 90vw, 720px');
}

function xuverse_inline_video($video, $fallbackUrl, $posterSizes)
{
    $poster = $video['poster'] ?? '';
    $embed = xuverse_youtube_embed($video['url']);
    ?>
    <div class="personal-video-player" data-inline-video data-video-source="<?= e($embed) ?>" data-video-title="<?= e($video['title']) ?>">
        <div class="personal-video-stage xu-image-reveal" data-video-stage>
            <?php if ($poster !== ''): ?>
            <img src="<?= e(xuverse_asset($poster)) ?>"
                <?= xuverse_image_attributes($poster) ?>
                <?= xuverse_responsive_image_attributes($poster, $posterSizes) ?>
                alt="" loading="lazy" decoding="async" data-video-poster>
            <?php endif; ?>
            <a class="personal-video-activation" href="<?= e($fallbackUrl) ?>" aria-label="<?= e('Watch ' . $video['title']) ?>" data-video-fallback></a>
            <button class="personal-video-activation" type="button" aria-label="<?= e('Play ' . $video['title']) ?>" data-video-play hidden></button>
        </div>
        <button type="button" class="personal-button personal-video-stop" data-video-stop hidden>Close video</button>
    </div>
    <?php
}
