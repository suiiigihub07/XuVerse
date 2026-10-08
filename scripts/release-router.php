<?php
// Installed once at the web root as xuverse-release.php. Releases never mutate it.
$storage = __DIR__;
$active = trim((string)@file_get_contents($storage . '/.xuverse-active'));
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$prefix = rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME'])), '/.');
if ($prefix && str_starts_with($path,$prefix.'/')) { $path = substr($path,strlen($prefix)); }
$path = ltrim($path,'/');
if (preg_match('#(^|/)(\.|content|output|backups|scripts|includes|database|vendor|tmp|dist|presentation-output)#i',$path)
    || preg_match('#config(?:\.[a-z]+)?\.php$|\.(sql|md|ini|log|bak|ps1|sh|zip)$#i',$path)) { http_response_code(403); exit; }
$release = $active;
if (str_starts_with($path,'assets/') && isset($_GET['r'])) { $release = $_GET['r']; }
if ($release !== '' && !preg_match('/^[a-f0-9]{40}$/',$release)) { http_response_code(503); exit('Release unavailable'); }
$root = $release === '' ? $storage : $storage . '/.xuverse-releases/' . $release;
if ($release && !is_file($root.'/.ready')) { http_response_code(503); exit('Release unavailable'); }
define('XUVERSE_STORAGE_ROOT',$storage);
define('XUVERSE_RELEASE_ID',$release);
putenv('XUVERSE_CONFIG_FILE='.$storage.'/config.local.php');
if (str_starts_with($path,'assets/')) {
    $file=realpath($root.'/'.$path); $assetRoot=realpath($root.'/assets');
    if (!$file || !$assetRoot || !str_starts_with($file,$assetRoot.DIRECTORY_SEPARATOR) || !is_file($file)) { http_response_code(404); exit; }
    $ext=strtolower(pathinfo($file,PATHINFO_EXTENSION));
    $types=['css'=>'text/css','js'=>'application/javascript','webp'=>'image/webp','avif'=>'image/avif','png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','svg'=>'image/svg+xml','woff2'=>'font/woff2','pdf'=>'application/pdf'];
    if (!isset($types[$ext])) { http_response_code(403); exit; }
    header('Content-Type: '.$types[$ext]); header('X-Content-Type-Options: nosniff');
    header('Cache-Control: '.($release?'public, max-age=31536000, immutable':'no-cache'));
    readfile($file); exit;
}
if ($path==='release.json') {
    header('Content-Type: application/json'); header('Cache-Control: no-store');
    echo json_encode(['revision'=>$release,'content_sha256'=>json_decode(file_get_contents($root.'/content/manifest.json'),true)['content_sha256'] ?? null]); exit;
}
$routes=[''=>'index.php','about'=>'about.php','projects'=>'projects.php','articles'=>'articles.php','media'=>'media.php','resume'=>'resume.php','resume/download'=>'resume-download.php','contact'=>'contact.php','now'=>'now.php','login'=>'login.php','robots.txt'=>'robots.php','sitemap.xml'=>'sitemap.php'];
$normalized=rtrim($path,'/'); $script=$routes[$normalized] ?? null;
if (in_array($path,['index.php','about.php','projects.php','articles.php','media.php','resume.php','contact.php','now.php','login.php'],true)) {
    $pretty=$path==='index.php'?'':substr($path,0,-4);
    header('Location: '.$prefix.'/'.$pretty,true,301); exit;
}
if (preg_match('#^(articles|projects|videos|photos)/([a-z0-9-]+)$#',$normalized,$m)) {
    $script=['articles'=>'article.php','projects'=>'project.php','videos'=>'video.php','photos'=>'photo.php'][$m[1]];
    if (ctype_digit($m[2])) { $_GET['id']=(int)$m[2]; } else { $_GET['slug']=$m[2]; }
}
if (!$script && preg_match('#^(?:admin/[a-z-]+/(?:index|edit|create|delete|upload|photos|videos|profile|password)|admin/dashboard|index|about|article|articles|project|projects|photo|video|media|resume|resume-download|contact|now|login|logout|upload_avatar|404)\.php$#',$path)) { $script=$path; }
if (!$script || !is_file($root.'/'.$script)) { $script='404.php'; http_response_code(404); }
$_SERVER['SCRIPT_NAME']=$prefix.'/'.$script;
chdir(dirname($root.'/'.$script)); require $root.'/'.$script;
