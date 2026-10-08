<?php
require_once __DIR__ . '/functions.php';

function xuverse_content($name) {
    static $cache = [];
    if (!preg_match('/^[a-z-]+$/', $name)) { throw new InvalidArgumentException('Invalid content name'); }
    return $cache[$name] ??= json_decode(file_get_contents(dirname(__DIR__) . '/content/' . $name . '.json'), true, 512, JSON_THROW_ON_ERROR);
}

// All raw HTML is escaped. Only this small Markdown vocabulary produces markup.
function xuverse_inline($text) {
    $text = e($text);
    $text = preg_replace_callback('/\[([^\]]+)\]\(([^\s)]+)\)/', function ($m) {
        $url = html_entity_decode($m[2], ENT_QUOTES, 'UTF-8');
        if (!preg_match('#^https?://#i', $url)) { return $m[1]; }
        return '<a href="' . e($url) . '" rel="noopener noreferrer">' . $m[1] . '</a>';
    }, $text);
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    return preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/', '<em>$1</em>', $text);
}

function xuverse_markdown($text) {
    $out = ''; $paragraph = []; $list = false; $table = false;
    $flush = function () use (&$out, &$paragraph) { if ($paragraph) { $out .= '<p>' . xuverse_inline(implode(' ', $paragraph)) . '</p>'; $paragraph = []; } };
    $close = function () use (&$out, &$list, &$table) { if ($list) { $out .= '</ul>'; $list = false; } if ($table) { $out .= '</tbody></table></div>'; $table = false; } };
    foreach (preg_split('/\R/u', trim($text)) as $line) {
        $line = trim($line);
        if ($line === '') { $flush(); $close(); continue; }
        if (preg_match('/^\|.*\|$/', $line)) {
            $flush();
            if (preg_match('/^\|[\s:|\-]+\|$/', $line)) { continue; }
            $cells = explode('|', trim($line, '|'));
            $tag = $table ? 'td' : 'th';
            if (!$table) { $out .= '<div class="table-scroll"><table><tbody>'; $table = true; }
            $out .= '<tr>'; foreach ($cells as $cell) { $out .= '<' . $tag . '>' . xuverse_inline(trim($cell)) . '</' . $tag . '>'; } $out .= '</tr>'; continue;
        }
        if (preg_match('/^(#{1,6})\s+(.+)$/u', $line, $m)) {
            $flush(); $close(); $level = max(2, strlen($m[1]));
            $out .= '<h' . $level . '>' . xuverse_inline($m[2]) . '</h' . $level . '>'; continue;
        }
        if (preg_match('/^[-*]\s+(.+)$/u', $line, $m)) { $flush(); if (!$list) { $out .= '<ul>'; $list = true; } $out .= '<li>' . xuverse_inline($m[1]) . '</li>'; continue; }
        $close(); $paragraph[] = $line;
    }
    $flush(); $close(); return $out;
}

function xuverse_body($article) {
    $slug = $article['slug'];
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) { throw new RuntimeException('Invalid slug'); }
    $body = file_get_contents(dirname(__DIR__) . '/content/writing/' . $slug . '.md');
    // Title/subtitle/date already have semantic positions in the page header.
    $parts = preg_split('/\R\s*\R/u', trim($body));
    array_splice($parts, 0, 3);
    return implode("\n\n", $parts);
}

function xuverse_public_settings($settings = []) {
    $copy = xuverse_content('copy'); $links = xuverse_content('links');
    $settings = array_merge($settings, [
        'site_title' => 'XuVerse', 'hero_title' => $copy['name'], 'hero_description' => $copy['hero_intro'],
        'hero_subtitle' => 'Intelligence Computing undergraduate', 'meta_description' => $copy['hero_intro'],
        'resume_headline' => 'Intelligence Computing undergraduate', 'resume_summary' => $copy['resume_summary'],
        'location_text' => $copy['location'], 'current_focus' => $copy['current_focus'],
        'contact_email' => substr($links['email'], 7), 'og_image' => 'assets/images/public/portrait.webp',
        'footer_tagline' => $copy['footer'], 'footer_text' => '', 'nav_cta_label' => 'Connect'
    ]);
    foreach (['github','linkedin','instagram','youtube','tiktok','facebook'] as $key) { $settings[$key . '_url'] = $links[$key]; }
    return $settings;
}

function xuverse_public_start($title, $description, $path = '') {
    global $settings, $pageTitle, $pageDescription, $canonicalUrl, $conn, $schemaData, $pageType, $pageImage;
    require_once __DIR__ . '/db.php';
    $settings = xuverse_public_settings();
    $pageTitle = $title . ' | B K Suraj'; $pageDescription = $description;
    $canonicalUrl = xuverse_base_url() . '/' . $path;
    include __DIR__ . '/header.php'; include __DIR__ . '/navbar.php';
}

function xuverse_find_public($collection, $slug, $id = 0) {
    foreach (xuverse_content($collection) as $item) {
        if (($slug !== '' && $item['slug'] === $slug) || ($id && in_array($id, $item['legacy_ids'] ?? [], true))) { return $item; }
    }
    return null;
}

function xuverse_public_card($item, $route, $image = '') {
    $url = xuverse_url($route . '/' . $item['slug']);
    echo '<article class="public-card">';
    if ($image) { echo '<a href="' . e($url) . '"><img src="' . e(xuverse_url($image)) . '" alt="' . e($item['title']) . '" loading="lazy" decoding="async"></a>'; }
    echo '<p class="eyebrow">' . e($item['category'] ?? $item['status'] ?? '') . '</p><h2><a href="' . e($url) . '">' . e($item['title']) . '</a></h2><p>' . e($item['summary']) . '</p><a class="text-link" href="' . e($url) . '">Read more →</a></article>';
}
