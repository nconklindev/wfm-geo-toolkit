<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\CombinesMultipleApiCalls;
use App\Traits\ExportsCsvData;
use App\Traits\PaginatesApiData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaborCategoriesPaginatedList extends BaseApiEndpoint
{
    use CombinesMultipleApiCalls;
    use ExportsCsvData;
    use PaginatesApiData;

    #[Validate('array')]
    public array $laborCategories = [];

    #[Validate('array')]
    public array $selectedLaborCategories = [];

    public function removeCategory(string $category): void
    {
        $this->selectedLaborCategories = array_values(
            array_filter($this->selectedLaborCategories, fn ($item) => $item !== $category)
        );
        $this->resetPage();
        $this->clearPaginationCache();
        $this->loadAllData();
    }

    protected function loadAllData(): void
    {
        if (! $this->isAuthenticated) {
            $this->totalRecords = 0;
            $this->cacheKey = '';

            return;
        }

        try {
            $startTime = microtime(true);

            if (empty($this->selectedLaborCategories)) {
                // Fetch ALL data in one API call
                $this->loadAllDataFromApi();
            } else {
                // Load filtered data using multiple API calls
                $this->loadFilteredData();
            }

            $this->logPerformanceMetrics($startTime);
        } catch (ConnectionException $ce) {
            $this->errorMessage = 'Unable to connect to API. Please check your network connection and try again.';
            Log::error('Connection error in LaborCategoriesPaginatedList', [
                'error' => $ce->getMessage(),
                'selected_categories' => $this->selectedLaborCategories,
            ]);
            $this->totalRecords = 0;
            $this->cacheKey = '';
        }
    }

    protected function loadAllDataFromApi(): void
    {
        // Fetch ALL records in one API call
        $requestData = [
            'count' => 50000, // Large number to get all records
            'index' => 0,
        ];

        $response = $this->makeAuthenticatedApiCall(function () use ($requestData) {
            return $this->wfmService->getLaborCategoryEntriesPaginated($requestData);
        });

        // Use the trait's method to process and cache the response
        $this->processApiResponseData($response, 'LaborCategoriesPaginatedList');
    }

    protected function loadFilteredData(): void
    {
        $apiCallFunctions = [];

        foreach ($this->selectedLaborCategories as $category) {
            $apiCallFunctions[] = function () use ($category) {
                return $this->wfmService->getLaborCategoryEntriesPaginated([
                    'count' => 10000,
                    'index' => 0,
                    'where' => ['laborCategory' => ['qualifier' => $category]],
                ]);
            };
        }

        $combinedData = $this->makeMultipleApiCalls($apiCallFunctions, function ($item) {
            return $item['id'] ?? ($item['name'] ?? '').'_'.data_get($item, 'laborCategory.name', '');
        });

        // Cache the combined data using the trait's pattern
        $this->cacheKey = $this->generateCacheKey();
        cache()->put($this->cacheKey, $combinedData, now()->addMinutes(30));

        $this->totalRecords = $combinedData->count();

        // Clear pagination cache when new data is loaded
        $this->clearPaginationCache();

        Log::info('Combined Data Cached', [
            'component' => 'LaborCategoriesPaginatedList',
            'total_records_available' => $this->totalRecords,
            'selected_categories' => count($this->selectedLaborCategories),
            'cache_key' => $this->cacheKey,
            'memory_usage_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ]);
    }

    /**
     * Override the generateCacheKey method to include selected categories
     */
    protected function generateCacheKey(): string
    {
        $categoriesHash = empty($this->selectedLaborCategories)
            ? 'all'
            : md5(json_encode(sort($this->selectedLaborCategories)));

        return 'api_data_'.class_basename($this).'_'.md5(
            $this->hostname.'_'.session('wfm_access_token', 'anonymous').'_'.$categoriesHash
        );
    }

    private function logPerformanceMetrics(float $startTime): void
    {
        $executionTime = (microtime(true) - $startTime) * 1000;
        $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024;

        Log::info('LaborCategoriesPaginatedList Performance Metrics', [
            'execution_time_ms' => round($executionTime, 2),
            'memory_usage_mb' => round($memoryUsage, 2),
            'total_records' => $this->totalRecords,
            'selected_categories' => count($this->selectedLaborCategories),
            'data_source' => empty($this->selectedLaborCategories) ? 'api_all' : 'api_filtered',
            'cache_key' => $this->cacheKey,
        ]);
    }

    /**
     * Override executeRequest to clear raw JSON viewer
     */
    public function executeRequest(): void
    {
        // Clear raw JSON viewer when making new request
        $this->dispatch('clear-raw-json-viewer');

        $this->resetPage();
        $this->clearPaginationCache();
        $this->executeApiCall();
    }

    /**
     * Export all available data (respects current search/sort but not category filters)
     */
    public function exportAllToCsv(): StreamedResponse
    {
        // Get ALL data regardless of category selections
        $allDataForExport = $this->getAllDataForExport();

        // Apply current search and sort to the full dataset
        $filteredData = $this->applySearchAndSort($allDataForExport);

        $filename = 'labor-category-entries-all_'.now()->format('Y-m-d_H-i-s');

        return $this->exportAsCsv($filteredData->toArray(), $this->tableColumns, $filename);
    }

    /**
     * Get ALL data for export (bypassing category filters)
     */
    protected function getAllDataForExport(): Collection
    {
        if (! $this->isAuthenticated) {
            return collect();
        }

        $requestData = [
            'count' => 50000,
            'index' => 0,
        ];

        $response = $this->makeAuthenticatedApiCall(function () use ($requestData) {
            return $this->wfmService->getLaborCategoryEntriesPaginated($requestData);
        });

        if ($response && $response->successful()) {
            $data = $response->json();
            $records = $data['records'] ?? [];

            return collect($records);
        }

        return collect();
    }

    /**
     * Export only data from selected categories (respects search/sort/category filters)
     */
    public function exportSelectionsToCsv(): StreamedResponse
    {
        // Get the current filtered and sorted data (what the user is seeing)
        $exportData = $this->getFilteredAndSortedData();

        $categoryNames = empty($this->selectedLaborCategories)
            ? 'current-view'
            : implode('-', array_slice($this->selectedLaborCategories, 0, 3));

        if (count($this->selectedLaborCategories) > 3) {
            $categoryNames .= '-and-'.(count($this->selectedLaborCategories) - 3).'-more';
        }

        $filename = 'labor-category-entries-'.$categoryNames.'_'.now()->format('Y-m-d_H-i-s');

        return $this->exportAsCsv($exportData->toArray(), $this->tableColumns, $filename);
    }

    public function render(): View
    {
        $paginatedData = $this->getPaginatedData();

        return view('livewire.tools.api-explorer.endpoints.labor-categories-paginated-list', [
            'paginatedData' => $paginatedData,
        ]);
    }

    protected function initializeEndpoint(): void
    {
        // Set table columns specific to labor categories
        $this->tableColumns = [
            [
                'field' => 'name',
                'label' => 'Name',
            ],
            [
                'field' => 'description',
                'label' => 'Description',
            ],
            [
                'field' => 'inactive',
                'label' => 'Inactive',
            ],
            [
                'field' => 'laborCategory.name',
                'label' => 'Labor Category',
            ],
        ];

        // Initialize pagination data
        $this->initializePaginationData();

        $this->setupAuthenticationFromSession();

        if (! $this->isAuthenticated) {
            return;
        }

        $response = $this->makeAuthenticatedApiCall(function () {
            return $this->wfmService->getLaborCategories();
        });

        if ($response && $response->successful()) {
            $data = $response->json();
            $this->laborCategories = array_column($data, 'name');
        } elseif (! $this->isAuthenticated) {
            // Authentication was invalid, laborCategories will remain empty
            // Error message already set by the authentication handler
        } else {
            $this->errorMessage = 'Unable to load labor categories. Please check your network connection and try again.';
            Log::error('Failed to load labor categories in initializeEndpoint', [
                'component' => get_class($this),
                'hostname' => $this->hostname,
            ]);
        }
    }

    protected function makeApiCall(): object
    {
        $this->loadAllData();

        // Get the cached data to create the mock response
        $cachedData = $this->getAllData();
        $recordCount = $cachedData->count();

        return $this->createMockResponse($cachedData->toArray(), $recordCount);
    }

    /**
     * Override the searchable fields for labor categories
     */
    protected function getSearchableFields(): array
    {
        return [
            'name',
            'description',
            'laborCategory.name',
        ];
    }

    /**
     * Override to extract record count from our custom mock response structure
     */
    protected function extractRecordCount($response): int
    {
        $data = $response->json();

        // Our mock response includes a record_count field that reflects the actual cached data count
        if (isset($data['record_count']) && is_numeric($data['record_count'])) {
            return (int) $data['record_count'];
        }

        // Fallback to counting records array
        if (isset($data['records']) && is_array($data['records'])) {
            return count($data['records']);
        }

        return 0;
    }
}
