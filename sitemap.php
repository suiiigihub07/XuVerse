<?php

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=UTF-8');

$pageModified = date('Y-m-d', max(
    filemtime(__DIR__ . '/index.php'),
    filemtime(__DIR__ . '/assets/css/style.css'),
    filemtime(__DIR__ . '/assets/js/main.js')
));

$urls = [
    ['loc' => xuverse_base_url() . '/', 'lastmod' => $pageModified],
    ['loc' => xuverse_base_url() . '/projects', 'lastmod' => $pageModified],
    ['loc' => xuverse_base_url() . '/resume', 'lastmod' => $pageModified],
    ['loc' => xuverse_base_url() . '/articles', 'lastmod' => $pageModified],
    ['loc' => xuverse_base_url() . '/media', 'lastmod' => $pageModified],
    ['loc' => xuverse_base_url() . '/contact', 'lastmod' => $pageModified],
    ['loc' => xuverse_base_url() . '/now', 'lastmod' => $pageModified]
];

$projects = $conn->query("SELECT id, created_at FROM projects WHERE is_published=1 ORDER BY display_order, id DESC");
while ($projects && $project = $projects->fetch_assoc()) {
    $urls[] = [
        'loc' => xuverse_base_url() . '/projects/' . (int)$project['id'],
        'lastmod' => date('Y-m-d', strtotime($project['created_at'] ?? 'now'))
    ];
}

$articles = $conn->query("SELECT id, created_at FROM articles WHERE published=1 AND LOWER(TRIM(title)) <> 'hi' ORDER BY id");
while ($articles && $article = $articles->fetch_assoc()) {
    $urls[] = [
        'loc' => xuverse_base_url() . '/articles/' . (int)$article['id'],
        'lastmod' => date('Y-m-d', strtotime($article['created_at'] ?? 'now'))
    ];
}

$photos = $conn->query(
    "SELECT MIN(id) AS id, MAX(uploaded_at) AS updated_at
     FROM photos
     WHERE image_path IS NOT NULL AND TRIM(image_path) <> ''
     GROUP BY image_path"
);
while ($photos && $photo = $photos->fetch_assoc()) {
    $urls[] = [
        'loc' => xuverse_base_url() . '/photos/' . (int)$photo['id'],
        'lastmod' => date('Y-m-d', strtotime($photo['updated_at'] ?? 'now'))
    ];
}

$videos = $conn->query(
    "SELECT id, uploaded_at
     FROM videos
     WHERE is_published=1
     ORDER BY id"
);
while ($videos && $video = $videos->fetch_assoc()) {
    $urls[] = [
        'loc' => xuverse_base_url() . '/videos/' . (int)$video['id'],
        'lastmod' => date('Y-m-d', strtotime($video['uploaded_at'] ?? 'now'))
    ];
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach ($urls as $url) {
    echo "  <url><loc>" . htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8') . "</loc><lastmod>{$url['lastmod']}</lastmod></url>\n";
}
echo "</urlset>\n";
