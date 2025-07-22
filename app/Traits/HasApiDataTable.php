<?php

namespace App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;

trait HasApiDataTable
{
    // Table interaction properties
    public string $search = '';

    public string $sortField = '';

    public string $sortDirection = 'asc';

    public array $tableColumns = [];

    protected ?LengthAwarePaginator $paginatedData = null;

    public function updatedSearch(): void
    {
        $this->createPaginatedData();
    }

    // Use computed property to get paginated data

    protected function createPaginatedData(): void
    {
        Log::info('HasApiDataTable: createPaginatedData started', [
            'component' => get_class($this),
            'data_empty' => empty($this->data),
            'data_count' => is_countable($this->data) ? count($this->data) : 'not countable',
            'data_type' => gettype($this->data),
        ]);

        if (empty($this->data)) {
            $this->paginatedData = null;
            Log::info('HasApiDataTable: Data is empty, setting paginatedData to null');

            return;
        }

        // Transform data for view
        Log::info('HasApiDataTable: About to transform data for view');
        $transformedData = $this->transformForView($this->data);

        Log::info('HasApiDataTable: Data transformed', [
            'transformed_count' => count($transformedData),
            'transformed_type' => gettype($transformedData),
            'first_transformed_item' => ! empty($transformedData) ? $transformedData[0] : 'no items',
        ]);

        $collection = collect($transformedData);

        // Apply search filter
        if (! empty($this->search)) {
            $collection = $collection->filter(function ($item) {
                return collect($item)->contains(function ($value) {
                    // Handle array values by converting to JSON or joining
                    if (is_array($value)) {
                        $searchableValue = json_encode($value);
                    } elseif (is_object($value)) {
                        $searchableValue = json_encode($value);
                    } else {
                        $searchableValue = (string) $value;
                    }

                    return stripos((string) $searchableValue, $this->search) !== false;
                });
            });
        }

        // Apply sorting
        if (! empty($this->sortField)) {
            $collection = $this->sortDirection === 'desc'
                ? $collection->sortByDesc($this->sortField)
                : $collection->sortBy($this->sortField);
        }

        // Create paginated data
        $currentPage = $this->getCurrentPage();
        $perPage = $this->getPerPage();
        $total = $collection->count();

        Log::info('HasApiDataTable: Pagination info', [
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'total' => $total,
        ]);

        $items = $collection->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $this->paginatedData = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );

        // Update total for display
        $this->totalRecords = $total;

        Log::info('HasApiDataTable: createPaginatedData completed', [
            'totalRecords' => $this->totalRecords,
            'paginated_items_count' => $items->count(),
        ]);
    }

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->createPaginatedData();
    }

    // Table interaction methods

    #[Computed]
    public function getPaginatedData(): ?LengthAwarePaginator
    {
        return $this->paginatedData;
    }

    // Called whenever the property `perPage` is updated
    // This is updated through the "Show" dropdown in each data table
    public function updatedPerPage(): void
    {
        $this->createPaginatedData();
        $this->resetPage(); // Reset the page to prevent no data showing
    }

    protected function initializeTableData(): void
    {
        $this->tableColumns = $this->getTableColumns();
        $this->createPaginatedData();
    }

    // Abstract method that concrete classes must implement

    /**
     * Get table column definitions
     *
     * Return an array of column definitions for the data table display.
     * Each column should be an associative array with 'field' and 'label' keys.
     * The 'field' key should match the data keys from transformForView().
     *
     *
     * @return array Array of column definitions with 'field' and 'label' keys
     */
    abstract protected function getTableColumns(): array;
}
