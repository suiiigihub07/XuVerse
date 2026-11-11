<?php
require_once __DIR__ . '/includes/content.php';

$copy = xuverse_content('copy');
$site = xuverse_content('site');
$portrait = $site['portrait'];
$portraitSizes = '(max-width: 720px) 90vw, (max-width: 1140px) 42vw, 460px';
$intro = $copy['about'];

xuverse_public_start('About', $intro, 'about');
?>

<section class="personal-page personal-about" aria-labelledby="about-name">
    <div class="container personal-about-layout">
        <div class="personal-about-intro">
            <p class="personal-label">About</p>
            <h1 class="personal-title" id="about-name"><?= e($copy['name']) ?></h1>
            <p class="personal-lead"><?= e($intro) ?></p>
            <div class="personal-about-focus">
                <h2>Current focus</h2>
                <p><?= e($copy['current_focus']) ?></p>
            </div>
            <div class="personal-actions">
                <a class="personal-button personal-button-primary" href="<?= e(xuverse_url('resume')) ?>"><?= e($site['about_resume_action']) ?></a>
                <a class="personal-button" href="<?= e(xuverse_url('contact')) ?>">Get in touch</a>
            </div>
        </div>
        <figure class="personal-portrait">
            <picture>
                <?= xuverse_avif_source($portrait, $portraitSizes) ?>
                <img src="<?= e(xuverse_asset($portrait)) ?>"
                    <?= xuverse_image_attributes($portrait) ?>
                    <?= xuverse_responsive_image_attributes($portrait, $portraitSizes) ?>
                    alt="<?= e($site['portrait_alt']) ?>" loading="eager" fetchpriority="high" decoding="async">
            </picture>
            <figcaption><?= e($copy['location']) ?></figcaption>
        </figure>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
