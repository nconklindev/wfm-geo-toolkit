<?php

namespace App\Traits;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use League\Csv\Bom;
use League\Csv\Writer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait ExportsCsvData
{
    /**
     * Export all available data as CSV (prioritizing cached data)
     */
    public function exportAllToCsv(): StreamedResponse|RedirectResponse
    {
        return $this->performExport('all', function () {
            // Strategy 1: Use currently loaded table data
            if (! empty($this->tableData)) {
                return collect($this->tableData);
            }

            // Strategy 2: Try to get data from the cache using the cache key
            if (! empty($this->cacheKey)) {
                $cachedData = cache()->get($this->cacheKey);
                if ($cachedData) {
                    return $cachedData instanceof Collection ? $cachedData : collect($cachedData);
                }
            }

            // Strategy 3: Try getAllData() method if available (usually returns cached data)
            if (method_exists($this, 'getAllData')) {
                $data = $this->getAllData();
                if (! $data->isEmpty()) {
                    return $data;
                }
            }

            // Strategy 4: Use custom getAllDataForExport if exists
            if (method_exists($this, 'getAllDataForExport')) {
                return $this->getAllDataForExport();
            }

            // Strategy 5: Use fetchData() pattern (this may trigger validation)
            if (method_exists($this, 'fetchData')) {
                try {
                    $response = $this->fetchData();
                    if ($response && $response->successful()) {
                        return collect($this->extractDataFromResponse($response));
                    }
                } catch (Exception $e) {
                    Log::warning('fetchData() failed during export', [
                        'error' => $e->getMessage(),
                        'component' => get_class($this),
                    ]);
                }
            }

            return collect();
        });
    }

    /**
     * Perform export with consolidated logic
     */
    private function performExport(string $exportType, callable $dataProvider): StreamedResponse|RedirectResponse
    {
        try {
            $data = $dataProvider();

            if ($data->isEmpty()) {
                session()?->flash('error', 'No data available to export.');

                return back();
            }

            // Apply filters and sorting if the method exists
            if (method_exists($this, 'applyFiltersAndSort')) {
                $data = $this->applyFiltersAndSort($data);
            }

            $filename = $this->getExportFilename($exportType);

            return $this->generateCsv($data->toArray(), $this->tableColumns, $filename);

        } catch (Exception $e) {
            Log::error("CSV Export Error - $exportType", [
                'error' => $e->getMessage(),
                'component' => get_class($this),
            ]);

            session()?->flash('error', 'Failed to export data. Please try again.');

            return back();
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            Log::error("CSV Export Error - $exportType", [
                'error' => $e->getMessage(),
                'component' => get_class($this),
                'exception' => $e->getTraceAsString(),
            ]);
            session()?->flash('error', 'Failed to export data. Please try again.');

            return back();
        }
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

        // Otherwise, generate smart default based on the class name
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
            $parts[] = "search-$searchSlug";
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
    protected function generateCsv(array $data, array $columns, string $filename): StreamedResponse
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
            $writer = Writer::createFromStream(fopen('php://output', 'wb'));
            $writer->setEscape('');
            $writer->setDelimiter(',');
            $writer->setOutputBOM(Bom::Utf8);

            // Add headers
            $headers = array_map(static fn ($column) => $column['label'], $columns);
            $writer->insertOne($headers);

            // Process each row of data
            foreach ($data as $row) {
                $csvRow = [];
                foreach ($columns as $column) {
                    $value = data_get($row, $column['field'], '');

                    // Convert arrays/objects to readable strings
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value, JSON_THROW_ON_ERROR);
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
                $writer->insertOne($csvRow);
            }
        }, $fullFileName, $headers);
    }

    /**
     * Sanitize the filename for safe download
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
     * Export current filtered/searched data as CSV (what the user sees)
     */
    public function exportSelectionsToCsv(): StreamedResponse|RedirectResponse
    {
        return $this->performExport('selections', function () {
            if (method_exists($this, 'getAllData')) {
                return $this->getAllData();
            }

            // Fallback to table data
            return collect($this->tableData ?? []);
        });
    }

    /**
     * Generate a default filename for CSV export
     */
    protected function getDefaultCsvFilename(): string
    {
        $className = class_basename(static::class);
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $className));

        return "$filename-export-$timestamp";
    }
}
