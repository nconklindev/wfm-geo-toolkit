<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\ExportsCsvData;
use App\Traits\PaginatesApiData;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdjustmentRulesList extends BaseApiEndpoint
{
    use ExportsCsvData;
    use PaginatesApiData;

    public function render()
    {
        $paginatedData = $this->getPaginatedData();

        return view('livewire.tools.api-explorer.endpoints.adjustment-rules-list', ['paginatedData' => $paginatedData]);
    }

    /**
     * Export all available data (respects current search/sort but not category filters)
     */
    public function exportAllToCsv(): StreamedResponse
    {
        $allData = $this->getAllData();

        if ($allData->isEmpty()) {
            session()->flash('error', 'No data available to export.');
        }

        // Apply current search and sort to the full dataset
        $filteredData = $this->getFilteredAndSortedData($allData);

        // Generate filename based on current state
        $filename = $this->generateExportFilename();

        return $this->exportAsCsv($filteredData->toArray(), $this->tableColumns, $filename);
    }

    protected function generateExportFilename(): string
    {
        $parts = ['adjustment-rules'];

        // Add search term if present
        if (! empty($this->search)) {
            $searchSlug = str_replace([' ', '.', '/', '\\'], '-', strtolower($this->search));
            $parts[] = "search-{$searchSlug}";
        }

        // Add sort info if not default
        if ($this->sortField !== 'name' || $this->sortDirection !== 'asc') {
            $parts[] = "sorted-by-{$this->sortField}-{$this->sortDirection}";
        }

        // Add timestamp
        $parts[] = now()->format('Y-m-d_H-i-s');

        return implode('-', $parts);
    }

    protected function getAllDataForExport(): Collection
    {
        if (! $this->isAuthenticated) {
            return collect();
        }

        $response = $this->makeAuthenticatedApiCall(function () {
            return $this->wfmService->getAdjustmentRules();
        });

        if ($response && $response->successful()) {
            $data = $response->json();
            $records = $data['records'] ?? [];

            return collect($records);
        }

        return collect();
    }

    protected function initializeEndpoint(): void
    {
        // Set table columns specific to data elements
        $this->tableColumns = [
            [
                'field' => 'name',
                'label' => 'Name',
            ],
            [
                'field' => 'ruleVersions.adjustmentRuleVersion.0.description',
                'label' => 'Description',
            ],
            [
                'field' => 'ruleVersions.adjustmentRuleVersion.0.effectiveDate',
                'label' => 'Effective Date',
            ],
            [
                'field' => 'ruleVersions.adjustmentRuleVersion.0.expirationDate',
                'label' => 'Expiration Date',
            ],
            [
                'field' => 'paycode_names',
                'label' => 'Pay Codes',
            ],
        ];

        // Initialize pagination data
        $this->initializePaginationData();
    }

    protected function makeApiCall()
    {
        if (! $this->isAuthenticated) {
            return null;
        }

        try {
            $response = $this->makeAuthenticatedApiCall(function () {
                return $this->wfmService->getAdjustmentRules();
            });

            $this->processApiResponseData($response, 'DataElementsList');

            // Clear any previous error messages on a successful call
            $this->errorMessage = '';

            return $response;
        } catch (Exception $e) {
            // Handle other types of exceptions
            $this->errorMessage = 'An unexpected error occurred. Please try again later.';

            Log::error('Unexpected error in AdjustmentRulesList', [
                'error' => $e->getMessage(),
                'type' => get_class($e),
                'stack_trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
            ]);

            $this->totalRecords = 0;

            if (! empty($this->cacheKey)) {
                cache()->forget($this->cacheKey);
            }

            return null;
        }
    }

    protected function processApiResponseData($response, string $componentName = ''): void
    {
        if ($response && $response->successful()) {
            $data = $response->json();
            $records = $data['records'] ?? $data;

            // Process each record to add custom fields
            $processedRecords = array_map(function ($item) {
                $item['paycode_names'] = $this->extractPaycodeNames($item);

                return $item;
            }, $records);

            // Cache the processed dataset
            $this->cacheKey = $this->generateCacheKey();
            cache()->put($this->cacheKey, collect($processedRecords), now()->addMinutes(30));

            $this->totalRecords = is_array($data) && isset($data['totalRecords'])
                ? $data['totalRecords']
                : count($processedRecords);

            // Clear pagination cache when new data is loaded
            $this->clearPaginationCache();

            Log::info('Data Cached', [
                'component' => $componentName ?: get_class($this),
                'total_records_available' => $this->totalRecords,
                'records_fetched' => count($processedRecords),
                'cache_key' => $this->cacheKey,
                'memory_usage_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ]);
        } else {
            $this->totalRecords = 0;
        }
    }

    private function extractPaycodeNames($item): string
    {
        $paycodeNames = [];

        // Navigate through the nested structure to get paycodes
        $ruleVersions = $item['ruleVersions']['adjustmentRuleVersion'] ?? [];

        foreach ($ruleVersions as $ruleVersion) {
            $triggers = $ruleVersion['triggers']['adjustmentTriggerForRule'] ?? [];

            foreach ($triggers as $trigger) {
                $payCodes = $trigger['payCodes'] ?? [];

                foreach ($payCodes as $payCode) {
                    if (isset($payCode['qualifier'])) {
                        $paycodeNames[] = $payCode['qualifier'];
                    }
                }
            }
        }

        // Remove duplicates and return as comma-separated string
        $uniqueNames = array_unique($paycodeNames);

        return implode(', ', $uniqueNames);
    }
}
