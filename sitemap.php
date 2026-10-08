<?php
require_once 'includes/content.php';
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
$paths=['','about','projects','articles','media','resume','contact'];
foreach(['articles','projects','media'] as $collection) foreach(xuverse_published($collection) as $item) {
    if($collection==='media') {
        $mediaPaths=['video'=>'videos','photograph'=>'photos','card series'=>'photos'];
        if(!isset($mediaPaths[$item['type']])) continue;
        $paths[]=$mediaPaths[$item['type']].'/'.$item['slug'];
    } else $paths[]=$collection.'/'.$item['slug'];
}
foreach($paths as $path) echo '<url><loc>'.e(xuverse_base_url().'/'.$path).'</loc></url>';
echo '</urlset>';
