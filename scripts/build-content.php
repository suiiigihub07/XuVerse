<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../includes/content.php';
require_once __DIR__ . '/../vendor/autoload.php';
$root = dirname(__DIR__);
$articles = xuverse_content('articles');
$manifest = ['content_sha256'=>'', 'files'=>[], 'writing'=>[]];
$hash = hash_init('sha256');
$paths = glob($root . '/content/*.json');
foreach (glob($root . '/content/writing/*.md') as $path) { $paths[] = $path; }
sort($paths);
foreach ($paths as $path) {
    if (basename($path) === 'manifest.json') { continue; }
    $relative = str_replace('\\', '/', substr($path, strlen($root)+1));
    $bytes = file_get_contents($path);
    if (str_ends_with($path, '.json')) { json_decode($bytes, true, 512, JSON_THROW_ON_ERROR); }
    $manifest['files'][$relative] = hash('sha256',$bytes);
    hash_update($hash, $relative . "\0" . $bytes . "\0");
}
$manifest['content_sha256'] = hash_final($hash);
if (!is_dir($root . '/assets/downloads')) { mkdir($root . '/assets/downloads',0755,true); }
$slugs = [];
foreach ($articles as $a) {
    foreach (['slug','title','subtitle','author','date_precision','written_date','summary','category','tags','publication_status','pdf_path','related_links'] as $key) {
        if (!array_key_exists($key,$a)) { throw new RuntimeException('Missing article field: ' . $key); }
    }
    if (!preg_match('/^[a-z0-9-]+$/',$a['slug']) || isset($slugs[$a['slug']])) { throw new RuntimeException('Invalid or duplicate slug'); }
    $slugs[$a['slug']] = true;
    if ($a['author'] !== 'B K Suraj' || !in_array($a['date_precision'],['year','month','day'],true)) { throw new RuntimeException('Invalid metadata'); }
    if ($a['pdf_path'] !== 'assets/downloads/' . $a['slug'] . '.pdf') { throw new RuntimeException('Invalid PDF path'); }
    $sourceHash = hash('sha256',json_encode($a,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . file_get_contents($root.'/content/writing/'.$a['slug'].'.md') . hash_file('sha256',__FILE__) . hash_file('sha256',$root.'/includes/content.php'));
    $pdfPath = $root . '/' . $a['pdf_path'];
    $previous = is_file($root.'/content/manifest.json') ? json_decode(file_get_contents($root.'/content/manifest.json'),true) : [];
    if (!is_file($pdfPath) || ($previous['writing'][$a['slug']]['source_sha256'] ?? '') !== $sourceHash) {
        $options = new \Dompdf\Options();
        $options->set('defaultFont','DejaVu Sans'); $options->set('isRemoteEnabled',false);
        $options->set('isPhpEnabled',false); $options->set('isJavascriptEnabled',false);
        $options->set('chroot',[$root.'/vendor/dompdf/dompdf/lib/fonts']);
        $pdf = new \Dompdf\Dompdf($options); $pdf->setPaper('A4');
        $html = '<html><head><meta charset="utf-8"><style>@page{margin:48pt 46pt 50pt}body{font-family:DejaVu Sans;font-size:10pt;line-height:1.55;color:#222c2a}h1{font-size:24pt;line-height:1.2}h2{font-size:14pt;margin-top:24pt;page-break-after:avoid}h3{font-size:12pt;page-break-after:avoid}p{margin:0 0 12pt}a{color:#355d50;overflow-wrap:anywhere}table{width:100%;border-collapse:collapse;font-size:8pt}td,th{border:1px solid #ccc;padding:6pt;text-align:left}tr{page-break-inside:avoid}.metadata{color:#58635f;font-size:9pt}</style></head><body><h1>' . e($a['title']) . '</h1><p>' . e($a['subtitle']) . '</p><p class="metadata">' . e($a['author'] . ' · ' . $a['date_display']) . '</p>' . xuverse_markdown(xuverse_body($a));
        if (isset($a['references'])) {
            $html .= '<h2>Source access</h2><p>Seven sources; four full texts and three abstract or partial sources. Checked 8 October 2026.</p>';
            foreach ($a['references'] as $ref) { $html .= '<p><a href="'.e($ref['url']).'">'.e($ref['citation']).'</a><br>'.e($ref['access']).'</p>'; }
        }
        $pdf->loadHtml($html . '</body></html>','UTF-8'); $pdf->render();
        $pdf->addInfo('Title',$a['title']); $pdf->addInfo('Author',$a['author']); $pdf->addInfo('Subject','XuVerse source SHA-256: '.$sourceHash);
        $canvas=$pdf->getCanvas(); $font=$pdf->getFontMetrics()->getFont('DejaVu Sans','normal');
        $canvas->page_text(46,812,'XuVerse · B K Suraj',$font,8,[.35,.39,.37]);
        $canvas->page_text(440,812,'{PAGE_NUM} / {PAGE_COUNT}',$font,8,[.35,.39,.37]);
        file_put_contents($pdfPath . '.tmp',$pdf->output()); rename($pdfPath.'.tmp',$pdfPath);
        echo 'Generated ' . $a['slug'] . PHP_EOL;
    }
    $manifest['writing'][$a['slug']] = ['source_sha256'=>$sourceHash,'pdf_sha256'=>hash_file('sha256',$pdfPath)];
}
file_put_contents($root.'/content/manifest.json',json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");
echo 'Content SHA-256: ' . $manifest['content_sha256'] . PHP_EOL;
