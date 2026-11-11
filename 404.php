<?php
require_once 'includes/content.php';

http_response_code(404);
xuverse_public_start('Page not found', 'This page could not be found.', null);
?>

<section class="xu-container xu-error" aria-labelledby="error-title">
    <p class="xu-kicker">404</p>
    <h1 id="error-title">Page not found.</h1>
    <p>The address may have changed. You can find the latest work from the homepage.</p>
    <a class="xu-link" href="<?= e(xuverse_url()) ?>">Back to home <span aria-hidden="true">↗</span></a>
</section>

<?php include 'includes/footer.php'; ?>
