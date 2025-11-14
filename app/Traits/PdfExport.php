<?php

namespace App\Traits;

use Barryvdh\DomPDF\Facade\Pdf;

trait PdfExport
{
    /**
     * Export data as PDF using DOMPDF with a Blade view.
     *
     * Usage:
     *  $this->exportToPdf('reporte-calificaciones', $data, 'calificaciones.pdf', 'a4', 'landscape');
     *
     * @param string $viewName   Name of the Blade view to render
     * @param array $data        Data to pass to the view
     * @param string $filename   Output filename (e.g., 'reporte.pdf')
     * @param string $paperSize  Paper size: 'a4', 'letter', etc. (default: 'a4')
     * @param string $orientation Orientation: 'portrait' or 'landscape' (default: 'portrait')
     * @return \Illuminate\Http\Response
     */
    public function exportToPdf(
        string $viewName,
        array $data = [],
        string $filename = 'documento.pdf',
        string $paperSize = 'a4',
        string $orientation = 'portrait'
    ) {
        // Mark that we're generating PDF (useful for conditional logic in views)
        $data['isPdf'] = true;

        // Load the view with DOMPDF
        $pdf = Pdf::loadView($viewName, $data);

        // Set paper size and orientation
        $pdf->setPaper($paperSize, $orientation);

        // Download the PDF
        return $pdf->download($filename);
    }

    /**
     * Stream PDF inline (display in browser) instead of downloading.
     *
     * @param string $viewName
     * @param array $data
     * @param string $paperSize
     * @param string $orientation
     * @return \Illuminate\Http\Response
     */
    public function streamPdf(
        string $viewName,
        array $data = [],
        string $paperSize = 'a4',
        string $orientation = 'portrait'
    ) {
        $data['isPdf'] = true;

        $pdf = Pdf::loadView($viewName, $data);
        $pdf->setPaper($paperSize, $orientation);

        return $pdf->stream();
    }

    /**
     * Export multiple sheets (name => Collection/array) as an Excel/CSV download.
     *
    * Usage:
    *  $this->exportPdf([
    *    'Calificaciones' => collect($rows),
    *  ], 'calificaciones.csv', 'csv');
     *
     * @param array $sheets  Associative array: sheetName => iterable (Collection|array)
     * @param string $filename
     * @param string $format   'csv'|'xlsx' (defaults to 'csv')
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response
     */
    public function exportPdf(array $sheets, string $filename = 'export.csv', string $format = 'csv')
    {
        // Prefer Maatwebsite\Excel if available
        if (class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            $writerType = $format === 'xlsx'
                ? \Maatwebsite\Excel\Excel::XLSX
                : \Maatwebsite\Excel\Excel::CSV;

            $collectionSheets = new \Maatwebsite\Excel\Collections\SheetCollection($sheets);

            return \Maatwebsite\Excel\Facades\Excel::download($collectionSheets, $filename, $writerType);
        }

        // Fallback: generate simple CSV from the first sheet
        $first = reset($sheets);
        $rows = is_iterable($first) ? $first : [];

        // Build CSV string
        $fh = fopen('php://memory', 'r+');
        $firstRowWritten = false;
        foreach ($rows as $r) {
            // If it's an object, cast to array
            $row = is_array($r) ? $r : (array) $r;
            if (!$firstRowWritten) {
                fputcsv($fh, array_keys($row));
                $firstRowWritten = true;
            }
            fputcsv($fh, array_values($row));
        }
        rewind($fh);
        $contents = stream_get_contents($fh);
        fclose($fh);

        return response($contents, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
