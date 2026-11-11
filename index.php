<?php
require_once 'includes/content.php';

$copy = xuverse_content('copy');
$site = xuverse_content('site');
$research = xuverse_find_public('articles', $site['featured_article']);
$project = xuverse_find_public('projects', $site['featured_project']);
$video = xuverse_find_public('media', $site['featured_video']);
$researchFeature = !empty($research['study_type']);
$essays = array_slice(array_values(array_filter(
    xuverse_published('articles'),
    fn ($article) => $article['slug'] !== $site['featured_article']
)), 0, 2);

xuverse_public_start($site['home_title'], $copy['hero_intro']);
?>

<section class="xu-hero" aria-labelledby="home-title">
    <?php include 'includes/background-signature.php'; ?>
    <div class="xu-container xu-hero-grid">
        <div class="xu-hero-copy">
            <p class="xu-kicker"><?= e($site['headline']) ?></p>
            <h1 id="home-title"><?= e($copy['name']) ?></h1>
            <p class="xu-hero-line"><?= e($copy['hero_headline']) ?></p>
            <div class="xu-hero-actions">
                <?php if ($research || $project || $video): ?><a class="xu-link" href="#selected-work"><?= e($copy['hero_work_action']) ?> <span aria-hidden="true">↓</span></a><?php endif; ?>
                <a class="xu-link xu-link-quiet" href="<?= e(xuverse_url('about')) ?>"><?= e($copy['hero_about_action']) ?> <span aria-hidden="true">↗</span></a>
            </div>
        </div>
        <figure class="xu-hero-portrait">
            <img src="<?= e(xuverse_asset($site['portrait'])) ?>" <?= xuverse_image_attributes($site['portrait']) ?> alt="<?= e($site['portrait_alt']) ?>" fetchpriority="high" decoding="async">
            <figcaption><span aria-hidden="true"></span><?= e($copy['location']) ?></figcaption>
        </figure>
    </div>
</section>

<div id="selected-work">
    <?php if ($research): ?>
    <section class="xu-feature xu-feature-paper" aria-labelledby="work-title">
        <div class="xu-container">
            <div class="xu-feature-head">
                <h2 id="work-title"><?= e($site['featured_heading']) ?></h2>
                <a class="xu-link" href="<?= e(xuverse_url('articles')) ?>">All articles <span aria-hidden="true">↗</span></a>
            </div>
            <div class="xu-writing-grid">
                <article class="xu-research-feature">
                    <p class="xu-kicker"><?= e($research['type'] ?? $research['category'] ?? 'Article') ?></p>
                    <h3><a href="<?= e(xuverse_url('articles/' . $research['slug'])) ?>"><?= e($research['title']) ?></a></h3>
                    <p class="xu-feature-subtitle"><?= e($research['subtitle']) ?></p>
                    <p class="xu-feature-summary"><?= e($research['summary']) ?></p>
                    <a class="xu-link" href="<?= e(xuverse_url('articles/' . $research['slug'])) ?>"><?= $researchFeature ? 'Read the research' : 'Read the article' ?> <span aria-hidden="true">↗</span></a>
                </article>
                <?php if ($essays): ?>
                <div class="xu-essay-list">
                    <p class="xu-kicker">Essays</p>
                    <ul>
                        <?php foreach ($essays as $essay): ?>
                        <li><a href="<?= e(xuverse_url('articles/' . $essay['slug'])) ?>"><span><strong><?= e($essay['title']) ?></strong><small><?= e($essay['subtitle']) ?></small></span><span aria-hidden="true">↗</span></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($project): ?>
    <section class="xu-feature xu-project-feature" aria-labelledby="home-project-title">
        <div class="xu-container xu-work-grid">
            <div class="xu-work-copy">
                <p class="xu-kicker">Software project</p>
                <h2 id="home-project-title"><a href="<?= e(xuverse_url('projects/' . $project['slug'])) ?>"><?= e($project['title']) ?></a></h2>
                <p class="xu-feature-summary"><?= e($project['summary']) ?></p>
                <div class="xu-work-actions">
                    <a class="xu-link" href="<?= e(xuverse_url('projects/' . $project['slug'])) ?>">View project <span aria-hidden="true">↗</span></a>
                    <a class="xu-link xu-link-quiet" href="<?= e(xuverse_url('projects')) ?>">All projects <span aria-hidden="true">↗</span></a>
                </div>
            </div>
            <?php if (!empty($project['image'])): ?>
            <figure class="xu-work-figure">
                <img src="<?= e(xuverse_asset($project['image'])) ?>" <?= xuverse_image_attributes($project['image']) ?> <?= xuverse_responsive_image_attributes($project['image'], '(max-width: 720px) 90vw, 52vw') ?> alt="<?= e($project['image_alt'] ?? $project['title']) ?>" loading="lazy" decoding="async">
                <?php if (!empty($project['image_caption'])): ?><figcaption><?= e($project['image_caption']) ?></figcaption><?php endif; ?>
            </figure>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($video): ?>
    <section class="xu-feature xu-video-feature" aria-labelledby="home-video-title">
        <div class="xu-container xu-work-grid">
            <?php if (!empty($video['poster'])): ?>
            <a class="xu-video-preview" href="<?= e(xuverse_url('videos/' . $video['slug'])) ?>" aria-label="Watch <?= e($video['title']) ?>">
                <img src="<?= e(xuverse_asset($video['poster'])) ?>" <?= xuverse_image_attributes($video['poster']) ?> alt="" loading="lazy" decoding="async">
                <span class="xu-play" aria-hidden="true">▶</span>
            </a>
            <?php endif; ?>
            <div class="xu-work-copy">
                <p class="xu-kicker">Video</p>
                <h2 id="home-video-title"><a href="<?= e(xuverse_url('videos/' . $video['slug'])) ?>"><?= e($video['title']) ?></a></h2>
                <p class="xu-feature-summary"><?= e($video['summary']) ?></p>
                <a class="xu-link" href="<?= e(xuverse_url('media')) ?>">Explore media <span aria-hidden="true">↗</span></a>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
