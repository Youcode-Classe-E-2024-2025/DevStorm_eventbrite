<?php
namespace App\traits;

use TCPDF;

trait PDFGenerator
{
    public function generatePDF($title, $header, $data, $filename)
    {
        $pdf = new TCPDF();
        $pdf->SetCreator('DevEvents');
        $pdf->SetTitle($title);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        // Title
        $pdf->Cell(0, 10, $title, 0, 1, 'C');
        $pdf->Ln(10);

        // Headers
        $pdf->SetFillColor(200, 200, 200);
        foreach ($header as $col) {
            $pdf->Cell(50, 7, $col, 1, 0, 'C', true);
        }
        $pdf->Ln();

        // Data
        foreach ($data as $row) {
            foreach ($row as $col) {
                $pdf->Cell(50, 7, $col, 1);
            }
            $pdf->Ln();
        }

        // Output PDF
        $pdf->Output($filename . '.pdf', 'D');
        exit();
    }
}
