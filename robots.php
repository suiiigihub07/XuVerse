<?php

require_once __DIR__ . '/includes/functions.php';

header('Content-Type: text/plain; charset=UTF-8');

$base = xuverse_base_url();

echo "User-agent: *\n";
if (!xuverse_is_production()) {
    echo "Disallow: /\n";
    exit;
}

echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /includes/\n";
echo "Disallow: /login.php\n";
echo "Disallow: /login\n";
echo "Disallow: /uploads/music/\n\n";
echo "Sitemap: {$base}/sitemap.xml\n";
