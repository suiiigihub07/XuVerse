<?php
require_once __DIR__ . '/content.php';

$footerCopy = xuverse_content('copy');
$footerLinks = xuverse_content('links');
$showFooterContact = basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'contact.php';
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
<footer class="site-footer<?= $showFooterContact ? ' xu-footer-contact' : '' ?>">
    <div class="xu-footer-inner<?= $showFooterContact ? '' : ' xu-footer-compact' ?>">
        <?php if ($showFooterContact): ?>
        <div class="xu-footer-top">
            <a class="xu-footer-title" href="<?= e(xuverse_url('contact')) ?>"><?= e($footerCopy['contact_heading']) ?> <span aria-hidden="true">↗</span></a>
            <a class="xu-link" href="<?= e($footerLinks['email']) ?>">Email me <span aria-hidden="true">↗</span></a>
        </div>
        <?php endif; ?>
        <div class="xu-footer-bottom">
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
