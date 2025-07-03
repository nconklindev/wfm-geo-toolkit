<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\ExportsCsvData;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\WithPagination;
use Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaborCategoriesPaginatedList extends BaseApiEndpoint
{
    use ExportsCsvData;
    use WithPagination;

    #[Validate('array')]
    public array $laborCategories = [];

    #[Validate('array')]
    public array $selectedLaborCategories = [];

    // Store all data as a collection
    public Collection $allData;

    public int $totalRecords = 0;

    #[Url(except: 15)]
    public int $perPage = 15;

    public string $search = '';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public array $tableColumns = [
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

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function removeCategory(string $category): void
    {
        $this->selectedLaborCategories = array_values(
            array_filter($this->selectedLaborCategories, fn ($item) => $item !== $category)
        );
        $this->resetPage();
        $this->loadAllData();
    }

    protected function loadAllData()
    {
        if (! $this->isAuthenticated) {
            $this->allData = collect();

            return;
        }

        try {
            $startTime = microtime(true);

            if (empty($this->selectedLaborCategories)) {
                // Fetch ALL data in one API call
                $this->loadAllDataFromApi();
            } else {
                // Load filtered data
                $this->loadFilteredData();
            }

            $this->logPerformanceMetrics($startTime);

        } catch (ConnectionException $ce) {
            $this->errorMessage = 'Unable to connect to API. Please check your network connection and try again.';
            Log::error('Connection error in LaborCategoriesPaginatedList', [
                'error' => $ce->getMessage(),
                'selected_categories' => $this->selectedLaborCategories,
            ]);
            $this->allData = collect();
        }
    }

    protected function loadAllDataFromApi()
    {
        // Fetch ALL records in one API call
        $requestData = [
            'count' => 50000, // Large number to get all records
            'index' => 0,
        ];

        $response = $this->makeAuthenticatedApiCall(function () use ($requestData) {
            return $this->wfmService->getLaborCategoryEntriesPaginated($requestData);
        });

        if ($response && $response->successful()) {
            $data = $response->json();
            $records = $data['records'] ?? [];

            // Convert to collection for easier manipulation
            $this->allData = collect($records);
            $this->totalRecords = $data['totalRecords'] ?? count($records);

            Log::info('All Data Loaded', [
                'total_records_available' => $this->totalRecords,
                'records_fetched' => count($records),
                'memory_usage_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ]);
        } else {
            $this->allData = collect();
            $this->totalRecords = 0;
        }
    }

    protected function loadFilteredData()
    {
        $allRecords = [];

        foreach ($this->selectedLaborCategories as $category) {
            $requestData = [
                'count' => 10000, // Large number for each category
                'index' => 0,
                'where' => [
                    'laborCategory' => [
                        'qualifier' => $category,
                    ],
                ],
            ];

            $response = $this->makeAuthenticatedApiCall(function () use ($requestData) {
                return $this->wfmService->getLaborCategoryEntriesPaginated($requestData);
            });

            if ($response && $response->successful()) {
                $data = $response->json();
                if (isset($data['records']) && is_array($data['records'])) {
                    $allRecords = array_merge($allRecords, $data['records']);
                }
            } else {
                // If any category fails due to auth, stop and let user know
                if (! $this->isAuthenticated) {
                    break;
                }
            }
        }

        // Convert to collection and remove duplicates
        $this->allData = collect($allRecords)->unique(function ($item) {
            return $item['id'] ?? ($item['name'] ?? '').'_'.data_get($item, 'laborCategory.name', '');
        });

        $this->totalRecords = $this->allData->count();
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
        ]);
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
     * Apply search and sort to a data collection
     */
    protected function applySearchAndSort(Collection $data): Collection
    {
        // Apply search filter
        if (! empty($this->search)) {
            $searchTerm = strtolower($this->search);
            $data = $data->filter(function ($item) use ($searchTerm) {
                return str_contains(strtolower(data_get($item, 'name', '')), $searchTerm) ||
                       str_contains(strtolower(data_get($item, 'description', '')), $searchTerm) ||
                       str_contains(strtolower(data_get($item, 'laborCategory.name', '')), $searchTerm);
            });
        }

        // Apply sorting
        if ($this->sortField) {
            $data = $data->sortBy(function ($item) {
                $value = data_get($item, $this->sortField, '');

                return is_string($value) ? strtolower($value) : $value;
            }, SORT_REGULAR, $this->sortDirection === 'desc');
        }

        return $data;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    /**
     * Export only data from selected categories (respects search/sort/category filters)
     */
    public function exportSelectionsToCsv(): StreamedResponse
    {
        if (empty($this->selectedLaborCategories)) {
            // If no categories selected, export current filtered view
            $exportData = $this->getFilteredAndSortedData();
        } else {
            // Export only data from selected categories
            $exportData = $this->getFilteredAndSortedData();
        }

        $categoryNames = empty($this->selectedLaborCategories)
            ? 'current-view'
            : implode('-', array_slice($this->selectedLaborCategories, 0, 3));

        if (count($this->selectedLaborCategories) > 3) {
            $categoryNames .= '-and-'.(count($this->selectedLaborCategories) - 3).'-more';
        }

        $filename = 'labor-category-entries-'.$categoryNames.'_'.now()->format('Y-m-d_H-i-s');

        return $this->exportAsCsv($exportData->toArray(), $this->tableColumns, $filename);
    }

    /**
     * Get filtered and sorted data collection (current view)
     */
    protected function getFilteredAndSortedData(): Collection
    {
        return $this->applySearchAndSort($this->allData);
    }

    public function render()
    {
        $paginatedData = $this->getPaginatedData();

        return view('livewire.tools.api-explorer.endpoints.labor-categories-paginated-list', [
            'paginatedData' => $paginatedData,
        ]);
    }

    protected function getPaginatedData()
    {
        if ($this->allData->isEmpty()) {
            return new LengthAwarePaginator(
                collect(),
                0,
                $this->perPage,
                $this->getPage(),
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );
        }

        // Get filtered and sorted data
        $filteredData = $this->getFilteredAndSortedData();

        // Update total records based on filtered data
        $totalFilteredRecords = $filteredData->count();

        // Calculate pagination
        $currentPage = $this->getPage();
        $offset = ($currentPage - 1) * $this->perPage;
        $currentPageItems = $filteredData->slice($offset, $this->perPage)->values();

        return new LengthAwarePaginator(
            $currentPageItems,
            $totalFilteredRecords,
            $this->perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    public function executeRequest(): void
    {
        $this->resetPage();
        $this->executeApiCall();
    }

    protected function initializeEndpoint(): void
    {
        // Initialize the collection first
        $this->allData = collect();

        $this->setupAuthenticationFromSession();

        if (! $this->isAuthenticated) {
            return;
        }

        // Use the new authenticated API call method
        $response = $this->makeAuthenticatedApiCall(function () {
            return $this->wfmService->getLaborCategories();
        });

        if ($response && $response->successful()) {
            $data = $response->json();
            $this->laborCategories = array_column($data, 'name');
        } elseif (! $this->isAuthenticated) {
            // Authentication was invalid, laborCategories will remain empty
            // Error message already set by authentication handler
        } else {
            $this->errorMessage = 'Unable to load labor categories. Please check your network connection and try again.';
            Log::error('Failed to load labor categories in initializeEndpoint', [
                'component' => get_class($this),
                'hostname' => $this->hostname,
            ]);
        }
    }

    protected function makeApiCall()
    {
        $this->loadAllData();

        return $this->createMockResponse();
    }

    private function createMockResponse()
    {
        // Return a mock response for the parent class
        return new class($this->allData->toArray())
        {
            private $data;

            public function __construct($data)
            {
                $this->data = ['records' => $data];
            }

            public function successful(): bool
            {
                return true;
            }

            public function status(): int
            {
                return 200;
            }

            public function json()
            {
                return $this->data;
            }
        };
    }

    protected function processApiResponse($response): void
    {
        parent::processApiResponse($response);
        // Data is already processed in loadAllData()
    }
}
