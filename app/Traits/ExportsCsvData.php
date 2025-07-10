<?php

namespace App\Traits;

use Illuminate\Http\RedirectResponse;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait ExportsCsvData
{
    /**
     * Export table data as CSV using the defined columns
     *
     * @return StreamedResponse|RedirectResponse the csv data or redirect back to the previous location
     */
    public function exportTableDataAsCsv(?string $filename = null): StreamedResponse|RedirectResponse
    {
        if (empty($this->tableData) || empty($this->tableColumns)) {
            session()->flash('error', 'No data available to export.');

            return back();
        }

        $filename = $filename ?? $this->getDefaultCsvFilename();

        return $this->exportAsCsv($this->tableData, $this->tableColumns, $filename);
    }

    /**
     * Generate a default filename for CSV export
     */
    protected function getDefaultCsvFilename(): string
    {
        $className = class_basename(static::class);
        $timestamp = now()->format('Y-m-d_H-i-s');

        // Convert CamelCase to kebab-case
        $filename = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $className));

        return "{$filename}-export-{$timestamp}";
    }

    /**
     * Export data as CSV with custom columns
     */
    protected function exportAsCsv(array $data, array $columns, string $filename): StreamedResponse
    {
        // Sanitize filename
        $safeFileName = $this->sanitizeFilename($filename);
        $fullFileName = $safeFileName.'.csv';

        // Define response headers
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fullFileName.'"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use ($data, $columns) {
            // Create the CSV writer
            $csv = Writer::createFromStream(fopen('php://output', 'w'));
            $csv->setDelimiter(',');
            $csv->setOutputBOM(Writer::BOM_UTF8);

            // Add headers
            $headers = array_map(fn ($column) => $column['label'], $columns);
            $csv->insertOne($headers);

            // Process each row of data
            foreach ($data as $row) {
                $csvRow = [];
                foreach ($columns as $column) {
                    // Use data_get to handle dot notation (e.g., 'laborCategory.name')
                    $value = data_get($row, $column['field'], '');

                    // Convert arrays/objects to readable strings
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value);
                    }

                    // Handle null values
                    if ($value === null) {
                        $value = '';
                    }

                    $csvRow[] = (string) $value;
                }
                $csv->insertOne($csvRow);
            }
        }, $fullFileName, $headers);
    }

    /**
     * Sanitize filename for safe download
     */
    protected function sanitizeFilename(string $filename): string
    {
        // Remove or replace invalid characters
        $safeFileName = preg_replace('/[\x00-\x1F\x7F\/:*?"<>|]/', '_', $filename);
        $safeFileName = substr($safeFileName, 0, 200);
        $safeFileName = trim($safeFileName, ' .');

        // Ensure we have a valid filename
        if (empty($safeFileName)) {
            $safeFileName = 'export_'.now()->format('Y-m-d_H-i-s');
        }

        return $safeFileName;
    }
}
