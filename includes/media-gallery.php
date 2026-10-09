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
      <div class="post-gallery-controls">
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
    <article class="media-card published-post compact-media-card reveal" id="<?= e($post['slug']) ?>">
    <?php if ($images): ?>
      <a href="<?= e($url) ?>" class="gallery-post-cover" data-open-gallery="<?= e($modalId) ?>" aria-label="Browse <?= count($images) ?> images: <?= e($post['title']) ?>">
        <img src="<?= e(xuverse_asset($images[0])) ?>" <?= xuverse_image_attributes($images[0]) ?> alt="<?= e(!empty($post['alt']) ? $post['alt'] : $post['title']) ?>" loading="lazy" decoding="async">
        <?php if (count($images) > 1): ?><span class="gallery-count" aria-hidden="true">1 / <?= count($images) ?></span><?php endif; ?>
      </a>
    <?php elseif (!empty($post['video_urls'])): ?>
      <?php xuverse_published_video($post['video_urls'][0], $post['title']); ?>
    <?php else: ?>
      <a href="<?= e($url) ?>" class="published-article-cover" aria-label="Read <?= e($post['title']) ?>">
        <svg viewBox="0 0 80 80" aria-hidden="true"><path d="M20 12h30l10 10v46H20zM50 12v12h10M29 34h22M29 43h22M29 52h14"/></svg>
        <span>Read article →</span>
      </a>
    <?php endif; ?>
      <div class="media-content"><h3><a href="<?= e($url) ?>"><?= e($post['title']) ?></a></h3></div>
    </article>
    <?php if ($images): ?>
    <dialog class="gallery-modal" id="<?= e($modalId) ?>" aria-label="<?= e($post['title']) ?> gallery">
      <div class="gallery-modal-toolbar"><a href="<?= e($url) ?>"><?= e($post['title']) ?> →</a><button class="gallery-close" type="button">Close</button></div>
      <?php xuverse_gallery($post, $modalId . '-track'); ?>
      <a class="text-link" href="<?= e(xuverse_url()) ?>">Home</a>
    </dialog>
    <?php endif;
}

function xuverse_published_video($url, $title) {
    $embed = xuverse_youtube_embed($url);
    if ($embed === '') return;
    ?>
    <div class="video-detail-frame media-player">
      <iframe src="<?= e($embed) ?>" title="<?= e($title) ?>" width="1600" height="900" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <?php
}
