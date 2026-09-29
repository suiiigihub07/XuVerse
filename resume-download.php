<?php

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/resume-data.php';
require_once __DIR__ . '/includes/resume-pdf.php';

header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff');
header('X-Robots-Tag: noindex, noarchive');
header('Cache-Control: private, no-store');

if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
    header('Allow: GET, HEAD');
    http_response_code(405);
    exit;
}

try {
    $resume = xuverse_resume_data($conn);
    $pdf = xuverse_resume_pdf($resume);
    $safeName = trim(preg_replace('/[^A-Za-z0-9]+/', '-', $resume['name']), '-') ?: 'XuVerse';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $safeName . '-Resume.pdf"');
    header('Content-Length: ' . strlen($pdf));
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
        echo $pdf;
    }
} catch (Throwable $error) {
    error_log('Resume PDF generation failed: ' . $error->getMessage());
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'The PDF is temporarily unavailable. Please use the Print button on the resume page or try again shortly.';
}
