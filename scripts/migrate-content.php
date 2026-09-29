<?php
// Run once after updating files; safe to rerun without overwriting edited content.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../includes/db.php';

function add_content_column($conn, $table, $name, $definition) {
    $found = $conn->query("SHOW COLUMNS FROM `{$table}` LIKE '{$name}'")->num_rows > 0;
    if (!$found) {
        $conn->query("ALTER TABLE `{$table}` ADD COLUMN `{$name}` {$definition}");
    }
    return !$found;
}

$initializeProjects = add_content_column($conn, 'projects', 'is_published', 'TINYINT(1) NOT NULL DEFAULT 0');
add_content_column($conn, 'projects', 'display_order', 'INT NOT NULL DEFAULT 0');
add_content_column($conn, 'projects', 'project_status', "VARCHAR(100) NOT NULL DEFAULT ''");
foreach (['project_scope', 'project_focus', 'project_evidence', 'case_study'] as $field) {
    add_content_column($conn, 'projects', $field, 'TEXT NULL');
}

$projectFacts = [
    13 => ['scope' => 'CMS-backed creative archive', 'focus' => 'Media organization, responsive image delivery and reusable content management', 'status' => 'Working portfolio feature', 'evidence' => 'The public media archive and its CMS-managed photograph can be inspected inside XuVerse.'],
    12 => ['scope' => 'Database-backed academic project concept', 'focus' => 'Hotel records, reservation workflows and PHP/MySQL data modeling', 'status' => 'Concept / development-stage project', 'evidence' => 'No public repository or live demonstration is currently attached.'],
    11 => ['scope' => 'Academic web application prototype', 'focus' => 'Lost-and-found reporting, record browsing and relational data', 'status' => 'Academic prototype', 'evidence' => 'A project cover and written scope are available. The repository and live demo have not been published.'],
    6 => ['scope' => 'Full-stack personal CMS', 'focus' => 'PHP, MySQL, authentication, CRUD, uploads, responsive UI, print and technical SEO', 'status' => 'Working application', 'evidence' => 'This portfolio is the running application. Genuine interface screenshots and locally verified quality checks are documented below.']
];

$caseSections = [
    6 => [
        ['title' => 'The problem', 'body' => 'A static resume becomes difficult to maintain when projects, experience, articles and media change independently. XuVerse was built to keep those areas in one database-backed system while preserving a focused public portfolio.'],
        ['title' => 'Architecture and implementation', 'body' => 'The application uses PHP and MySQL with reusable includes, prepared database operations, protected administrative CRUD modules, media uploads, settings-driven content and clean public routes. The public resume, projects, articles and media pages all read from the same managed data.'],
        ['title' => 'Security decisions', 'body' => 'Administrative mutations use POST requests and CSRF validation. Login attempts are throttled, passwords are hashed, uploaded files are restricted, sensitive project files are denied by the web server, and baseline security headers are applied centrally.'],
        ['title' => 'Quality work', 'body' => 'The interface includes semantic landmarks, visible keyboard focus, responsive navigation, reduced-motion support, correctly sized images, AVIF/WebP delivery, canonical URLs, structured data, a dynamic sitemap, genuine 404 responses and a printable resume and automatically updated PDF download.'],
        ['title' => 'Verified result', 'body' => 'The current local build passes PHP syntax checks, returns correct success and error status codes, generates a readable PDF from the current resume records and supports longer content across multiple pages without overlap.'],
        ['title' => 'Current limitation', 'body' => 'The source repository is not public yet, so this case study does not claim that it is. Repository publication with installation documentation and commit history is the next evidence milestone.']
    ],
    11 => [
        ['title' => 'Project goal', 'body' => 'The academic concept addresses a familiar campus problem: reporting lost or found items and making those records easier to browse in one place.'],
        ['title' => 'Technical scope', 'body' => 'The documented scope covers PHP/MySQL data handling, relational records, reporting and browsing workflows. It is presented as an academic prototype, not as a deployed production service.'],
        ['title' => 'Evidence boundary', 'body' => 'A public repository, live demo and measured usage results are not currently available. This limitation is visible because portfolio evidence should be inspectable, not implied.'],
        ['title' => 'Next milestone', 'body' => 'To graduate this into a complete case study, the project needs repository publication, database documentation, working screenshots, validation states, mobile views and a deployable demonstration.']
    ],
    13 => [
        ['title' => 'Project goal', 'body' => 'The archive gives campus and community photography a structured home instead of mixing finished visual work into unrelated project pages.'],
        ['title' => 'CMS workflow', 'body' => 'Media records are managed through XuVerse and rendered into a responsive public archive with individual detail routes, descriptive text and optimized image formats.'],
        ['title' => 'Presentation decisions', 'body' => 'The public view keeps the dark and red visual identity while allowing photography to remain the dominant content. Duplicate media records are removed from the public query so repeated uploads do not create a misleading body of work.'],
        ['title' => 'Evidence and limitation', 'body' => 'The working media archive is available on this site. The public collection is intentionally small today; adding six to twelve distinct, carefully captioned images would make this a stronger creative case study.']
    ]
];

if ($initializeProjects) {
    $stmt = $conn->prepare("UPDATE projects SET is_published=1, display_order=?, project_status=?, project_scope=?, project_focus=?, project_evidence=?, case_study=? WHERE id=?");
    foreach ([6, 11, 13] as $order => $id) {
        $facts = $projectFacts[$id];
        $narrative = implode("\n\n", array_map(fn($section) => '## ' . $section['title'] . "\n" . $section['body'], $caseSections[$id]));
        $stmt->bind_param('isssssi', $order, $facts['status'], $facts['scope'], $facts['focus'], $facts['evidence'], $narrative, $id);
        $stmt->execute();
    }
}

if (add_content_column($conn, 'settings', 'resume_headline', "VARCHAR(255) NOT NULL DEFAULT ''")) {
    $conn->query("UPDATE settings SET resume_headline='Software Engineering Student - Media Creator - Community Builder'");
}
if (add_content_column($conn, 'settings', 'resume_summary', 'TEXT NULL')) {
    $conn->query("UPDATE settings SET resume_summary='Software engineering student at Dong-Eui University in Busan combining database-backed web development with photography, video, campus media, leadership and community work.'");
}

if (add_content_column($conn, 'videos', 'is_published', 'TINYINT(1) NOT NULL DEFAULT 0')) {
    $conn->query("UPDATE videos SET is_published=1 WHERE video_url LIKE '%youtube.com/watch%' AND LOWER(title) NOT LIKE '%search%'");
}

echo "Content fields ready. Existing drafts stay hidden; edited values are preserved.\n";
