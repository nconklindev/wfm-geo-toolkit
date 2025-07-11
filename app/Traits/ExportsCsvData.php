<?php

namespace App\Traits;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use League\Csv\Writer;
use Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait ExportsCsvData
{
    /**
     * Export table data as CSV using the defined columns
     */
    public function exportTableDataAsCsv(?string $filename = null): StreamedResponse|RedirectResponse
    {
        if (empty($this->tableData) || empty($this->tableColumns)) {
            session()->flash('error', 'No data available to export.');

            return back();
        }

        $filename = $filename ?? $this->getDefaultCsvFilename();

        try {
            return $this->exportAsCsv($this->tableData, $this->tableColumns, $filename);
        } catch (Exception $e) {
            Log::error('CSV Export Error', [
                'error' => $e->getMessage(),
                'filename' => $filename,
                'data_count' => count($this->tableData),
                'columns_count' => count($this->tableColumns),
            ]);

            session()->flash('error', 'Failed to export CSV. Please try again.');

            return back();
        }
    }

    /**
     * Generate a default filename for CSV export
     */
    protected function getDefaultCsvFilename(): string
    {
        $className = class_basename(static::class);
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $className));

        return "{$filename}-export-{$timestamp}";
    }

    /**
     * Export data as CSV with custom columns
     */
    protected function exportAsCsv(array $data, array $columns, string $filename): StreamedResponse
    {
        $safeFileName = $this->sanitizeFilename($filename);
        $fullFileName = $safeFileName.'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fullFileName.'"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use ($data, $columns) {
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
                    $value = data_get($row, $column['field'], '');

                    // Convert arrays/objects to readable strings
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value);
                    }

                    // Handle null values
                    if ($value === null) {
                        $value = '';
                    }

                    // Handle boolean values for better readability in CSV
                    if (is_bool($value)) {
                        $value = $value ? 'Yes' : 'No';
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
        $safeFileName = preg_replace('/[\x00-\x1F\x7F\/:*?"<>|]/', '_', $filename);
        $safeFileName = substr($safeFileName, 0, 200);
        $safeFileName = trim($safeFileName, ' .');

        if (empty($safeFileName)) {
            $safeFileName = 'export_'.now()->format('Y-m-d_H-i-s');
        }

        return $safeFileName;
    }

    /**
     * Generate export filename with optional search term
     */
    protected function generateExportFilename(string $itemName): string
    {
        $parts = [Str::slug($itemName)];

        // Add search term if present
        if (! empty($this->search)) {
            $searchSlug = str_replace([' ', '.', '/', '\\'], '-', strtolower($this->search));
            $parts[] = "search-$searchSlug";
        }

        $parts[] = now()->format('Y-m-d_H-i-s');

        return implode('-', $parts);
    }
}
