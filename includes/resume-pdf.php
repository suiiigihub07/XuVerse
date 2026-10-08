<?php

function xuverse_resume_pdf_text($value)
{
    // Keep punctuation consistent across embedded fonts and text extraction.
    return htmlspecialchars(str_replace(["\u{2010}", "\u{2011}", "\u{2012}", "\u{2013}", "\u{2014}", "\u{2212}"], '-', (string)$value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function xuverse_resume_pdf_paragraphs($value)
{
    $paragraphs = preg_split('/\R+/', trim((string)$value));
    $html = '';
    foreach ($paragraphs as $paragraph) {
        if (trim($paragraph) !== '') {
            $html .= '<p>' . xuverse_resume_pdf_text($paragraph) . '</p>';
        }
    }
    return $html;
}

function xuverse_resume_pdf($resume)
{
    $dependencyRoot = defined('XUVERSE_STORAGE_ROOT') ? XUVERSE_STORAGE_ROOT : dirname(__DIR__);
    $autoload = $dependencyRoot . '/vendor/autoload.php';
    if (!is_file($autoload)) {
        throw new RuntimeException('Run composer install to enable the resume PDF download.');
    }
    require_once $autoload;

    $options = new \Dompdf\Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isRemoteEnabled', false);
    $options->set('isPhpEnabled', false);
    $options->set('isJavascriptEnabled', false);
    $options->set('isFontSubsettingEnabled', true);
    $options->set('chroot', [$dependencyRoot . '/vendor/dompdf/dompdf/lib/fonts']);

    ob_start();
    include __DIR__ . '/resume-pdf-template.php';
    $html = ob_get_clean();

    $pdf = new \Dompdf\Dompdf($options);
    $pdf->setPaper('A4');
    $pdf->loadHtml($html, 'UTF-8');
    $pdf->render();
    $pdf->addInfo('Title', (string)$resume['name'] . ' - Resume');
    $pdf->addInfo('Author', (string)$resume['name']);

    // Canvas footers stay in the page margin, independent of flowing body content.
    $canvas = $pdf->getCanvas();
    $font = $pdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
    $canvas->page_text(42, $canvas->get_height() - 29, 'Resume', $font, 8, [0.40, 0.40, 0.43]);
    $canvas->page_text($canvas->get_width() - 105, $canvas->get_height() - 29, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 8, [0.40, 0.40, 0.43]);

    return $pdf->output();
}
