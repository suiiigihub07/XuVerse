<?php
// Update only obsolete implementation descriptions after the dynamic PDF change.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../includes/db.php';
$conn->query("UPDATE projects SET description=REPLACE(description, 'one-page resume export', 'automatically updated PDF resume export') WHERE id=6");
$conn->query("UPDATE projects SET description=REPLACE(description, 'a automatically updated', 'an automatically updated') WHERE id=6");
$conn->query("UPDATE projects SET case_study=REPLACE(REPLACE(case_study, 'a dedicated one-page print resume', 'a printable resume and automatically updated PDF download'), 'produces a one-page resume PDF without overlap, and has achieved perfect local Lighthouse scores for accessibility, best practices and SEO. Performance remains an optimization target rather than a fabricated perfect score.', 'generates a readable PDF from the current resume records and supports longer content across multiple pages without overlap.') WHERE id=6");
$conn->query("UPDATE articles SET content=REPLACE(content, 'The final print stylesheet creates a one-page light resume with controlled spacing, explicit page margins and hidden navigation.', 'The print stylesheet provides a light resume with controlled spacing, explicit margins and hidden navigation. The PDF download is generated directly from the current CMS records and flows across pages as the content grows.') WHERE id=2");
echo "Obsolete PDF implementation descriptions updated.\n";
