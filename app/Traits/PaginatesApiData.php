<?php

namespace App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

trait PaginatesApiData
{
    use WithPagination;

    #[Url(except: 15)]
    public int $perPage = 15;

    public string $search = '';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Get paginated data - main method called by views
     */
    public function getPaginatedData(): LengthAwarePaginator
    {
        $allData = $this->getAllData();

        if ($allData->isEmpty()) {
            return $this->createEmptyPaginator();
        }

        $filteredData = $this->applyFiltersAndSort($allData);

        return $this->createPaginator($filteredData);
    }

    /**
     * Get all data from cache or component
     */
    protected function getAllData(): Collection
    {
        // Try cache first
        if (! empty($this->cacheKey)) {
            $cached = cache()->get($this->cacheKey);
            if ($cached) {
                return $cached instanceof Collection ? $cached : collect($cached);
            }
        }

        // Fallback to component data
        return collect($this->tableData ?? []);
    }

    /**
     * Create empty paginator
     */
    protected function createEmptyPaginator(): LengthAwarePaginator
    {
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

    /**
     * Apply search and sorting filters
     */
    protected function applyFiltersAndSort(Collection $data): Collection
    {
        // Apply search filter
        if (! empty($this->search)) {
            $data = $data->filter(function ($item) {
                return $this->matchesSearch($item, strtolower($this->search));
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
     * Check if item matches search term
     */
    protected function matchesSearch($item, string $searchTerm): bool
    {
        $searchableFields = $this->getSearchableFields();

        foreach ($searchableFields as $field) {
            $value = data_get($item, $field, '');

            if (is_array($value)) {
                $value = implode(' ', array_filter($value, 'is_string'));
            } elseif (is_object($value)) {
                $value = json_encode($value);
            }

            if (str_contains(strtolower((string) $value), $searchTerm)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get searchable fields from table columns
     */
    protected function getSearchableFields(): array
    {
        return array_column($this->tableColumns ?? [], 'field');
    }

    /**
     * Handle sorting from table component
     */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            // Toggle direction if same field
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Set new field and default to ascending
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        // Reset to first page when sorting changes
        $this->resetPage();
    }

    /**
     * Create paginator for filtered data
     */
    protected function createPaginator(Collection $filteredData): LengthAwarePaginator
    {
        $total = $filteredData->count();
        $currentPage = $this->getPage();
        $offset = ($currentPage - 1) * $this->perPage;
        $items = $filteredData->slice($offset, $this->perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $this->perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Clear pagination cache - called when data changes
     */
    public function clearPaginationCache(): void
    {
        $this->resetPage();
    }

    /**
     * Initialize pagination - called by components
     */
    protected function initializePaginationData(): void
    {
        if (method_exists($this, 'generateCacheKey')) {
            $this->cacheKey = $this->generateCacheKey();
        }
    }

    /**
     * Generate cache key for pagination
     */
    protected function generateCacheKey(): string
    {
        $userId = auth()->id() ?? 'anonymous';
        $sessionId = session()->getId();

        return 'api_data_'.class_basename($this).'_'.hash('sha256', $this->hostname.'_'.$userId.'_'.$sessionId);
    }
}
