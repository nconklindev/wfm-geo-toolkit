<?php

namespace App\Traits;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use League\Csv\Bom;
use League\Csv\CannotInsertRecord;
use League\Csv\ColumnConsistency;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait HasCsvExport
{
    /**
     * Export all data to CSV - this is the main public method
     */
    public function exportToCsv(): StreamedResponse|RedirectResponse
    {
        try {
            $data = $this->getDataForCsvExport();

            if ($data->isEmpty()) {
                session()?->flash('error', 'No data available to export.');

                return back();
            }

            // Apply search and sort filters if available
            $filteredData = $this->applyFiltersForCsvExport($data);

            // Use the existing transformForCsv method from DataTransformerInterface
            $csvData = $this->transformForCsv($filteredData->toArray());

            // Get CSV-specific columns
            $csvColumns = $this->getCsvColumns();

            $filename = $this->generateCsvFilename();

            return $this->generateCsvResponse($csvData, $csvColumns, $filename);

        } catch (Exception $e) {
            session()?->flash('error', 'Failed to export data. Please try again.');

            return back();
        }
    }

    /**
     * Get the data collection for CSV export
     * Each endpoint must implement this to return the appropriate data
     */
    abstract protected function getDataForCsvExport(): Collection;

    /**
     * Apply search and sort filters to the data for CSV export
     * Components can override this method to provide custom filtering
     */
    protected function applyFiltersForCsvExport(Collection $data): Collection
    {
        // Apply search filter if search functionality is available
        if ($this->hasSearchCapability()) {
            $data = $this->applySearchFilter($data);
        }

        // Apply sort if sort functionality is available
        if ($this->hasSortCapability()) {
            $data = $this->applySortFilter($data);
        }

        return $data;
    }

    /**
     * Check if component has search capability
     */
    protected function hasSearchCapability(): bool
    {
        return property_exists($this, 'search') && ! empty($this->search);
    }

    /**
     * Apply search filter to data
     * Default implementation - components can override for custom search logic
     */
    protected function applySearchFilter(Collection $data): Collection
    {
        if (! $this->hasSearchCapability()) {
            return $data;
        }

        $searchTerm = strtolower($this->search);

        return $data->filter(function ($item) use ($searchTerm) {
            // Convert item to array if it's an object
            $itemArray = is_array($item) ? $item : (array) $item;

            // Search through all string values in the item
            foreach ($itemArray as $value) {
                if (is_string($value) && str_contains(strtolower($value), $searchTerm)) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Check if component has sort capability
     */
    protected function hasSortCapability(): bool
    {
        return property_exists($this, 'sortField') && ! empty($this->sortField);
    }

    /**
     * Apply sort filter to data
     * Default implementation - components can override for custom sort logic
     */
    protected function applySortFilter(Collection $data): Collection
    {
        if (! $this->hasSortCapability()) {
            return $data;
        }

        $direction = property_exists($this, 'sortDirection') ? $this->sortDirection : 'asc';

        return $direction === 'desc'
            ? $data->sortByDesc($this->sortField)->values()
            : $data->sortBy($this->sortField)->values();
    }

    /**
     * Get CSV column definitions
     *
     * Return an array of column definitions for CSV export. Each column should be
     * an associative array with 'field' and 'label' keys.
     *
     * Implementation steps:
     * 1. Define the fields you want to export (these should match the data keys)
     * 2. Provide user-friendly labels for each field
     * 3. Order columns as they should appear in the CSV
     *
     * Example implementation:
     * return [
     *     ['field' => 'id', 'label' => 'ID'],
     *     ['field' => 'name', 'label' => 'Name'],
     *     ['field' => 'created_at', 'label' => 'Created Date'],
     * ];
     *
     * This implementation may be similar to {@see self::getTableColumns()}
     *
     * @return array Array of column definitions with 'field' and 'label' keys
     */
    abstract protected function getCsvColumns(): array;

    /**
     * Generate default CSV filename - can be overridden by endpoints
     */
    protected function generateCsvFilename(): string
    {
        $className = class_basename(static::class);
        $readableName = $this->convertClassNameToReadable($className);

        $parts = [$readableName];

        // Add search term if present
        if ($this->hasSearchCapability()) {
            $searchSlug = str_replace([' ', '.', '/', '\\'], '-', strtolower($this->search));
            $parts[] = "search-{$searchSlug}";
        }

        // Add sort info if present
        if ($this->hasSortCapability()) {
            $sortDirection = property_exists($this, 'sortDirection') ? $this->sortDirection : 'asc';
            $parts[] = "sort-{$this->sortField}-{$sortDirection}";
        }

        // Add timestamp
        $parts[] = now()->format('Y-m-d_H-i-s');

        return implode('-', $parts);
    }

    /**
     * Convert class name to readable format
     */
    protected function convertClassNameToReadable(string $className): string
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
     * Generate the CSV response with proper headers and formatting
     */
    protected function generateCsvResponse(array $data, array $columns, string $filename): StreamedResponse
    {
        $safeFilename = $this->sanitizeFilename($filename);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$safeFilename}.csv\"",
            'Charset' => 'utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use ($data, $columns) {
            $this->writeCsvContent($data, $columns);
        }, $safeFilename.'.csv', $headers);
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
     * Write CSV content to output stream
     *
     * @throws \League\Csv\InvalidArgument
     * @throws \JsonException
     * @throws \League\Csv\CannotInsertRecord
     */
    protected function writeCsvContent(array $data, array $columns): void
    {
        $validator = new ColumnConsistency;

        $writer = Writer::createFromStream(fopen('php://output', 'wb'));
        $writer->addValidator($validator, 'column_consistency');
        $writer->setEscape('');
        $writer->setDelimiter(',');
        $writer->setOutputBOM(Bom::Utf8);

        // Add headers
        $headers = array_map(static fn ($column) => $column['label'], $columns);
        try {
            $writer->insertOne($headers);
        } catch (CannotInsertRecord|\League\Csv\Exception $e) {
            Log::error('Failed to write CSV headers', [
                'error' => $e->getMessage(),
                'component' => get_class($this),
                'data' => $data,
            ]);

            return;
        }

        // Process each row of data
        foreach ($data as $row) {
            $csvRow = [];
            foreach ($columns as $column) {
                $value = data_get($row, $column['field'], '');
                $csvRow[] = $this->formatValueForCsv($value);
            }
            try {
                $writer->insertOne($csvRow);
            } catch (CannotInsertRecord|Exception $e) {
                Log::error('Failed to write CSV row', [
                    'error' => $e->getMessage(),
                    'component' => get_class($this),
                    'data' => $row,
                ]);
                throw $e;
            }
        }
    }

    /**
     * Format individual values for CSV output
     */
    protected function formatValueForCsv($value): string
    {
        // Convert arrays/objects to readable strings
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_THROW_ON_ERROR);
        }

        // Handle null values
        if ($value === null) {
            $value = '';
        }

        // Handle boolean values
        if (is_bool($value)) {
            $value = $value ? 'Yes' : 'No';
        }

        return (string) $value;
    }
}
