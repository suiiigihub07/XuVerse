<?php
require_once 'includes/content.php';
$copy=xuverse_content('copy'); $projects=xuverse_content('projects'); $writing=xuverse_content('articles');
xuverse_public_start('XuVerse',$copy['hero_intro']);
?>
<section class="landing"><div class="container landing-grid">
<div class="landing-copy reveal">
<p class="eyebrow"><?= e($copy['hero_kicker']) ?></p>
<h1><?= e($copy['name']) ?></h1>
<p class="landing-line"><?= e($copy['hero_headline']) ?></p>
<p class="hero-proof"><?= e($copy['hero_intro']) ?></p>
<div class="action-row"><a class="vx-btn primary" href="<?= e(xuverse_url('projects')) ?>">See My Work</a><a class="vx-btn" href="<?= e(xuverse_url('resume')) ?>">Resume</a><a class="vx-btn ghost" href="<?= e(xuverse_url('contact')) ?>">Let’s Connect</a></div>
<p class="focus-line"><?= e($copy['current_focus']) ?></p>
</div>
<aside class="identity-panel reveal magnetic-card"><img src="<?= e(xuverse_url('assets/images/public/portrait.webp')) ?>" alt="Portrait of B K Suraj" width="1024" height="1024" loading="eager" fetchpriority="high" decoding="async"></aside>
</div></section>
<section class="section nav-deck-section" id="worlds"><div class="container"><div class="nav-deck">
<?php foreach([['01','projects','Projects & ideas',$copy['projects_intro']],['02','media','Photo & video',$copy['media_intro']],['03','articles','Writing & learning',$copy['articles_intro']],['04','about','About & exploration',$copy['research_intro']]] as $area): ?>
<a class="deck-card reveal" href="<?= e(xuverse_url($area[1])) ?>"><span><?= e($area[0]) ?></span><strong><?= e($area[2]) ?></strong><em><?= e($area[3]) ?></em></a>
<?php endforeach; ?></div></div></section>
<section class="section home-featured"><div class="container"><div class="section-head-compact reveal"><p class="eyebrow">Selected Work</p><h2>Ideas taking shape.</h2></div><div class="public-grid">
<?php xuverse_public_card($writing[3],'articles');xuverse_public_card($projects[0],'projects',$projects[0]['image']); ?>
<article class="public-card media-card reveal magnetic-card"><p class="eyebrow">Media</p><h2><a href="<?= e(xuverse_url('videos/global-college-2026')) ?>">Dong-Eui Global College 2026</a></h2><p>Selected university promotional media work.</p><a class="text-link" href="<?= e(xuverse_url('videos/global-college-2026')) ?>">Watch video</a></article>
</div></div></section>
<section class="section home-writing"><div class="container"><div class="section-head-compact reveal"><p class="eyebrow">Writing</p><h2>Looking a little closer.</h2><p><?= e($copy['articles_intro']) ?></p></div><div class="public-grid"><?php foreach(array_slice($writing,0,3) as $a) xuverse_public_card($a,'articles'); ?></div></div></section>
<section class="section"><div class="container"><div class="section-head-compact reveal"><p class="eyebrow">Selected Media</p><h2>Images, movement, and community.</h2><p><?= e($copy['media_intro']) ?></p></div><div class="public-grid">
<?php foreach([xuverse_content('media')[0],xuverse_content('media')[2]] as $m):parse_str(parse_url($m['url'],PHP_URL_QUERY),$q); ?>
<article class="media-card video-card reveal magnetic-card"><div class="video-detail-frame media-player"><iframe src="https://www.youtube-nocookie.com/embed/<?= e($q['v']) ?>" title="<?= e($m['title']) ?>" width="1600" height="900" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div><div class="media-content"><h2><?= e($m['title']) ?></h2><p><?= e($m['summary']) ?></p><a class="text-link" href="<?= e(xuverse_url('videos/'.$m['slug'])) ?>">Video details</a></div></article>
<?php endforeach; ?>
<article class="public-card article-card reveal"><p class="eyebrow">Photographs & visuals</p><h2>From the photo collection.</h2><p>Photographs, selected videos, and credited ANCHOR visual communication work.</p><a class="text-link" href="<?= e(xuverse_url('media#photographs')) ?>">Explore media</a></article>
</div></div></section>
<section class="section home-connect"><div class="container"><div class="section-head-compact reveal"><p class="eyebrow">Connect</p><h2><?= e($copy['contact_heading']) ?></h2><p><?= e($copy['contact_intro']) ?></p><div class="action-row"><a class="vx-btn primary" href="<?= e(xuverse_url('contact')) ?>">Connect</a><a class="vx-btn ghost" href="<?= e(xuverse_url('about')) ?>">About me</a></div></div></div></section>
<?php include 'includes/footer.php'; ?>
