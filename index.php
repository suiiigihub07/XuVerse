<?php
require_once 'includes/content.php';

$copy = xuverse_content('copy');
$site = xuverse_content('site');
$research = xuverse_find_public('articles', $site['featured_article']);
$video = xuverse_find_public('media', $site['featured_video']);
$work = array_values(array_filter(xuverse_published('projects'), fn ($item) => !empty($item['image'])));
usort($work, fn ($a, $b) => ($b['slug'] === $site['featured_project']) <=> ($a['slug'] === $site['featured_project']));
$work = array_slice($work, 0, 2);
$essays = array_slice(array_values(array_filter(
    xuverse_published('articles'),
    fn ($article) => $article['slug'] !== $site['featured_article']
)), 0, 2);
$portrait = $site['portrait'];
$portraitSizes = '(max-width: 720px) 240px, 380px';

xuverse_public_start($site['home_title'], $copy['hero_intro']);
?>

<section class="xu-hero xu-tone-dark" aria-labelledby="home-title">
    <div class="xu-container xu-hero-grid">
        <div class="xu-hero-copy reveal">
            <p class="xu-kicker"><span><?= e($site['headline']) ?></span><span><?= e($copy['location']) ?></span></p>
            <h1 id="home-title"><?= e($copy['name']) ?></h1>
            <p class="xu-hero-line"><?= e($copy['hero_headline']) ?></p>
            <div class="xu-hero-actions">
                <a class="xu-primary" href="<?= e($work ? '#selected-work' : xuverse_url('projects')) ?>"><?= e($copy['hero_work_action']) ?></a>
                <a class="xu-link" href="<?= e(xuverse_url('about')) ?>"><?= e($copy['hero_about_action']) ?></a>
            </div>
        </div>
        <figure class="xu-hero-portrait reveal xu-image-reveal">
            <picture>
                <?= xuverse_avif_source($portrait, $portraitSizes) ?>
                <img src="<?= e(xuverse_asset($portrait)) ?>" <?= xuverse_image_attributes($portrait) ?> <?= xuverse_responsive_image_attributes($portrait, $portraitSizes) ?> alt="<?= e($site['portrait_alt']) ?>" fetchpriority="high" decoding="async">
            </picture>
        </figure>
    </div>
</section>

<?php if ($work): ?>
<section id="selected-work" class="xu-home-section xu-home-work xu-tone-light" aria-labelledby="work-title">
    <div class="xu-container">
        <div class="xu-feature-head reveal">
            <h2 id="work-title"><?= e($site['featured_heading']) ?></h2>
            <a class="xu-link" href="<?= e(xuverse_url('projects')) ?>">All projects</a>
        </div>
        <div class="xu-home-project-grid">
            <?php foreach ($work as $project):
                $image = $project['image'];
                $imageSizes = '(max-width: 559px) 90vw, 44vw';
            ?>
            <article class="xu-home-project reveal">
                <a class="xu-home-project-link" href="<?= e(xuverse_url('projects/' . $project['slug'])) ?>">
                    <picture class="xu-home-project-image reveal xu-image-reveal">
                        <?= xuverse_avif_source($image, $imageSizes) ?>
                        <img src="<?= e(xuverse_asset($image)) ?>" <?= xuverse_image_attributes($image) ?> <?= xuverse_responsive_image_attributes($image, $imageSizes) ?> alt="<?= e($project['image_alt'] ?? '') ?>" loading="lazy" decoding="async">
                    </picture>
                    <div class="xu-home-project-title">
                        <p class="xu-kicker"><?= $project['slug'] === $site['featured_project'] ? 'Portfolio' : 'Software project' ?></p>
                        <h3><?= e($project['title']) ?></h3>
                    </div>
                    <p class="xu-home-project-summary"><?= e(($project['home_summary'] ?? '') ?: $project['summary']) ?></p>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($research || $essays): ?>
<section class="xu-home-section xu-home-writing xu-tone-light" aria-labelledby="home-writing-title">
    <div class="xu-container">
        <div class="xu-feature-head reveal">
            <h2 id="home-writing-title">Writing &amp; Research</h2>
            <a class="xu-link" href="<?= e(xuverse_url('articles')) ?>">All articles</a>
        </div>
        <div class="xu-writing-grid">
            <?php if ($research): ?>
            <article class="xu-research-feature reveal">
                <a href="<?= e(xuverse_url('articles/' . $research['slug'])) ?>">
                    <p class="xu-kicker"><?= e($research['type'] ?? $research['category'] ?? 'Article') ?></p>
                    <h3><?= e($research['title']) ?></h3>
                    <?php if (!empty($research['subtitle'])): ?><p><?= e($research['subtitle']) ?></p><?php endif; ?>
                </a>
            </article>
            <?php endif; ?>
            <?php if ($essays): ?>
            <ul class="xu-essay-list reveal" aria-label="Essays">
                <?php foreach ($essays as $essay): ?>
                <li><a href="<?= e(xuverse_url('articles/' . $essay['slug'])) ?>"><span class="xu-kicker">Essay</span><strong><?= e($essay['title']) ?></strong><?php if (!empty($essay['subtitle'])): ?><small><?= e($essay['subtitle']) ?></small><?php endif; ?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($video): ?>
<section class="xu-home-section xu-home-media xu-tone-dark" aria-labelledby="home-media-title">
    <div class="xu-container">
        <div class="xu-feature-head reveal">
            <h2 id="home-media-title">Featured Media</h2>
            <a class="xu-link" href="<?= e(xuverse_url('media')) ?>">Explore media</a>
        </div>
        <div class="xu-home-media-grid">
            <?php if (!empty($video['poster'])): ?>
            <div class="reveal xu-image-reveal">
                <a class="xu-video-preview" href="<?= e(xuverse_url('videos/' . $video['slug'])) ?>" aria-label="Watch <?= e($video['title']) ?>">
                    <img src="<?= e(xuverse_asset($video['poster'])) ?>" <?= xuverse_image_attributes($video['poster']) ?> alt="" loading="lazy" decoding="async">
                    <span class="xu-play" aria-hidden="true"><svg viewBox="0 0 20 20" width="20" height="20" focusable="false"><path fill="currentColor" d="M7 4.5v11l8-5.5z"/></svg></span>
                </a>
            </div>
            <?php endif; ?>
            <div class="xu-home-video-copy reveal">
                <p class="xu-kicker">Video</p>
                <h3><a href="<?= e(xuverse_url('videos/' . $video['slug'])) ?>"><?= e($video['title']) ?></a></h3>
                <?php if (!empty($video['summary'])): ?><p><?= e($video['summary']) ?></p><?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="xu-home-section xu-home-about xu-tone-light" aria-labelledby="home-about-title">
    <div class="xu-container xu-home-about-grid">
        <h2 class="reveal" id="home-about-title">About</h2>
        <div class="reveal">
            <p><?= e($copy['about']) ?></p>
            <a class="xu-link" href="<?= e(xuverse_url('about')) ?>"><?= e($copy['hero_about_action']) ?></a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
