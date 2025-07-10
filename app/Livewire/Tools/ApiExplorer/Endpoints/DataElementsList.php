<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\ExportsCsvData;
use App\Traits\PaginatesApiData;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataElementsList extends BaseApiEndpoint
{
    use ExportsCsvData;
    use PaginatesApiData;

    public function render(): View
    {
        $paginatedData = $this->getPaginatedData();

        return view('livewire.tools.api-explorer.endpoints.data-elements-list', [
            'paginatedData' => $paginatedData,
        ]);
    }

    /**
     * Export all data elements to CSV (respects current search/sort but gets all data)
     */
    public function exportAllToCsv(): StreamedResponse|RedirectResponse
    {
        // Get all data (not paginated)
        $allData = $this->getAllData();

        if ($allData->isEmpty()) {
            session()->flash('error', 'No data available to export.');

            return back();
        }

        // Apply current search and sort to the full dataset
        $filteredData = $this->getFilteredAndSortedData($allData);

        // Generate filename based on current state
        $filename = $this->generateExportFilename();

        return $this->exportAsCsv($filteredData->toArray(), $this->tableColumns, $filename);
    }

    /**
     * Generate a descriptive filename for the export
     */
    protected function generateExportFilename(): string
    {
        $parts = ['data-elements'];

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

    /**
     * Export current filtered/searched selections to CSV
     */
    public function exportSelectionsToCsv(): StreamedResponse|RedirectResponse
    {
        // Get the current filtered and sorted data (what the user is seeing)
        $exportData = $this->getFilteredAndSortedData();

        if ($exportData->isEmpty()) {
            session()->flash('error', 'No data available to export.');

            return back();
        }

        // Generate filename based on the current view
        $filename = $this->generateExportFilename();

        return $this->exportAsCsv($exportData->toArray(), $this->tableColumns, $filename);
    }

    /**
     * Override the searchable fields for data elements
     */
    protected function getSearchableFields(): array
    {
        return [
            'key',
            'dataProvider',
            'categories',
            'metadata.dataType',
            'metadata.entity',
        ];
    }

    protected function initializeEndpoint(): void
    {
        // Set table columns specific to data elements
        $this->tableColumns = [
            [
                'field' => 'key',
                'label' => 'Key',
            ],
            [
                'field' => 'label',
                'label' => 'Label',
            ],
            [
                'field' => 'dataProvider',
                'label' => 'Data Provider',
            ],
            [
                'field' => 'metadata.dataType',
                'label' => 'Data Type',
            ],
            [
                'field' => 'metadata.entity',
                'label' => 'Entity Name',
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
                return $this->wfmService->getDataElementsPaginated();
            });

            $this->processApiResponseData($response, 'DataElementsList');

            // Clear any previous error messages on a successful call
            $this->errorMessage = '';

            return $response;
        } catch (Exception $e) {
            // Handle other types of exceptions
            $this->errorMessage = 'An unexpected error occurred. Please try again later.';

            Log::error('Unexpected error in DataElementsList', [
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
}
