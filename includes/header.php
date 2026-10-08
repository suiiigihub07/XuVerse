<?php

require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE && isset($_COOKIE[session_name()])) {
    xuverse_session_start();
}

if (!headers_sent()) {
    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https://i.ytimg.com; media-src 'self'; style-src 'self' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self'; frame-src https://www.youtube.com https://www.youtube-nocookie.com; connect-src 'self'");

    if (xuverse_is_production()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

if (!isset($settings) || !is_array($settings)) {
    require_once __DIR__ . '/db.php';

    $settings = $conn->query(
        "SELECT * FROM settings
         LIMIT 1"
    )->fetch_assoc();
}

require_once __DIR__ . '/content.php';
if (strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') === false) { $settings = xuverse_public_settings($settings); }
$siteTitle = xuverse_setting($settings, 'site_title', 'XuVerse');
$documentTitle = $pageTitle ?? $siteTitle;
$metaDescription = xuverse_setting(
    $settings,
    'meta_description',
    xuverse_setting($settings, 'hero_description', 'Personal website and CMS.')
);
$pageDescription = $pageDescription ?? $metaDescription;
$canonicalUrl = $canonicalUrl ?? xuverse_current_url();
$ownerAvatar = xuverse_content('site')['portrait'];
$pageImage = $pageImage ?? xuverse_setting($settings, 'og_image', $ownerAvatar);
$absoluteImage = xuverse_absolute_url($pageImage);
$pageType = $pageType ?? 'website';
$isPrivatePage = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') !== false;
$robots = $robots ?? (xuverse_is_production() && !$isPrivatePage ? 'index, follow, max-image-preview:large' : xuverse_noindex());
$adminName = $adminName ?? xuverse_setting($settings, 'hero_title', 'B K Suraj');
$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => $adminName,
    'url' => xuverse_base_url() . '/',
    'image' => $absoluteImage,
    'email' => xuverse_setting($settings, 'contact_email', ''),
    'sameAs' => array_values(array_filter([
        $settings['github_url'] ?? '',
        $settings['linkedin_url'] ?? '',
        $settings['instagram_url'] ?? '',
        $settings['tiktok_url'] ?? '',
        $settings['facebook_url'] ?? '',
        $settings['youtube_url'] ?? ''
    ])),
    'jobTitle' => xuverse_setting($settings, 'resume_headline', 'Software, media and community'),
    'knowsAbout' => [
        'PHP and MySQL web development',
        'Responsive interface design',
        'Photography',
        'Video production',
        'Campus media',
        'Student community work'
    ],
    'address' => [
        '@type' => 'PostalAddress',
        'name' => xuverse_setting($settings, 'location_text', '')
    ]
];

if (!empty($schemaData) && is_array($schemaData)) {
    $jsonLd = ['@context' => 'https://schema.org', '@graph' => [$jsonLd, $schemaData]];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title><?= htmlspecialchars($documentTitle) ?></title>

<meta
name="description"
content="<?= e(xuverse_excerpt($pageDescription, 155)) ?>">

<meta name="robots" content="<?= e($robots) ?>">
<meta name="xuverse-revision" content="<?= e(xuverse_checkout_revision()) ?>">
<meta name="theme-color" content="#050507">
<link rel="canonical" href="<?= e($canonicalUrl) ?>">

<meta property="og:type" content="<?= e($pageType) ?>">
<meta property="og:site_name" content="<?= e($siteTitle) ?>">
<meta property="og:title" content="<?= e($documentTitle) ?>">
<meta property="og:description" content="<?= e(xuverse_excerpt($pageDescription, 155)) ?>">
<meta property="og:url" content="<?= e($canonicalUrl) ?>">
<meta property="og:image" content="<?= e($absoluteImage) ?>">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($documentTitle) ?>">
<meta name="twitter:description" content="<?= e(xuverse_excerpt($pageDescription, 155)) ?>">
<meta name="twitter:image" content="<?= e($absoluteImage) ?>">

<link rel="icon" type="image/svg+xml" href="<?= e(xuverse_url('assets/images/favicon.svg')) ?>">
<?php if ($ownerAvatar !== ''): ?>
<link rel="apple-touch-icon" href="<?= e(xuverse_asset($ownerAvatar)) ?>">
<?php endif; ?>



<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="dns-prefetch" href="//fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;family=Manrope:wght@400;500;600;700;800&amp;family=Space+Grotesk:wght@500;600;700&amp;display=swap">
<link rel="preload" href="<?= e(xuverse_url('assets/css/style.min.css')) ?><?= defined('XUVERSE_RELEASE_ID') && XUVERSE_RELEASE_ID ? '&amp;' : '?' ?>v=20260918-consistency-5" as="style">
<?php if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])): ?>
<meta name="csrf-token" content="<?= e(xuverse_csrf_token()) ?>">
<?php endif; ?>

<link
rel="stylesheet"
href="<?= e(xuverse_url('assets/css/style.min.css')) ?><?= defined('XUVERSE_RELEASE_ID') && XUVERSE_RELEASE_ID ? '&amp;' : '?' ?>v=20260918-consistency-5">
<link rel="stylesheet" href="<?= e(xuverse_url('assets/css/responsive.css')) ?><?= defined('XUVERSE_RELEASE_ID') && XUVERSE_RELEASE_ID ? '&amp;' : '?' ?>v=20260930-3">

<script
src="<?= e(xuverse_url('assets/js/main.min.js')) ?><?= defined('XUVERSE_RELEASE_ID') && XUVERSE_RELEASE_ID ? '&amp;' : '?' ?>v=20260930-1"
defer></script>

<script type="application/ld+json">
<?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_AMP) ?>
</script>

<link rel="stylesheet" href="<?= e(xuverse_url('assets/css/public.css')) ?><?= defined('XUVERSE_RELEASE_ID') && XUVERSE_RELEASE_ID ? '&amp;' : '?' ?>v=<?= substr(hash_file('sha256', __DIR__ . '/../assets/css/public.css'),0,12) ?>">
<meta name="xuverse-content-sha256" content="<?= e(xuverse_content('manifest')['content_sha256']) ?>">
<?php require __DIR__ . '/ambient-motion.php'; ?>
</head>

<body>
<a class="skip-link" href="#main-content">Skip to main content</a>
<div class="top-progress" aria-hidden="true"></div>
