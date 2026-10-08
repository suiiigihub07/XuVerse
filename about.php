<?php require_once 'includes/content.php'; $copy=xuverse_content('copy'); xuverse_public_start('About',$copy['about'],'about'); ?>
<section class="section container public-reading"><p class="eyebrow">About</p><h1>B K Suraj</h1><p class="lead"><?= e($copy['about']) ?></p><p><?= e($copy['philosophy']) ?></p><p><?= e($copy['location']) ?></p><a class="text-link" href="<?= e(xuverse_url('resume')) ?>">Read the fuller resume →</a></section>
<?php include 'includes/footer.php'; ?>
