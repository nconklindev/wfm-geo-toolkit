<?php

namespace App\Traits;

use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Log;

trait PaginatesApiData
{
    use WithPagination;

    public int $totalRecords = 0;

    #[Url(except: 15)]
    public int $perPage = 15;

    public string $search = '';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public array $tableColumns = [];

    // Cache key for storing the full dataset
    public string $cacheKey = '';

    // Add a pagination cache for filtered/sorted results
    protected string $paginationCacheKey = '';

    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->clearPaginationCache();
    }

    /**
     * Clear pagination-related cache
     */
    protected function clearPaginationCache(): void
    {
        // Clear all pagination cache entries for this component
        $baseKey = $this->generateCacheKey();

        // Get the cache store
        $cache = cache();
        $store = $cache->getStore();

        try {
            // Handle different cache drivers
            if (method_exists($store, 'getRedis') && $store->getRedis()) {
                // Redis implementation
                $this->clearRedisPaginationCache($store->getRedis(), $baseKey);
            } elseif (method_exists($store, 'getMemcached')) {
                // Memcached implementation
                $this->clearMemcachedPaginationCache($baseKey);
            } else {
                // File cache or other drivers - use key tracking
                $this->clearFilePaginationCache($cache, $baseKey);
            }

            //            Log::debug('Pagination cache cleared', [
            //                'component' => get_class($this),
            //                'base_key' => $baseKey,
            //                'cache_driver' => config('cache.default'),
            //            ]);
        } catch (Exception $e) {
            Log::warning('Failed to clear pagination cache', [
                'error' => $e->getMessage(),
                'component' => get_class($this),
                'base_key' => $baseKey,
            ]);
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
     * Clear Redis pagination cache using pattern matching
     */
    private function clearRedisPaginationCache($redis, string $baseKey): void
    {
        $patterns = [
            $baseKey.'_page_*',
            $baseKey.'_filter_*',
        ];

        foreach ($patterns as $pattern) {
            $keys = $redis->keys($pattern);
            if (! empty($keys)) {
                $redis->del($keys);
            }
        }
    }

    /**
     * Clear Memcached pagination cache using tracked keys
     */
    private function clearMemcachedPaginationCache(string $baseKey): void
    {
        // Memcached doesn't support pattern deletion, so we track keys
        $trackedKeysKey = $baseKey.'_tracked_keys';
        $trackedKeys = cache()->get($trackedKeysKey, []);

        foreach ($trackedKeys as $key) {
            cache()->forget($key);
        }

        // Clear the tracking key itself
        cache()->forget($trackedKeysKey);
    }

    /**
     * Clear file cache pagination using tracked keys
     */
    private function clearFilePaginationCache($cache, string $baseKey): void
    {
        // For file cache and other drivers without pattern support
        $trackedKeysKey = $baseKey.'_tracked_keys';
        $trackedKeys = $cache->get($trackedKeysKey, []);

        foreach ($trackedKeys as $key) {
            $cache->forget($key);
        }

        // Clear the tracking key itself
        $cache->forget($trackedKeysKey);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->clearPaginationCache();
    }

    /**
     * Execute request and reset pagination
     */
    public function executeRequest(): void
    {
        $this->resetPage();
        $this->clearPaginationCache();
        $this->executeApiCall();
    }

    /**
     * Get paginated data for rendering with enhanced caching
     */
    public function getPaginatedData(): LengthAwarePaginator
    {
        // Generate a cache key for the current pagination state
        $this->paginationCacheKey = $this->generatePaginationCacheKey();

        // Try to get the cached pagination result
        $cachedPagination = cache()->get($this->paginationCacheKey);

        if ($cachedPagination) {
            return $cachedPagination;
        }

        $allData = $this->getAllData();

        if ($allData->isEmpty()) {
            $emptyPaginator = new LengthAwarePaginator(
                collect(),
                0,
                $this->perPage,
                $this->getPage(),
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );

            // Cache empty results briefly with tracking
            $this->putCacheWithTracking($this->paginationCacheKey, $emptyPaginator, now()->addMinutes(5));

            return $emptyPaginator;
        }

        // Get filtered and sorted data
        $filteredData = $this->getFilteredAndSortedData($allData);

        // Update total records based on filtered data
        $totalFilteredRecords = $filteredData->count();

        // Calculate pagination
        $currentPage = $this->getPage();
        $offset = ($currentPage - 1) * $this->perPage;
        $currentPageItems = $filteredData->slice($offset, $this->perPage)->values();

        $paginator = new LengthAwarePaginator(
            $currentPageItems,
            $totalFilteredRecords,
            $this->perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );

        // Cache pagination result for 10 minutes with tracking
        $this->putCacheWithTracking($this->paginationCacheKey, $paginator, now()->addMinutes(10));

        return $paginator;
    }

    /**
     * Generate the cache key for pagination state
     */
    protected function generatePaginationCacheKey(): string
    {
        return $this->generateCacheKey().'_page_'.md5(
            $this->search.$this->sortField.$this->sortDirection.$this->perPage.$this->getPage()
        );
    }

    /**
     * Get the full dataset from the cache
     */
    public function getAllData(): Collection
    {
        if (empty($this->cacheKey)) {
            return collect();
        }

        return cache()->get($this->cacheKey, collect()); // FIXME
    }

    /**
     * Enhanced cache put method that tracks keys
     */
    private function putCacheWithTracking(string $key, $value, $ttl): void
    {
        cache()->put($key, $value, $ttl);

        // Track the key if we're not using Redis (which supports pattern deletion)
        $store = cache()->getStore();
        if (! method_exists($store, 'getRedis') || ! $store->getRedis()) {
            $this->trackCacheKey($key);
        }
    }

    /**
     * Track cache keys for drivers that don't support pattern deletion
     */
    private function trackCacheKey(string $key): void
    {
        $baseKey = $this->generateCacheKey();
        $trackedKeysKey = $baseKey.'_tracked_keys';

        $trackedKeys = cache()->get($trackedKeysKey, []);

        if (! in_array($key, $trackedKeys)) {
            $trackedKeys[] = $key;
            cache()->put($trackedKeysKey, $trackedKeys, now()->addHours(2));
        }
    }

    /**
     * Get filtered and sorted data collection with enhanced caching
     */
    protected function getFilteredAndSortedData(?Collection $data = null): Collection
    {
        $data = $data ?? $this->getAllData();

        // Generate cache key for filtered/sorted data
        $filterSortCacheKey = $this->generateFilterSortCacheKey();

        // Try to get cached filtered data
        $cachedFiltered = cache()->get($filterSortCacheKey);

        if ($cachedFiltered) {
            return $cachedFiltered;
        }

        $filteredData = $this->applySearchAndSort($data);

        // Cache filtered/sorted data for 15 minutes with tracking
        $this->putCacheWithTracking($filterSortCacheKey, $filteredData, now()->addMinutes(15));

        return $filteredData;
    }

    /**
     * Generate cache key for filtered/sorted data
     */
    protected function generateFilterSortCacheKey(): string
    {
        return $this->generateCacheKey().'_filter_'.md5(
            $this->search.$this->sortField.$this->sortDirection
        );
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
     */
    protected function matchesSearchTerm($item, string $searchTerm): bool
    {
        $searchableFields = $this->getSearchableFields();

        foreach ($searchableFields as $field) {
            $value = data_get($item, $field, '');

            // Handle arrays and objects by converting to string
            if (is_array($value)) {
                $value = implode(' ', array_filter($value, 'is_string'));
            } elseif (is_object($value)) {
                $value = json_encode($value);
            }

            // Ensure we have a string before calling strtolower
            $value = (string) $value;

            if (str_contains(strtolower($value), $searchTerm)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get fields that should be searched
     */
    protected function getSearchableFields(): array
    {
        return [
            'name',
            'description',
            'laborCategory.name',
        ];
    }

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
        $this->clearPaginationCache();
    }

    /**
     * Process API response data and cache it
     */
    protected function processApiResponseData($response, string $componentName = ''): void
    {
        if ($response && $response->successful()) {
            $data = $response->json();
            $records = $data['records'] ?? $data;

            // Cache the full dataset
            $this->cacheKey = $this->generateCacheKey();
            cache()->put($this->cacheKey, collect($records), now()->addMinutes(30));

            $this->totalRecords = is_array($data) && isset($data['totalRecords'])
                ? $data['totalRecords']
                : count($records);

            // Clear pagination cache when new data is loaded
            $this->clearPaginationCache();

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
     * Initialize the data collection
     */
    protected function initializePaginationData(): void
    {
        $this->totalRecords = 0;
        $this->cacheKey = '';
        $this->paginationCacheKey = '';
    }
}
