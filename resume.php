<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/resume-data.php';

$resume = xuverse_resume_data($conn);
$settings = $resume['settings'];
$pageTitle = 'Résumé | ' . $resume['name'];
$pageDescription = xuverse_excerpt($resume['summary'], 155);
$canonicalUrl = xuverse_base_url() . '/resume';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="personal-page personal-cv" aria-labelledby="cv-name">
    <div class="container personal-cv-container">
        <header class="personal-cv-header">
            <div>
                <p class="personal-label">Résumé</p>
                <h1 class="personal-title" id="cv-name"><?= e($resume['name']) ?></h1>
                <p class="personal-cv-headline"><?= e($resume['headline']) ?></p>
                <ul class="personal-cv-links">
                    <li><?= e($resume['location']) ?></li>
                    <?php if ($resume['email'] !== ''): ?>
                    <li><a href="mailto:<?= e($resume['email']) ?>"><?= e($resume['email']) ?></a></li>
                    <?php endif; ?>
                    <?php foreach (['github_url' => 'GitHub', 'linkedin_url' => 'LinkedIn'] as $key => $label): ?>
                        <?php if (!empty($settings[$key])): ?>
                        <li><a href="<?= e($settings[$key]) ?>" target="_blank" rel="noopener noreferrer"><?= e($label) ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="personal-actions personal-cv-controls">
                <a class="personal-button personal-button-primary" href="<?= e(xuverse_url('resume/download')) ?>" download>Download PDF</a>
                <button type="button" class="personal-button print-only-control" data-print-resume>Print</button>
            </div>
        </header>

        <div class="personal-cv-layout">
            <div>
                <section class="personal-cv-section" aria-labelledby="cv-education">
                    <h2 id="cv-education">Education</h2>
                    <?php foreach ($resume['education'] as $entry): ?>
                    <article class="personal-cv-entry">
                        <div class="personal-cv-entry-title">
                            <h3><?= e($entry['institution']) ?></h3>
                            <p><?= e($entry['start_year']) ?> – <?= e($entry['end_year'] ?: 'Present') ?></p>
                        </div>
                        <p><?= e($entry['degree']) ?></p>
                        <p class="personal-cv-detail"><?= e($entry['location']) ?> · <?= e($entry['semester']) ?></p>
                        <p class="personal-cv-detail"><?= e($entry['description']) ?></p>
                    </article>
                    <?php endforeach; ?>
                </section>

                <?php foreach ($resume['role_groups'] as $heading => $roles): ?>
                    <?php if (!$roles) continue; ?>
                    <section class="personal-cv-section" aria-labelledby="cv-<?= e(strtolower($heading)) ?>">
                        <h2 id="cv-<?= e(strtolower($heading)) ?>"><?= e($heading) ?></h2>
                        <?php foreach ($roles as $role): ?>
                        <article class="personal-cv-entry">
                            <div class="personal-cv-entry-title">
                                <h3><?= e($role['job_title']) ?></h3>
                                <p><?= e($role['dates']) ?></p>
                            </div>
                            <p class="personal-cv-detail"><?= e($role['company_name']) ?></p>
                            <?php if ($role['description'] !== ''): ?>
                            <p><?= e($role['description']) ?></p>
                            <?php endif; ?>
                        </article>
                        <?php endforeach; ?>
                    </section>
                <?php endforeach; ?>

                <?php if ($resume['achievement'] !== ''): ?>
                <section class="personal-cv-section" aria-labelledby="cv-recognition">
                    <h2 id="cv-recognition">Selected Recognition</h2>
                    <p><?= e($resume['achievement']) ?></p>
                    <p class="personal-cv-detail"><?= e($resume['achievement_note']) ?></p>
                </section>
                <?php endif; ?>
            </div>

            <div>
                <section class="personal-cv-section" aria-labelledby="cv-areas">
                    <h2 id="cv-areas">Areas of Work</h2>
                    <?php foreach ($resume['areas'] as $area => $skills): ?>
                    <div class="personal-cv-entry">
                        <h3><?= e($area) ?></h3>
                        <p class="personal-cv-detail"><?= e(implode(' · ', $skills)) ?></p>
                    </div>
                    <?php endforeach; ?>
                </section>

                <section class="personal-cv-section" aria-labelledby="cv-tools">
                    <h2 id="cv-tools">Tools</h2>
                    <p class="personal-cv-detail"><?= e(implode(' · ', $resume['tools'])) ?></p>
                </section>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
