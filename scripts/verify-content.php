<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../includes/content.php';
require_once __DIR__ . '/../includes/content-build.php';
require_once __DIR__ . '/../includes/dashboard-content.php';
function check($value,$message) { if (!$value) { throw new RuntimeException($message); } echo 'PASS: '.$message.PHP_EOL; }
function verify_shared_asset_scope() {
    // Header includes share their caller's PHP scope (Resume and legacy media forms).
    $name='B K Suraj'; $type='photo'; $path='caller-path'; $url='caller-url'; $separator='caller-separator';
    ob_start(); require __DIR__.'/../includes/ambient-motion.php'; $assets=ob_get_clean();
    check([$name,$type,$path,$url,$separator]===['B K Suraj','photo','caller-path','caller-url','caller-separator'],'Shared asset loader preserves caller variables');
    foreach(['ambient-motion.css','media-gallery.css','rounded.css','ambient-motion.js','media-gallery.js'] as $asset) check(str_contains($assets,$asset),'Shared asset '.$asset);
}
verify_shared_asset_scope();
$root=dirname(__DIR__); $manifest=xuverse_content('manifest');
foreach($manifest['files'] as $path=>$hash) check(hash_file('sha256',$root.'/'.$path)===$hash,'Canonical hash '.$path);
foreach (array_keys(xuverse_editor_sections()) as $collection) { xuverse_editor_validate($collection,xuverse_content($collection)); check(true,'Content structure '.$collection); }
$articles = xuverse_content('articles'); $bySlug = array_column($articles,null,'slug');
$retained = ['kage','before-the-next-cut','what-the-frame-holds','same-moment-different-meaning'];
foreach ($retained as $slug) check(isset($bySlug[$slug]) && ($bySlug[$slug]['publication_status'] ?? '') === 'published','Retained supplied article '.$slug);
foreach($articles as $a) {
    $body=xuverse_markdown(xuverse_body($a)); check(strlen($body)>(in_array($a['slug'],$retained,true) ? 2500 : 0),'Complete body '.$a['slug']);
    check(!str_contains($body,'<script'),'Escaped Markdown '.$a['slug']);
    if (($a['publication_status'] ?? 'published') === 'draft') { check(!isset($manifest['writing'][$a['slug']]),'Draft has no generated publication '.$a['slug']); continue; }
    check(is_file($root.'/'.$a['pdf_path']),'PDF '.$a['slug']);
    check(hash_file('sha256',$root.'/'.$a['pdf_path'])===($manifest['writing'][$a['slug']]['pdf_sha256'] ?? ''),'PDF hash '.$a['slug']);
    check(xuverse_pdf_source_hash($a)===($manifest['writing'][$a['slug']]['source_sha256'] ?? ''),'PDF source hash '.$a['slug']);
}
$research = $bySlug['same-moment-different-meaning'];
$retainedSources = ['https://www2.eecs.berkeley.edu/Pubs/TechRpts/2012/EECS-2012-94.html','https://doi.org/10.1163/22134913-00002024','https://terrybarrettosu.com/wp-content/uploads/2017/08/Barrett-1986-Photographs-Contexts.pdf','https://doi.org/10.16910/jemr.17.5.2','https://doi.org/10.1068/p6700','https://doi.org/10.1163/156856808784532662','https://doi.org/10.1068/p7233'];
check(!array_diff($retainedSources,array_column($research['references'] ?? [],'url')),'Seven supplied research sources retained');
check(($research['original_participant_data'] ?? null) === false && ($research['original_photographic_dataset'] ?? null) === false,'Research does not claim original participant data or photographs');
check(($research['status'] ?? '') === 'Independent study; not peer-reviewed','Research status retained');
foreach (xuverse_content('articles') as $research) foreach ($research['references'] ?? [] as $reference) { check(filter_var($reference['url'],FILTER_VALIDATE_URL) && in_array(parse_url($reference['url'],PHP_URL_SCHEME),['https','http'],true),'Safe research source URL'); }
$malicious=xuverse_markdown('<script>alert(1)</script> [bad](javascript:alert) [good](https://example.com)');
check(!str_contains($malicious,'<script') && !str_contains($malicious,'href="javascript:'),'Markdown rejects executable HTML and unsafe links');
foreach(xuverse_content('projects') as $p) {
    check(!empty($p['slug']),'Stable project slug');
    if($p['image']) check(is_file($root.'/'.$p['image']),'Project image '.$p['slug']);
    foreach(['repository','live'] as $field) if(!empty($p[$field])) check(filter_var($p[$field],FILTER_VALIDATE_URL) && str_starts_with($p[$field],'https://'),'Safe project URL '.$field);
}
foreach(xuverse_content('media') as $m) if ($m['type']==='video') check(xuverse_youtube_embed($m['url']) !== '', 'Supported YouTube URL');
foreach(['github','linkedin','instagram','youtube','tiktok','facebook'] as $key) check(filter_var(xuverse_content('links')[$key],FILTER_VALIDATE_URL) && str_starts_with(xuverse_content('links')[$key],'https://'),'Safe social URL '.$key);
foreach(xuverse_content('media') as $m) if ($m['type']==='card series') { check(count($m['cards'])>=1 && count($m['cards'])<=10,'Gallery has 1–10 images'); foreach($m['cards'] as $path) check(is_file($root.'/'.$path),'Gallery image '.$path); }
foreach(xuverse_content('media') as $m) if ($m['type']==='photograph') check(xuverse_editor_asset_valid($m['image'] ?? ''),'Photograph image '.$m['slug']);
$files=array_merge(glob($root.'/*.php'),glob($root.'/includes/*.php'),glob($root.'/scripts/*.php'),glob($root.'/admin/*/*.php'));
foreach($files as $file) { exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($file),$output,$status); if($status!==0) throw new RuntimeException('PHP lint failed: '.$file); }
echo 'PASS: PHP syntax checks'.PHP_EOL;
