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
@page { margin: 46pt 42pt 48pt; }
body { font-family: "DejaVu Sans", sans-serif; font-size: 9.7pt; line-height: 1.45; color: #161619; }
h1 { font: normal 31pt/1.13 "DejaVu Serif", serif; margin: 4pt 0 6pt; letter-spacing: -.7pt; }
h2, .label { margin: 0; color: #982047; font-size: 8.1pt; line-height: 1.45; font-weight: bold; letter-spacing: .65pt; text-transform: uppercase; }
h3 { font-size: 10.2pt; line-height: 1.35; margin: 0 0 2pt; }
p { margin: 0 0 3pt; orphans: 3; widows: 3; word-wrap: break-word; }
a { color: #982047; text-decoration: none; word-wrap: break-word; }
.masthead { padding-bottom: 17pt; border-bottom: 2pt solid #a60000; }
.headline { font-size: 11pt; margin-bottom: 6pt; }
.contact { color: #55555b; font-size: 8.8pt; line-height: 1.55; margin: 0; }
.meta { color: #55555b; font-size: 8.8pt; }
.section { width: 100%; table-layout: auto; border-collapse: collapse; border-bottom: .65pt solid #d6d4d6; }
.section td { padding: 14pt 0 13pt; vertical-align: top; }
.section .rail { width: 138pt; padding-right: 16pt; }
.entry + .entry { margin-top: 8pt; padding-top: 8pt; border-top: .5pt solid #e0dee0; }
.entry > :last-child, .section td > :last-child { margin-bottom: 0; }
</style>
</head>
<body>
<div class="masthead">
<p class="label">Résumé</p>
<h1><?= xuverse_resume_pdf_text($resume['name']) ?></h1>
<?php if ($resume['headline'] !== ''): ?><p class="headline"><?= xuverse_resume_pdf_text($resume['headline']) ?></p><?php endif; ?>
<p class="contact">
<?php if ($resume['location'] !== ''): ?><?= xuverse_resume_pdf_text($resume['location']) ?> &nbsp; | &nbsp; <?php endif; ?>
<?php if (filter_var($resume['email'], FILTER_VALIDATE_EMAIL)): ?><a href="mailto:<?= xuverse_resume_pdf_text($resume['email']) ?>"><?= xuverse_resume_pdf_text($resume['email']) ?></a><br><?php endif; ?>
<a href="<?= xuverse_resume_pdf_text($resume['website']) ?>"><?= xuverse_resume_pdf_text(preg_replace('#^https?://#', '', $resume['website'])) ?></a>
<?php foreach (['linkedin_url' => 'LinkedIn', 'github_url' => 'GitHub'] as $key => $label): ?>
<?php $link = $resume['settings'][$key] ?? ''; if (filter_var($link, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $link)): ?>
 &nbsp; | &nbsp; <a href="<?= xuverse_resume_pdf_text($link) ?>"><?= $label ?></a>
<?php endif; endforeach; ?>
</p>
</div>

<?php if ($resume['education']): ?>
<table class="section" role="presentation"><tr>
<td class="rail"><h2>Education</h2></td>
<td>
<?php foreach ($resume['education'] as $entry): ?>
<div class="entry">
<h3><?= xuverse_resume_pdf_text($entry['institution']) ?></h3>
<p><?= xuverse_resume_pdf_text($entry['degree']) ?></p>
<?php if (!empty($entry['start_year'])): ?>
<p class="meta"><?= xuverse_resume_pdf_text($entry['start_year']) ?> - <?= xuverse_resume_pdf_text($entry['end_year'] ?: 'Present') ?></p>
<?php endif; ?>
<p class="meta"><?= xuverse_resume_pdf_text($entry['location'] . ' | ' . $entry['semester']) ?></p>
</div>
<?php endforeach; ?>
</td></tr></table>
<?php endif; ?>

<?php foreach ($resume['role_groups'] as $group): if (!$group['entries']) continue; ?>
<table class="section" role="presentation"><tr>
<td class="rail"><h2><?= xuverse_resume_pdf_text($group['label']) ?></h2></td>
<td>
<?php foreach ($group['entries'] as $entry): ?>
<div class="entry">
<h3><?= xuverse_resume_pdf_text($entry['job_title']) ?></h3>
<p class="meta"><?= xuverse_resume_pdf_text($entry['company_name'] . ' | ' . $entry['dates']) ?></p>
<?= xuverse_resume_pdf_paragraphs($entry['description']) ?>
</div>
<?php endforeach; ?>
</td></tr></table>
<?php endforeach; ?>

<table class="section" role="presentation"><tr>
<td class="rail"><h2>Skills &amp; Areas</h2></td>
<td>
<?php foreach ($resume['areas'] as $area => $skills): ?>
<p><strong><?= xuverse_resume_pdf_text($area) ?></strong> · <?= xuverse_resume_pdf_text(implode(', ', $skills)) ?></p>
<?php endforeach; ?>
</td></tr></table>

<table class="section" role="presentation"><tr>
<td class="rail"><h2>Tools</h2></td>
<td><p><?= xuverse_resume_pdf_text(implode(', ', $resume['tools'])) ?></p></td>
</tr></table>
</body>
</html>
