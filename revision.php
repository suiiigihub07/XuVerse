<?php
require_once 'includes/content.php';
header('Content-Type: application/json'); header('Cache-Control: no-store');
$revision=trim((string)@file_get_contents(__DIR__.'/.xuverse-local-revision'));
echo json_encode(['revision'=>$revision ?: null,'content_sha256'=>xuverse_content('manifest')['content_sha256']]);
