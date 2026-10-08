<?php
require_once 'includes/content.php';
$copy=xuverse_content('copy'); $site=xuverse_content('site'); $projects=xuverse_published('projects'); $writing=xuverse_published('articles');
xuverse_public_start($site['home_title'],$copy['hero_intro']);
?>
<section class="landing"><div class="container landing-grid">
<div class="landing-copy reveal">
<p class="eyebrow"><?= e($copy['hero_kicker']) ?></p>
<h1><?= e($copy['name']) ?></h1>
<p class="landing-line"><?= e($copy['hero_headline']) ?></p>
<p class="hero-proof"><?= e($copy['hero_intro']) ?></p>
<div class="action-row"><a class="vx-btn primary" href="<?= e(xuverse_url('projects')) ?>"><?= e($copy['hero_actions'][0]) ?></a><a class="vx-btn" href="<?= e(xuverse_url('resume')) ?>"><?= e($copy['hero_actions'][1]) ?></a><a class="vx-btn ghost" href="<?= e(xuverse_url('contact')) ?>"><?= e($copy['hero_actions'][2]) ?></a></div>
<p class="focus-line"><?= e($copy['current_focus']) ?></p>
</div>
<aside class="identity-panel reveal magnetic-card"><img src="<?= e(xuverse_url($site['portrait'])) ?>" alt="<?= e($site['portrait_alt']) ?>" width="1024" height="1024" loading="eager" fetchpriority="high" decoding="async"></aside>
</div></section>
<section class="section nav-deck-section" id="worlds"><div class="container"><div class="nav-deck">
<?php foreach([['01','projects',$site['deck_projects'],$copy['projects_intro']],['02','media',$site['deck_media'],$copy['media_intro']],['03','articles',$site['deck_articles'],$copy['articles_intro']],['04','about',$site['deck_about'],$copy['research_intro']]] as $area): ?>
<a class="deck-card reveal" href="<?= e(xuverse_url($area[1])) ?>"><span><?= e($area[0]) ?></span><strong><?= e($area[2]) ?></strong><em><?= e($area[3]) ?></em></a>
<?php endforeach; ?></div></div></section>
<section class="section home-featured"><div class="container"><div class="section-head-compact reveal"><p class="eyebrow"><?= e($site['featured_kicker']) ?></p><h2><?= e($site['featured_heading']) ?></h2></div><div class="public-grid">
<?php $featuredArticle=xuverse_find_public('articles',$site['featured_article']); $featuredProject=xuverse_find_public('projects',$site['featured_project']); $featuredVideo=xuverse_find_public('media',$site['featured_video']); if($featuredArticle) xuverse_public_card($featuredArticle,'articles'); if($featuredProject) xuverse_public_card($featuredProject,'projects',$featuredProject['image']); ?>
<?php if($featuredVideo): ?><article class="public-card media-card reveal magnetic-card"><p class="eyebrow"><?= e($site['nav_media']) ?></p><h2><a href="<?= e(xuverse_url('videos/'.$featuredVideo['slug'])) ?>"><?= e($featuredVideo['title']) ?></a></h2><a class="text-link" href="<?= e(xuverse_url('videos/'.$featuredVideo['slug'])) ?>">Watch video</a></article><?php endif; ?>
</div></div></section>
<section class="section home-writing"><div class="container"><div class="section-head-compact reveal"><p class="eyebrow"><?= e($site['writing_kicker']) ?></p><h2><?= e($site['writing_heading']) ?></h2><p><?= e($copy['articles_intro']) ?></p></div><div class="public-grid"><?php foreach(array_slice($writing,0,3) as $a) xuverse_public_card($a,'articles'); ?></div></div></section>
<section class="section"><div class="container"><div class="section-head-compact reveal"><p class="eyebrow"><?= e($site['media_kicker']) ?></p><h2><?= e($site['media_heading']) ?></h2><p><?= e($copy['media_intro']) ?></p></div><div class="public-grid">
<?php foreach($site['home_videos'] as $slug): $m=xuverse_find_public('media',$slug); if(!$m || $m['type']!=='video') continue; ?>
<article class="media-card video-card compact-media-card reveal magnetic-card"><div class="video-detail-frame media-player"><iframe src="<?= e(xuverse_youtube_embed($m['url'])) ?>" title="<?= e($m['title']) ?>" width="1600" height="900" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div><div class="media-content"><h3><a href="<?= e(xuverse_url('videos/'.$m['slug'])) ?>"><?= e($m['title']) ?></a></h3></div></article>
<?php endforeach; ?>
<article class="public-card article-card reveal"><p class="eyebrow"><?= e($site['photo_kicker']) ?></p><h2><?= e($site['photo_heading']) ?></h2><p><?= e($site['photo_intro']) ?></p><a class="text-link" href="<?= e(xuverse_url('media#photographs')) ?>">Explore media</a></article>
</div></div></section>
<section class="section home-connect"><div class="container"><div class="section-head-compact reveal"><p class="eyebrow">Connect</p><h2><?= e($copy['contact_heading']) ?></h2><p><?= e($copy['contact_intro']) ?></p><div class="action-row"><a class="vx-btn primary" href="<?= e(xuverse_url('contact')) ?>">Connect</a><a class="vx-btn ghost" href="<?= e(xuverse_url('about')) ?>">About me</a></div></div></div></section>
<?php include 'includes/footer.php'; ?>
