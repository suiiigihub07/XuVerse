<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../includes/content.php';
function check($value,$message) { if (!$value) { throw new RuntimeException($message); } echo 'PASS: '.$message.PHP_EOL; }
$root=dirname(__DIR__); $manifest=xuverse_content('manifest');
foreach($manifest['files'] as $path=>$hash) check(hash_file('sha256',$root.'/'.$path)===$hash,'Canonical hash '.$path);
check(count(xuverse_content('articles'))===4,'Three essays and one research paper');
foreach(xuverse_content('articles') as $a) {
    check(is_file($root.'/'.$a['pdf_path']),'PDF '.$a['slug']);
    check(hash_file('sha256',$root.'/'.$a['pdf_path'])===$manifest['writing'][$a['slug']]['pdf_sha256'],'PDF hash '.$a['slug']);
    $body=xuverse_markdown(xuverse_body($a)); check(strlen($body)>2500,'Complete body '.$a['slug']);
    check(!str_contains($body,'<script'),'Escaped Markdown '.$a['slug']);
}
$research=xuverse_content('articles')[3]; check(count($research['references'])===7,'Research references retained');
$malicious=xuverse_markdown('<script>alert(1)</script> [bad](javascript:alert) [good](https://example.com)');
check(!str_contains($malicious,'<script') && !str_contains($malicious,'href="javascript:'),'Markdown rejects executable HTML and unsafe links');
foreach(xuverse_content('projects') as $p) {
    check(!empty($p['slug']),'Stable project slug');
    if($p['image']) check(is_file($root.'/'.$p['image']),'Project image '.$p['slug']);
}
foreach(xuverse_content('media')[3]['cards'] as $path) check(is_file($root.'/'.$path),'Credited card '.$path);
$files=array_merge(glob($root.'/*.php'),glob($root.'/includes/*.php'),glob($root.'/scripts/*.php'),glob($root.'/admin/*/*.php'));
foreach($files as $file) { exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($file),$output,$status); if($status!==0) throw new RuntimeException('PHP lint failed: '.$file); }
echo 'PASS: PHP syntax checks'.PHP_EOL;
