<?php
require_once 'includes/content.php';
$a=xuverse_find_public('articles',$_GET['slug'] ?? '',(int)($_GET['id'] ?? 0));
if(!$a){http_response_code(404);require '404.php';exit;}
if(empty($_GET['slug'])){header('Location: '.xuverse_url('articles/'.$a['slug']),true,301);exit;}
$pageType='article';$schemaData=['@type'=>'Article','headline'=>$a['title'],'description'=>$a['summary'],'author'=>['@type'=>'Person','name'=>$a['author']]];
if (!empty($a['published_at'])) $schemaData['datePublished'] = $a['published_at'];
xuverse_public_start($a['title'],$a['summary'],'articles/'.$a['slug']);
?>
<section class="page-hero"><div class="container reading-header"><p class="eyebrow"><?= e($a['category']) ?></p><h1><?= e($a['title']) ?></h1><p class="lead"><?= e($a['subtitle']) ?></p><div class="reading-meta"><span><?= e($a['author']) ?></span><time<?= $a['date_precision'] === 'year' ? '' : ' datetime="'.e($a['written_date']).'"' ?>><?= e($a['date_display']) ?></time><span><?= e($a['reading_time_minutes']) ?> min read</span></div></div></section>
<section class="section"><article class="public-reading article-detail">
<?php if(isset($a['study_type'])): ?><p class="research-note"><?= e($a['study_type']) ?>. <?= e($a['status']) ?>. <?= e($a['contribution']) ?>.</p><?php endif; ?>
<div class="article-toolbar public-actions"><a class="btn" href="<?= e(xuverse_url($a['pdf_path'])) ?>" download>Download PDF</a><button class="share-button" data-share-url="<?= e(xuverse_base_url().'/articles/'.$a['slug']) ?>" data-share-title="<?= e($a['title']) ?>">Share</button></div>
<div class="article-body prose"><?= xuverse_markdown(xuverse_body($a)) ?></div>
<?php if(!empty($a['references'])): ?><aside class="source-notes"><h2><?= e(xuverse_content('site')['source_access_heading']) ?></h2><p><?= e($a['source_note'] ?? xuverse_content('site')['research_source_note']) ?></p><ul><?php foreach($a['references'] as $ref): ?><li><a href="<?= e($ref['url']) ?>"><?= e($ref['citation']) ?></a><p><?= e($ref['access']) ?></p></li><?php endforeach; ?></ul></aside><?php endif; ?>
<div class="related-reading"><h2>Related reading</h2><ul><?php foreach(xuverse_published('articles') as $related):if($related['slug']===$a['slug'])continue; ?><li><a href="<?= e(xuverse_url('articles/'.$related['slug'])) ?>"><?= e($related['title']) ?></a></li><?php endforeach; ?></ul><a class="text-link" href="<?= e(xuverse_url('articles')) ?>">Back to articles</a></div>
</article></section><?php include 'includes/footer.php'; ?>
