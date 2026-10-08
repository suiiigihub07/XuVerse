<?php
if (!isset($resume)) {
    http_response_code(404);
    return;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= xuverse_resume_pdf_text($resume['name']) ?> - Resume</title>
<style>
@page { margin: 36pt 42pt 48pt; }
body { font-family: "DejaVu Sans", sans-serif; font-size: 9.1pt; line-height: 1.43; color: #242428; }
h1 { font-size: 22pt; line-height: 1.13; margin: 0 0 5pt; color: #17171a; }
h2 { font-size: 10.3pt; line-height: 1.25; color: #b31535; letter-spacing: .5pt; text-transform: uppercase; margin: 14pt 0 6pt; padding-bottom: 4pt; border-bottom: .7pt solid #e1d5d8; page-break-after: avoid; }
h3 { font-size: 10pt; line-height: 1.3; margin: 9pt 0 2pt; color: #17171a; page-break-after: avoid; }
p { margin: 0 0 5pt; orphans: 3; widows: 3; word-wrap: break-word; }
a { color: #a71330; text-decoration: none; word-wrap: break-word; }
.headline { font-size: 10.3pt; color: #45454c; margin-bottom: 7pt; }
.contact { font-size: 8.5pt; line-height: 1.5; color: #52525b; margin-bottom: 2pt; }
.profile { margin-top: 10pt; }
.meta { font-size: 8.5pt; color: #62626b; margin-bottom: 4pt; page-break-after: avoid; }
.entry-heading { page-break-inside: avoid; page-break-after: avoid; }
.skill { margin-bottom: 4pt; }
</style>
</head>
<body>
<h1><?= xuverse_resume_pdf_text($resume['name']) ?></h1>
<?php if ($resume['headline'] !== ''): ?><p class="headline"><?= xuverse_resume_pdf_text($resume['headline']) ?></p><?php endif; ?>
<p class="contact">
<?php if ($resume['location'] !== ''): ?><?= xuverse_resume_pdf_text($resume['location']) ?><br><?php endif; ?>
<?php if (filter_var($resume['email'], FILTER_VALIDATE_EMAIL)): ?><a href="mailto:<?= xuverse_resume_pdf_text($resume['email']) ?>"><?= xuverse_resume_pdf_text($resume['email']) ?></a><br><?php endif; ?>
<a href="<?= xuverse_resume_pdf_text($resume['website']) ?>"><?= xuverse_resume_pdf_text(preg_replace('#^https?://#', '', $resume['website'])) ?></a>
<?php foreach (['linkedin_url' => 'LinkedIn', 'github_url' => 'GitHub'] as $key => $label): ?>
<?php $link = $resume['settings'][$key] ?? ''; if (filter_var($link, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $link)): ?>
 &nbsp; | &nbsp; <a href="<?= xuverse_resume_pdf_text($link) ?>"><?= $label ?></a>
<?php endif; endforeach; ?>
</p>

<?php if ($resume['summary'] !== ''): ?>
<div class="profile"><?= xuverse_resume_pdf_paragraphs($resume['summary']) ?></div>
<?php endif; ?>

<?php if ($resume['experience']): ?>
<h2>Experience</h2>
<?php foreach ($resume['experience'] as $entry): ?>
<div class="entry-heading">
<h3><?= xuverse_resume_pdf_text($entry['job_title']) ?></h3>
<p class="meta"><?= xuverse_resume_pdf_text($entry['company_name']) ?><?php if (!empty($entry['start_date'])): ?> | <?= xuverse_resume_pdf_text(xuverse_resume_date($entry['start_date'], '')) ?> - <?= xuverse_resume_pdf_text(xuverse_resume_date($entry['end_date'] ?? '')) ?><?php endif; ?></p>
</div>
<?= xuverse_resume_pdf_paragraphs($entry['description']) ?>
<?php endforeach; endif; ?>

<?php if ($resume['education']): ?>
<h2>Education</h2>
<?php foreach ($resume['education'] as $entry): ?>
<div class="entry-heading">
<h3><?= xuverse_resume_pdf_text($entry['institution']) ?></h3>
<p class="meta"><?= xuverse_resume_pdf_text($entry['degree']) ?><?php if (!empty($entry['start_year'])): ?> | <?= xuverse_resume_pdf_text($entry['start_year']) ?> - <?= xuverse_resume_pdf_text($entry['end_year'] ?: 'Present') ?><?php endif; ?></p>
</div>
<?= xuverse_resume_pdf_paragraphs($entry['description']) ?>
<?php endforeach; endif; ?>

<?php if ($resume['skills']): ?>
<h2>Skills</h2>
<?php foreach ($resume['skills'] as $category => $skills): ?>
<p class="skill"><strong><?= xuverse_resume_pdf_text($category) ?>:</strong> <?= xuverse_resume_pdf_text(implode(', ', $skills)) ?></p>
<?php endforeach; endif; ?>

<?php if ($resume['projects']): ?>
<h2>Selected projects</h2>
<?php foreach ($resume['projects'] as $entry): ?>
<h3><a href="<?= xuverse_resume_pdf_text($resume['website'] . '/projects/' . $entry['slug']) ?>"><?= xuverse_resume_pdf_text($entry['title']) ?></a></h3>
<?= xuverse_resume_pdf_paragraphs($entry['description']) ?>
<?php endforeach; endif; ?>

<h2>Achievement</h2><p><?= xuverse_resume_pdf_text($resume['achievement']) ?></p><p class="meta">User-reported award; official title, placement and date are unverified.</p>
<?php if ($resume['focus'] !== ''): ?>
<h2>Current focus</h2>
<?= xuverse_resume_pdf_paragraphs($resume['focus']) ?>
<?php endif; ?>
</body>
</html>
