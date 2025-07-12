<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\CombinesMultipleApiCalls;
use App\Traits\ExportsCsvData;
use App\Traits\PaginatesApiData;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use stdClass;

class RetrieveAllPersons extends BaseApiEndpoint
{
    use CombinesMultipleApiCalls;
    use ExportsCsvData;
    use PaginatesApiData;

    public function render(): View
    {
        $paginatedData = $this->getPaginatedData();

        return view('livewire.tools.api-explorer.endpoints.retrieve-all-persons', [
            'paginatedData' => $paginatedData,
        ]);
    }

    /**
     * Override executeRequest to handle pagination and clear raw JSON viewer
     */
    public function executeRequest(): void
    {
        // Clear raw JSON viewer when making new request
        $this->dispatch('clear-raw-json-viewer');

        if (! $this->isAuthenticated) {
            $this->errorMessage = 'Please authenticate first using the credentials form above.';

            return;
        }

        $this->isLoading = true;
        $this->errorMessage = null;

        try {
            // Clear any existing cache
            $this->clearCache();

            // Load all data from API
            $this->loadAllData();

            // Create a user-friendly API response
            $this->apiResponse = [
                'status' => 200,
                'data' => [
                    'message' => "Data loaded successfully - $this->totalRecords records",
                    'record_count' => $this->totalRecords,
                    'click_to_view' => 'Click "Show Raw JSON" to view full response',
                    'cached' => false,
                ],
            ];

            // Set a raw JSON cache key
            $this->rawJsonCacheKey = $this->cacheKey;

            // Trigger pagination refresh
            $this->clearPaginationCache();

        } catch (Exception $e) {
            $this->handleError($e);
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Load all data from API
     *
     * @throws ConnectionException
     */
    protected function loadAllData(): void
    {
        if (! $this->isAuthenticated) {
            $this->totalRecords = 0;
            $this->cacheKey = '';

            return;
        }

        try {
            $startTime = microtime(true);
            $this->loadAllDataFromApi();
            $this->logPerformanceMetrics($startTime);
        } catch (ConnectionException $ce) {
            $this->errorMessage = 'Unable to connect to API. Please check your network connection and try again.';
            Log::error('Connection error in RetrieveAllPersons', [
                'error' => $ce->getMessage(),
            ]);
            $this->totalRecords = 0;
            $this->cacheKey = '';
            throw $ce;
        }
    }

    /**
     * Load all data from API using pagination
     */
    protected function loadAllDataFromApi(): void
    {
        $batchSize = 500;
        $pageIndex = 0; // Start with page 0
        $allRecords = collect();
        $hasMoreData = true;
        $requestCount = 0;

        while ($hasMoreData) {
            $requestCount++;

            $requestData = [
                'where' => new stdClass,
                'index' => $pageIndex,
                'count' => $batchSize,
            ];

            Log::info('Making API request', [
                'request_number' => $requestCount,
                'page_index' => $pageIndex,
                'batch_size' => $batchSize,
                'total_records_so_far' => $allRecords->count(),
            ]);

            $response = $this->makeAuthenticatedApiCall(function () use ($requestData) {
                return $this->wfmService->getAllPersonsPaginated($requestData);
            });

            if (! $response || ! $response->successful()) {
                $this->errorMessage = 'Failed to load data from API. Please try again.';
                Log::error('API call failed in loadAllDataFromApi', [
                    'status' => $response ? $response->status() : 'no_response',
                    'requested_count' => $requestData['count'],
                    'current_page' => $pageIndex,
                    'records_so_far' => $allRecords->count(),
                    'hostname' => $this->hostname,
                ]);
                break;
            }

            $data = $response->json();
            $records = collect($data['records'] ?? []);

            Log::info('API response received', [
                'request_number' => $requestCount,
                'page_index' => $pageIndex,
                'batch_size' => $batchSize,
                'actual_count' => $records->count(),
                'total_so_far' => $allRecords->count(),
            ]);

            // If we got no records, we're done
            if ($records->isEmpty()) {
                Log::info('No more records found - stopping pagination', [
                    'final_total' => $allRecords->count(),
                    'final_page' => $pageIndex,
                ]);
                $hasMoreData = false;
                break;
            }

            // Add records to collection
            $transformedRecords = $this->transformApiData($records->toArray());
            $allRecords = $allRecords->concat($transformedRecords->toArray());

            Log::info('Records added to collection', [
                'records_added' => $records->count(),
                'total_records_now' => $allRecords->count(),
            ]);

            // If we got fewer records than requested, we've reached the end
            if ($records->count() < $batchSize) {
                Log::info('Received fewer records than requested - reached end', [
                    'requested' => $batchSize,
                    'received' => $records->count(),
                    'total_records' => $allRecords->count(),
                    'final_page' => $pageIndex,
                ]);
                $hasMoreData = false;
                break;
            }

            // Move to the next page
            $pageIndex++;

            // Safety check to prevent infinite loops
            if ($requestCount > 50) {
                Log::warning('Too many API requests, stopping to prevent infinite loop', [
                    'request_count' => $requestCount,
                    'total_records' => $allRecords->count(),
                    'final_page' => $pageIndex,
                ]);
                break;
            }

            Log::info('Continuing to next page', [
                'request_number' => $requestCount,
                'next_page_index' => $pageIndex,
                'total_records_so_far' => $allRecords->count(),
            ]);
        }

        // Store data in base class properties AND cache
        $this->storeDataInComponent($allRecords);

        Log::info('All Data Loaded and Cached', [
            'component' => 'RetrieveAllPersons',
            'total_records' => $this->totalRecords,
            'total_requests' => $requestCount,
            'final_page' => $pageIndex,
            'batch_size' => $batchSize,
            'cache_key' => $this->cacheKey,
        ]);
    }

    /**
     * Transform API data - convert boolean fields and clean up data structure
     */
    protected function transformApiData(array $data): Collection
    {
        // Don't transform boolean fields here - let the view handle the display
        // The view template expects actual boolean values to show icons and colors
        return collect($data);
    }

    /**
     * Store data in both component properties and cache
     */
    protected function storeDataInComponent(Collection $data): void
    {
        // Convert the collection to array for storage
        $dataArray = $data->toArray();

        // Store in base class properties (required for pagination trait)
        $this->tableData = $dataArray;
        $this->totalRecords = count($dataArray);

        // Generate the cache key and store in cache
        $this->cacheKey = $this->generateCacheKey();
        cache()->put($this->cacheKey, $data, now()->addMinutes(30));

        // Clear pagination cache when new data is loaded
        $this->clearPaginationCache();
    }

    /**
     * Log performance metrics
     */
    private function logPerformanceMetrics(float $startTime): void
    {
        $executionTime = (microtime(true) - $startTime) * 1000;
        $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024;

        Log::info('RetrieveAllPersons Performance Metrics', [
            'execution_time_ms' => round($executionTime, 2),
            'memory_usage_mb' => round($memoryUsage, 2),
            'total_records' => $this->totalRecords,
            'cache_key' => $this->cacheKey,
        ]);
    }

    /**
     * Initialize endpoint configuration
     */
    protected function initializeEndpoint(): void
    {
        // Configure the table structure - adjust these based on the actual person data structure
        $this->tableColumns = [
            ['field' => 'personId', 'label' => 'Database ID'],
            ['field' => 'personNumber', 'label' => 'Employee ID'],
            ['field' => 'firstName', 'label' => 'First Name'],
            ['field' => 'lastName', 'label' => 'Last Name'],
            ['field' => 'employmentStatus', 'label' => 'Employment Status'],
            ['field' => 'userAccountStatus', 'label' => 'User Account Status'],
        ];

        // Initialize pagination with the cache key
        $this->initializePaginationData();
    }

    protected function fetchData(): ?Response
    {
        return null;
    }

    /**
     * Get all data for export (fetches fresh data from API)
     */
    // TODO: Maybe we should just load from cache instead of requesting again?
    protected function getAllDataForExport(): Collection
    {
        if (! $this->isAuthenticated) {
            return collect();
        }

        $batchSize = 500;
        $pageIndex = 0; // Start with page 0
        $allRecords = collect();
        $hasMoreData = true;

        while ($hasMoreData) {
            $requestData = [
                'where' => new stdClass,
                'index' => $pageIndex, // This is the page number
                'count' => $batchSize,
            ];

            $response = $this->makeAuthenticatedApiCall(function () use ($requestData) {
                return $this->wfmService->getAllPersonsPaginated($requestData);
            });

            if (! $response || ! $response->successful()) {
                break;
            }

            $data = $response->json();
            $records = collect($data['records'] ?? []);

            if ($records->isEmpty()) {
                $hasMoreData = false;
            } else {
                // Transform data for export too
                $transformedRecords = $this->transformApiData($records->toArray());
                $allRecords = $allRecords->concat($transformedRecords->toArray());

                // Move to the next page
                $pageIndex++;

                // If the count is less than the batch size, then we've reached the end
                if ($records->count() < $batchSize) {
                    $hasMoreData = false;
                }
            }
        }

        return $allRecords;
    }

    /**
     * Override the searchable fields for persons
     */
    protected function getSearchableFields(): array
    {
        return [
            'firstName',
            'lastName',
            'employeeId',
            'databaseId',
        ];
    }
}
