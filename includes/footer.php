<?php
require_once __DIR__ . '/content.php';

$footerCopy = xuverse_content('copy');
$footerLinks = xuverse_content('links');
$isContactPage = basename($_SERVER['SCRIPT_NAME'] ?? '') === 'contact.php';
$footerSocials = [
    'github' => 'GitHub',
    'linkedin' => 'LinkedIn',
    'instagram' => 'Instagram',
    'youtube' => 'YouTube',
    'tiktok' => 'TikTok',
    'facebook' => 'Facebook',
];
?>

</main>
</div>

<?php if (!empty($isAdminPage)): ?>
<footer class="site-footer">
    <div class="container footer-core"><p class="footer-copy">&copy; <?= gmdate('Y') ?> <?= e(xuverse_content('site')['site_title']) ?></p></div>
</footer>
<?php else: ?>
<footer class="site-footer xu-footer-contact xu-tone-dark<?= $isContactPage ? ' xu-footer-compact' : '' ?>">
    <div class="xu-footer-inner">
        <div class="xu-footer-top reveal">
            <h2 class="xu-footer-title">
                <?php if ($isContactPage): ?><?= e($footerCopy['contact_heading']) ?>
                <?php else: ?><a href="<?= e(xuverse_url('contact')) ?>"><?= e($footerCopy['contact_heading']) ?></a><?php endif; ?>
            </h2>
            <?php if (!empty($footerLinks['email'])): ?>
            <a class="xu-link" href="<?= e($footerLinks['email']) ?>"><?= e(substr($footerLinks['email'], 7)) ?></a>
            <?php endif; ?>
        </div>
        <div class="xu-footer-bottom reveal">
            <p>&copy; <?= gmdate('Y') ?> <?= e($footerCopy['name']) ?></p>
            <nav class="xu-footer-socials" aria-label="Social links">
                <?php foreach ($footerSocials as $key => $label): ?>
                    <?php if (!empty($footerLinks[$key])): ?>
                    <a href="<?= e($footerLinks[$key]) ?>"><?= e($label) ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>
</footer>
<?php endif; ?>

</body>
</html>
