<?php
require_once __DIR__ . '/content.php';

function xuverse_gallery_images($item) {
    return array_slice($item['cards'] ?? $item['images'] ?? [$item['image']], 0, 10);
}

function xuverse_gallery($item, $id) {
    $images = xuverse_gallery_images($item);
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
