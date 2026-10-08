<?php
require_once 'includes/content.php';
$a=xuverse_find_public('articles',$_GET['slug'] ?? '',(int)($_GET['id'] ?? 0));
if(!$a){http_response_code(404);require '404.php';exit;}
if(empty($_GET['slug'])){header('Location: '.xuverse_url('articles/'.$a['slug']),true,301);exit;}
$pageType='article'; $schemaData=['@type'=>'Article','headline'=>$a['title'],'description'=>$a['summary'],'datePublished'=>$a['published_at'],'author'=>['@type'=>'Person','name'=>$a['author']]];
xuverse_public_start($a['title'],$a['summary'],'articles/'.$a['slug']);
?>
<section class="section container public-reading"><article><p class="eyebrow"><?= e($a['category']) ?></p><h1><?= e($a['title']) ?></h1><p class="lead"><?= e($a['subtitle']) ?></p><p><?= e($a['author']) ?> · <time datetime="<?= e($a['written_date']) ?>"><?= e($a['date_display']) ?></time> · <?= e($a['reading_time_minutes']) ?> min read</p><?php if(isset($a['study_type'])): ?><p class="research-note"><?= e($a['study_type']) ?>. <?= e($a['status']) ?>. <?= e($a['contribution']) ?>.</p><?php endif; ?><div class="public-actions"><a class="btn secondary-btn" href="<?= e(xuverse_url($a['pdf_path'])) ?>" download>Download PDF</a><button class="share-button" data-share-url="<?= e(xuverse_base_url().'/articles/'.$a['slug']) ?>" data-share-title="<?= e($a['title']) ?>">Share</button></div><div class="article-body prose"><?= xuverse_markdown(xuverse_body($a)) ?></div>
<?php if(isset($a['references'])): ?><aside class="source-notes"><h2>Source access</h2><p>Seven sources; four full texts and three abstract or partial sources. Checked 8 October 2026.</p><ul><?php foreach($a['references'] as $ref): ?><li><a href="<?= e($ref['url']) ?>"><?= e($ref['citation']) ?></a><p><?= e($ref['access']) ?></p></li><?php endforeach; ?></ul></aside><?php endif; ?>
<h2>Related reading</h2><ul><?php foreach(xuverse_content('articles') as $related): if($related['slug']===$a['slug'])continue; ?><li><a href="<?= e(xuverse_url('articles/'.$related['slug'])) ?>"><?= e($related['title']) ?></a></li><?php endforeach; ?></ul><a class="text-link" href="<?= e(xuverse_url('articles')) ?>">← Back to articles</a></article></section>
<?php include 'includes/footer.php'; ?>
