<?php
require_once __DIR__ . '/includes/content.php';

$legacyId = (int)($_GET['id'] ?? 0);
$mediaAliases = [
    7 => 'videos/global-college-2026',
    8 => 'videos/promotional-competition',
    9 => 'videos/daam-introduction',
    10 => 'media#anchor-eco-delta',
];
if (isset($mediaAliases[$legacyId])) {
    header('Location: ' . xuverse_url($mediaAliases[$legacyId]), true, 301);
    exit;
}

$project = xuverse_find_public('projects', $_GET['slug'] ?? '', $legacyId);
if (!$project) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$path = 'projects/' . $project['slug'];
if (empty($_GET['slug'])) {
    header('Location: ' . xuverse_url($path), true, 301);
    exit;
}

$image = $project['image'] ?? '';
$hasFacts = !empty($project['role']) || !empty($project['technology']);
xuverse_public_start($project['title'], $project['summary'], $path);
?>
<article class="xw-page xw-container xw-project-page">
    <header class="xw-reading-head">
        <p class="xw-kicker"><?= e($project['status']) ?></p>
        <h1><?= e($project['title']) ?></h1>
        <p class="xw-reading-lead"><?= e($project['summary']) ?></p>
    </header>

    <?php if ($image): ?>
    <figure class="xw-project-evidence">
        <img src="<?= e(xuverse_asset($image)) ?>" <?= xuverse_image_attributes($image) ?> <?= xuverse_responsive_image_attributes($image, '(max-width: 1280px) 94vw, 1100px') ?> alt="<?= e($project['image_alt'] ?? '') ?>" fetchpriority="high" decoding="async">
        <?php if (!empty($project['image_caption'])): ?>
        <figcaption><?= e($project['image_caption']) ?></figcaption>
        <?php endif; ?>
    </figure>
    <?php endif; ?>

    <div class="xw-project-main<?= $hasFacts ? ' xw-project-with-facts' : '' ?>">
        <div class="xw-project-story">
            <section>
                <h2>Overview</h2>
                <p><?= e($project['description']) ?></p>
            </section>
            <section>
                <h2>What I learned</h2>
                <p><?= e($project['learning']) ?></p>
            </section>
        </div>

        <?php if ($hasFacts): ?>
        <dl class="xw-project-facts">
            <?php if (!empty($project['role'])): ?>
            <div><dt>Role</dt><dd><?= e($project['role']) ?></dd></div>
            <?php endif; ?>
            <?php if (!empty($project['technology'])): ?>
            <div><dt>Technology</dt><dd><?= e(implode(' · ', $project['technology'])) ?></dd></div>
            <?php endif; ?>
        </dl>
        <?php endif; ?>
    </div>

    <footer class="xw-project-footer">
        <a class="xw-return" href="<?= e(xuverse_url('projects')) ?>"><span aria-hidden="true">←</span> All projects</a>
        <?php if (!empty($project['repository']) || !empty($project['live'])): ?>
        <div class="xw-project-actions">
            <?php foreach (['repository' => 'GitHub', 'live' => 'Live site'] as $key => $label): ?>
            <?php if (!empty($project[$key])): ?>
            <a class="xw-control" href="<?= e($project[$key]) ?>" rel="noopener noreferrer"><?= e($label) ?><span aria-hidden="true">↗</span></a>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </footer>
</article>
<?php include __DIR__ . '/includes/footer.php'; ?>
