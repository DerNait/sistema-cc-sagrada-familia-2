<?php

namespace App\Trait;

trait PdfExport
{
    /**
     * Export multiple sheets (name => Collection/array) as an Excel/CSV download.
     *
     * Usage:
     *  $this->export([
     *    'Calificaciones' => collect($rows),
     *  ], 'calificaciones.csv', 'csv');
     *
     * @param array $sheets  Associative array: sheetName => iterable (Collection|array)
     * @param string $filename
     * @param string $format   'csv'|'xlsx' (defaults to 'csv')
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response
     */
    public function export(array $sheets, string $filename = 'export.csv', string $format = 'csv')
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
