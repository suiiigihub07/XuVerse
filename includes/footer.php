<?php

require_once __DIR__ . '/content.php';

$footerCopy = xuverse_content('copy');
$footerLinks = xuverse_content('links');
$siteTitle = xuverse_content('site')['site_title'];

$socialLinks = [
    'github' => ['label' => 'GitHub', 'url' => $footerLinks['github']],
    'linkedin' => ['label' => 'LinkedIn', 'url' => $footerLinks['linkedin']],
    'instagram' => ['label' => 'Instagram', 'url' => $footerLinks['instagram']],
    'youtube' => ['label' => 'YouTube', 'url' => $footerLinks['youtube']],
    'tiktok' => ['label' => 'TikTok', 'url' => $footerLinks['tiktok']],
    'facebook' => ['label' => 'Facebook', 'url' => $footerLinks['facebook']]
];

function xuverse_social_icon($name)
{
    $icons = [
        'github' => '<path d="M12 2a10 10 0 0 0-3.16 19.49c.5.09.68-.22.68-.48v-1.7c-2.78.6-3.37-1.19-3.37-1.19-.46-1.16-1.12-1.47-1.12-1.47-.91-.62.07-.61.07-.61 1.01.07 1.54 1.04 1.54 1.04.9 1.53 2.36 1.09 2.93.83.09-.65.35-1.09.64-1.34-2.22-.25-4.55-1.11-4.55-4.94 0-1.09.39-1.98 1.03-2.68-.1-.25-.45-1.27.1-2.64 0 0 .84-.27 2.75 1.02A9.56 9.56 0 0 1 12 5.99c.85 0 1.71.11 2.51.34 1.91-1.29 2.75-1.02 2.75-1.02.55 1.37.2 2.39.1 2.64.64.7 1.03 1.59 1.03 2.68 0 3.84-2.34 4.68-4.57 4.93.36.31.68.92.68 1.86v2.75c0 .27.18.58.69.48A10 10 0 0 0 12 2Z"/>',
        'linkedin' => '<path d="M6.94 8.98H3.75v10.27h3.19V8.98ZM5.35 7.58a1.84 1.84 0 1 0 0-3.68 1.84 1.84 0 0 0 0 3.68Zm13.9 6.04c0-3.09-1.65-4.53-3.86-4.53a3.34 3.34 0 0 0-3.04 1.67h-.04V8.98H9.25v10.27h3.18v-5.08c0-1.34.25-2.64 1.92-2.64 1.64 0 1.66 1.54 1.66 2.73v4.99h3.19l.05-5.63Z"/>',
        'instagram' => '<path d="M7.8 2h8.4A5.8 5.8 0 0 1 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8A5.8 5.8 0 0 1 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2Zm-.2 2A3.6 3.6 0 0 0 4 7.6v8.8A3.6 3.6 0 0 0 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6A3.6 3.6 0 0 0 16.4 4H7.6Zm9.65 1.55a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4ZM12 7.25A4.75 4.75 0 1 1 12 16.75 4.75 4.75 0 0 1 12 7.25Zm0 2A2.75 2.75 0 1 0 12 14.75 2.75 2.75 0 0 0 12 9.25Z"/>',
        'tiktok' => '<path d="M15.5 2c.32 2.27 1.58 3.62 3.9 3.77v3.12a7.02 7.02 0 0 1-3.82-1.16v5.88c0 3.17-2.05 6.02-5.62 6.02-3.08 0-5.36-2.08-5.36-5.1 0-3.42 2.96-5.62 6.24-5.19v3.2c-1.43-.44-3.05.35-3.05 1.93 0 1.17.9 1.91 2.03 1.91 1.56 0 2.34-1.02 2.34-2.75V2h3.34Z"/>',
        'facebook' => '<path d="M14.04 22v-8.1h2.72l.41-3.16h-3.13V8.72c0-.91.25-1.54 1.57-1.54h1.68V4.36A22.7 22.7 0 0 0 14.84 4c-2.43 0-4.1 1.49-4.1 4.22v2.52H8v3.16h2.75V22h3.29Z"/>',
        'youtube' => '<path d="M21.58 7.19a2.63 2.63 0 0 0-1.85-1.86C18.1 4.9 12 4.9 12 4.9s-6.1 0-7.73.43a2.63 2.63 0 0 0-1.85 1.86A27.31 27.31 0 0 0 2 12a27.31 27.31 0 0 0 .42 4.81 2.63 2.63 0 0 0 1.85 1.86c1.63.43 7.73.43 7.73.43s6.1 0 7.73-.43a2.63 2.63 0 0 0 1.85-1.86A27.31 27.31 0 0 0 22 12a27.31 27.31 0 0 0-.42-4.81ZM10 15.07V8.93L15.2 12 10 15.07Z"/>'
    ];

    return $icons[$name] ?? '<circle cx="12" cy="12" r="8"/>';
}

?>

</main>
</div>

<footer class="site-footer" aria-label="XuVerse closing signature">
<div class="container footer-core">
<div class="footer-mark">
<a href="<?= e(xuverse_url()) ?>" class="footer-brand"><?= e($siteTitle) ?></a>
<p><?= e($footerCopy['footer']) ?></p>
</div>

<nav class="footer-icons" aria-label="Social links">
<?php foreach ($socialLinks as $key => $social): ?>
<a href="<?= e($social['url']) ?>" target="_blank" rel="noopener noreferrer"
   aria-label="<?= e($social['label']) ?>" class="magnetic-card">
<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
<?= xuverse_social_icon($key) ?>
</svg>
</a>
<?php endforeach; ?>
</nav>

<p class="footer-copy">&copy; <?= gmdate('Y') ?> <?= e($siteTitle) ?></p>
</div>
</footer>

</body>
</html>
