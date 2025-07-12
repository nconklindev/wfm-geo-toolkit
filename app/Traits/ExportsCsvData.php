<?php

namespace App\Traits;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait ExportsCsvData
{
    /**
     * Export all available data as CSV (fresh from API)
     */
    public function exportAllToCsv(): StreamedResponse|RedirectResponse
    {
        try {
            $allData = $this->getAllDataForAllExport();

            if (empty($allData)) {
                session()->flash('error', 'No data available to export.');

                return back();
            }

            $filteredData = $this->applyFiltersAndSort(collect($allData));

            // Use custom filename if endpoint provides one, otherwise use smart default
            $filename = $this->getExportFilename('all');

            return $this->exportAsCsv($filteredData->toArray(), $this->tableColumns, $filename);
        } catch (Exception $e) {
            Log::error('CSV Export Error - All Data', [
                'error' => $e->getMessage(),
                'component' => get_class($this),
            ]);

            session()->flash('error', 'Failed to export data. Please try again.');

            return back();
        }
    }

    protected function getAllDataForAllExport(): array
    {
        // Strategy 1: Use custom getAllDataForExport if exists
        if (method_exists($this, 'getAllDataForExport')) {
            return $this->getAllDataForExport()->toArray();
        }

        // Strategy 2: Use fetchData() pattern
        if (method_exists($this, 'fetchData')) {
            $response = $this->fetchData();

            if ($response && $response->successful()) {
                return $this->extractDataFromResponse($response);
            }
        }

        // Strategy 3: Use current loaded data
        return $this->getAllData()->toArray();
    }

    /**
     * Get export filename - allows endpoints to customize while providing smart defaults
     */
    protected function getExportFilename(string $type): string
    {
        // If endpoint has custom generateExportFilename method, use it
        if (method_exists($this, 'generateExportFilename')) {
            return $this->generateExportFilename($type);
        }

        // Otherwise, generate smart default based on class name
        return $this->generateSmartDefaultFilename($type);
    }

    /**
     * Generate smart default filename based on class name
     */
    protected function generateSmartDefaultFilename(string $type): string
    {
        $className = class_basename(static::class);

        // Convert class name to readable format
        $readableName = $this->convertClassNameToReadable($className);

        $parts = [$readableName, $type];

        // Add search term if present
        if (! empty($this->search)) {
            $searchSlug = str_replace([' ', '.', '/', '\\'], '-', strtolower($this->search));
            $parts[] = "search-{$searchSlug}";
        }

        // Add timestamp
        $parts[] = now()->format('Y-m-d_H-i-s');

        return implode('-', $parts);
    }

    /**
     * Convert class name to readable format
     */
    private function convertClassNameToReadable(string $className): string
    {
        // Handle common patterns
        $replacements = [
            'RetrieveAll' => '',
            'Retrieve' => '',
            'List' => '',
            'PaginatedList' => '',
        ];

        $cleaned = str_replace(array_keys($replacements), array_values($replacements), $className);

        // Convert CamelCase to kebab-case
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $cleaned));
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
     * Export current filtered/searched data as CSV (what user sees)
     */
    public function exportSelectionsToCsv(): StreamedResponse|RedirectResponse
    {
        try {
            $exportData = $this->getAllData();
            $filteredData = $this->applyFiltersAndSort($exportData);

            // Use custom filename if endpoint provides one, otherwise use smart default
            $filename = $this->getExportFilename('selections');

            return $this->exportAsCsv($filteredData->toArray(), $this->tableColumns, $filename);
        } catch (Exception $e) {
            Log::error('CSV Export Error - Selections', [
                'error' => $e->getMessage(),
                'component' => get_class($this),
            ]);

            session()->flash('error', 'Failed to export CSV. Please try again.');

            return back();
        }
    }

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
}
