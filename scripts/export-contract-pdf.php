<?php

/**
 * Export docs/EHMS_SYSTEM_CONTRACT.md to PDF using project dependencies.
 * Usage: php scripts/export-contract-pdf.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$root = dirname(__DIR__);
$mdPath = $root . '/docs/EHMS_SYSTEM_CONTRACT.md';
$pdfPath = $root . '/docs/EHMS_SYSTEM_CONTRACT.pdf';

if (!is_readable($mdPath)) {
    fwrite(STDERR, "Markdown file not found: {$mdPath}\n");
    exit(1);
}

$markdown = file_get_contents($mdPath);
$parsedown = new \Parsedown();
$body = $parsedown->text($markdown);

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EHMS System Contract</title>
    <style>
        @page { margin: 2cm 1.8cm; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5pt;
            line-height: 1.45;
            color: #222;
        }
        h1 { font-size: 18pt; border-bottom: 2px solid #333; padding-bottom: 6px; margin-top: 0; }
        h2 { font-size: 13pt; margin-top: 16px; page-break-after: avoid; }
        h3 { font-size: 11.5pt; page-break-after: avoid; }
        table { border-collapse: collapse; width: 100%; margin: 8px 0 12px; font-size: 9.5pt; }
        th, td { border: 1px solid #bbb; padding: 5px 7px; vertical-align: top; }
        th { background: #eee; font-weight: bold; }
        hr { border: none; border-top: 1px solid #ccc; margin: 16px 0; }
        pre, code { font-family: DejaVu Sans Mono, monospace; font-size: 8.5pt; }
        pre {
            background: #f5f5f5;
            padding: 10px;
            white-space: pre-wrap;
            border: 1px solid #ddd;
        }
        strong { font-weight: bold; }
        ul, ol { margin: 6px 0 10px 18px; }
        li { margin-bottom: 3px; }
        p { margin: 6px 0; }
    </style>
</head>
<body>
{$body}
</body>
</html>
HTML;

$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

file_put_contents($pdfPath, $dompdf->output());

echo "PDF created: {$pdfPath}\n";
echo 'Size: ' . number_format(filesize($pdfPath)) . " bytes\n";
