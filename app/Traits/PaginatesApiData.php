<?php

namespace App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Log;

trait PaginatesApiData
{
    use WithPagination;

    // Don't store all data in component state - use caching instead
    public int $totalRecords = 0;

    #[Url(except: 15)]
    public int $perPage = 15;

    public string $search = '';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public array $tableColumns = [];

    // Cache key for storing the full dataset
    public string $cacheKey = '';

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * Execute request and reset pagination
     */
    public function executeRequest(): void
    {
        $this->resetPage();
        $this->executeApiCall();
    }

    /**
     * Get paginated data for rendering
     */
    public function getPaginatedData(): LengthAwarePaginator
    {
        $allData = $this->getAllData();

        if ($allData->isEmpty()) {
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
        $filteredData = $this->getFilteredAndSortedData($allData);

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

    /**
     * Get the full dataset from cache
     */
    public function getAllData(): Collection
    {
        if (empty($this->cacheKey)) {
            return collect();
        }

        return cache()->get($this->cacheKey, collect());
    }

    /**
     * Get filtered and sorted data collection (current view)
     */
    protected function getFilteredAndSortedData(?Collection $data = null): Collection
    {
        $data = $data ?? $this->getAllData();

        return $this->applySearchAndSort($data);
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
                return $this->matchesSearchTerm($item, $searchTerm);
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

    /**
     * Check if an item matches the search term
     * Override in child classes for custom search logic
     */
    protected function matchesSearchTerm($item, string $searchTerm): bool
    {
        // Default search implementation - searches common fields
        $searchableFields = $this->getSearchableFields();

        foreach ($searchableFields as $field) {
            $value = data_get($item, $field, '');
            if (str_contains(strtolower($value), $searchTerm)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get fields that should be searched
     * Override in child classes to define custom searchable fields
     */
    protected function getSearchableFields(): array
    {
        return [
            'name',
            'description',
            'laborCategory.name',
        ];
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
     * Process API response data and cache it instead of storing in component state
     */
    protected function processApiResponseData($response, string $componentName = ''): void
    {
        if ($response && $response->successful()) {
            $data = $response->json();
            $records = $data['records'] ?? $data; // Handle both wrapped and unwrapped responses

            // Cache the full dataset instead of storing in component state
            $this->cacheKey = $this->generateCacheKey();
            cache()->put($this->cacheKey, collect($records), now()->addMinutes(30));

            $this->totalRecords = is_array($data) && isset($data['totalRecords'])
                ? $data['totalRecords']
                : count($records);

            Log::info('Data Cached', [
                'component' => $componentName ?: get_class($this),
                'total_records_available' => $this->totalRecords,
                'records_fetched' => count($records),
                'cache_key' => $this->cacheKey,
                'memory_usage_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ]);
        } else {
            $this->totalRecords = 0;
        }
    }

    /**
     * Generate a unique cache key for this component instance
     */
    protected function generateCacheKey(): string
    {
        return 'api_data_'.class_basename($this).'_'.md5(
            $this->hostname.'_'.session('wfm_access_token', 'anonymous')
        );
    }

    /**
     * Initialize the data collection
     * Call this in the child class's initializeEndpoint method
     */
    protected function initializePaginationData(): void
    {
        $this->totalRecords = 0;
        $this->cacheKey = '';
    }
}
