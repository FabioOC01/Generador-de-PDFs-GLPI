<?php

declare(strict_types=1);

namespace GlpiPlugin\Vacationpdf;

use Glpi\Application\View\TemplateRenderer;
use RuntimeException;
use TCPDF;
use Throwable;

/**
 * Renders a Twig template and turns it into a PDF file on disk.
 */
final class PdfBuilder
{
    /**
     * @param array<string,mixed> $variables
     * @return string absolute path of the produced PDF
     */
    public function render(PdfType $type, array $variables, string $filename): string
    {
        $html = TemplateRenderer::getInstance()->render($type->template(), $variables);
        $path = rtrim(GLPI_TMP_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        $this->ensureTcpdfLoaded();

        try {
            $pdf = new TCPDF();
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetCreator('GLPI VacationPDF');
            $pdf->SetAuthor('GLPI');
            $pdf->SetTitle($type->value);
            $pdf->SetMargins(14, 8, 14);
            $pdf->SetAutoPageBreak(true, 10);
            $pdf->AddPage();
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Output($path, 'F');
        } catch (Throwable $e) {
            throw new RuntimeException('TCPDF rendering failed: ' . $e->getMessage(), 0, $e);
        }

        if (!is_file($path)) {
            throw new RuntimeException("PDF not written at {$path}");
        }

        return $path;
    }

    private function ensureTcpdfLoaded(): void
    {
        if (class_exists(TCPDF::class)) {
            return;
        }
        $tcpdf = GLPI_ROOT . '/vendor/tecnickcom/tcpdf/tcpdf.php';
        if (!is_file($tcpdf)) {
            throw new RuntimeException('TCPDF library not found at ' . $tcpdf);
        }
        require_once $tcpdf;
    }
}
