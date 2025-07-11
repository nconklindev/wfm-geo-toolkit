<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\ExportsCsvData;
use App\Traits\PaginatesApiData;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
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
     * Export all data elements to CSV
     */
    public function exportAllToCsv(): StreamedResponse|RedirectResponse
    {
        try {
            $response = $this->fetchData();

            if (! $response || ! $response->successful()) {
                session()->flash('error', 'Failed to fetch data for export.');

                return back();
            }

            $allData = $this->extractDataFromResponse($response);

            if (empty($allData)) {
                session()->flash('error', 'No data available to export.');

                return back();
            }

            // Apply current search and sort to the full dataset
            $filteredData = $this->applyFiltersAndSort(collect($allData));

            $filename = $this->generateExportFilename('data-elements-all');

            return $this->exportAsCsv($filteredData->toArray(), $this->tableColumns, $filename);
        } catch (Exception $e) {
            Log::error('Error exporting all data elements data', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            session()->flash('error', 'Failed to export data. Please try again.');

            return back();
        }
    }

    /**
     * Fetch data from WFM API
     */
    protected function fetchData(): ?Response
    {
        return $this->makeAuthenticatedApiCall(function () {
            return $this->wfmService->getDataElementsPaginated();
        });
    }

    /**
     * Generate a descriptive filename for the export
     */
    protected function generateExportFilename(string $type): string
    {
        $parts = [$type];

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
     * Initialize endpoint configuration
     */
    protected function initializeEndpoint(): void
    {
        $this->tableColumns = [
            ['field' => 'key', 'label' => 'Key'],
            ['field' => 'label', 'label' => 'Label'],
            ['field' => 'dataProvider', 'label' => 'Data Provider'],
            ['field' => 'metadata.dataType', 'label' => 'Data Type'],
            ['field' => 'metadata.entity', 'label' => 'Entity Name'],
        ];

        $this->initializePaginationData();
    }

    /**
     * Export current page/filtered data as CSV
     */
    public function exportSelectionsToCsv(): StreamedResponse|RedirectResponse
    {
        $filename = $this->generateExportFilename('data-elements-selections');

        return $this->exportTableDataAsCsv($filename);
    }
}
