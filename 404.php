<?php

require_once 'includes/db.php';
require_once 'includes/functions.php';

$settings = $conn->query(
    "SELECT * FROM settings
     LIMIT 1"
)->fetch_assoc();

http_response_code(404);

$pageTitle = 'Deep Space - XuVerse';
$pageDescription = 'This XuVerse route drifted into deep space.';
$canonicalUrl = xuverse_current_url();
$robots = xuverse_noindex();

include 'includes/header.php';
include 'includes/navbar.php';

?>

<section class="section error-space">
<div class="container">
<p class="page-kicker">404 / Deep Space</p>
<h1>Signal lost.</h1>
<p>The page you were looking for drifted beyond the mapped universe.</p>
<div class="action-row">
<a href="<?= e(xuverse_url()) ?>" class="vx-btn primary">Return Home</a>
<a href="<?= e(xuverse_url('now')) ?>" class="vx-btn ghost">What I’m doing now</a>
</div>
</div>
</section>

<?php include 'includes/footer.php'; ?>
