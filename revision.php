<?php
require_once 'includes/content.php';
header('Content-Type: application/json'); header('Cache-Control: no-store');
$revision=xuverse_checkout_revision();
echo json_encode(['revision'=>$revision ?: null,'content_sha256'=>xuverse_content('manifest')['content_sha256']]);
