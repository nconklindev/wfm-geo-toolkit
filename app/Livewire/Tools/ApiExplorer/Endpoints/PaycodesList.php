<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\ExportsCsvData;
use App\Traits\PaginatesApiData;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaycodesList extends BaseApiEndpoint
{
    use ExportsCsvData;
    use PaginatesApiData;

    // Property to store the table data for export
    public array $tableData = [];

    public function render(): View
    {
        $paginatedData = $this->getPaginatedData();

        return view('livewire.tools.api-explorer.endpoints.paycodes-list', ['paginatedData' => $paginatedData]);
    }

    /**
     * Export all paycodes data as CSV
     * Called by the "Export All (CSV)" button in the blade template
     */
    public function exportAllToCsv(): StreamedResponse|RedirectResponse
    {
        try {
            // Make a fresh API call to get all data (not paginated)
            $response = $this->makeAuthenticatedApiCall(function () {
                return $this->wfmService->getPaycodes();
            });

            // Extract the actual data from the response
            // This mimics what processApiResponseData does but just for the data extraction
            $allData = $this->extractDataFromResponse($response);

            if (empty($allData)) {
                session()->flash('error', 'No data available to export.');

                return back();
            }

            // Temporarily store all data for export
            $originalTableData = $this->tableData;
            $this->tableData = $allData;

            // Use the trait method with a custom filename
            $filename = $this->generateExportFilename('paycodes-all');
            $response = $this->exportTableDataAsCsv($filename);

            // Restore original table data
            $this->tableData = $originalTableData;

            return $response;
        } catch (Exception $e) {
            Log::error('Error exporting all paycodes data', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            session()->flash('error', 'Failed to export data. Please try again.');

            return back();
        }
    }

    /**
     * Extract data from API response
     * This method handles the response processing without updating pagination/caching
     */
    private function extractDataFromResponse($response): array
    {
        if (! $response || ! $response->successful()) {
            return [];
        }

        $data = $response->json();

        // Handle different response structures
        if (isset($data['data'])) {
            return is_array($data['data']) ? $data['data'] : [];
        }

        if (is_array($data)) {
            return $data;
        }

        return [];
    }

    /**
     * Export current page/filtered paycodes data as CSV
     * Called by the "Export Selections (CSV)" button in the blade template
     */
    public function exportSelectionsToCsv(): StreamedResponse|RedirectResponse
    {
        // Use the trait method with current tableData and a custom filename
        $filename = $this->generateExportFilename('paycodes-selections');

        return $this->exportTableDataAsCsv($filename);
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
                'field' => 'description',
                'label' => 'Description',
            ],
            [
                'field' => 'type',
                'label' => 'Type',
            ],
            [
                'field' => 'money',
                'label' => 'Money',
            ],
            [
                'field' => 'excusedAbsence',
                'label' => 'Excuses Absence',
            ],
            [
                'field' => 'totals',
                'label' => 'Include in Totals',
            ],
            [
                'field' => 'unit',
                'label' => 'Unit',
            ],
            [
                'field' => 'associatedDurationPayCode.qualifier',
                'label' => 'Associated Duration Pay Code',
            ],
            [
                'field' => 'combined',
                'label' => 'Combined',
            ],
            [
                'field' => 'visibleToTimecardSchedule',
                'label' => 'Visible in Timecard and Schedule',
            ],
            [
                'field' => 'visibleToReports',
                'label' => 'Visible in Reports',
            ],
        ];

        // Initialize pagination data
        $this->initializePaginationData();
    }

    protected function makeApiCall(): mixed
    {
        if (! $this->isAuthenticated) {
            return null;
        }

        try {
            $response = $this->makeAuthenticatedApiCall(function () {
                return $this->wfmService->getPaycodes();
            });

            $this->processApiResponseData($response, 'PaycodesList');

            // Store the processed data for export functionality
            $this->tableData = $this->extractDataFromResponse($response);

            // Clear any previous error messages on a successful call
            $this->errorMessage = '';

            return $response;
        } catch (Exception $e) {
            // Handle other types of exceptions
            $this->errorMessage = 'An unexpected error occurred. Please try again later.';

            Log::error('Unexpected error in PaycodesList', [
                'error' => $e->getMessage(),
                'type' => get_class($e),
                'stack_trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
            ]);

            $this->totalRecords = 0;
            $this->tableData = []; // Clear table data on error

            if (! empty($this->cacheKey)) {
                cache()->forget($this->cacheKey);
            }

            return null;
        }
    }
}
