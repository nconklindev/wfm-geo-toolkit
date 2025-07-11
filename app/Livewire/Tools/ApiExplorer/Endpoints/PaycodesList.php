<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\ExportsCsvData;
use App\Traits\PaginatesApiData;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaycodesList extends BaseApiEndpoint
{
    use ExportsCsvData;
    use PaginatesApiData;

    public function render(): View
    {
        $paginatedData = $this->getPaginatedData();

        return view('livewire.tools.api-explorer.endpoints.paycodes-list', [
            'paginatedData' => $paginatedData,
        ]);
    }

    /**
     * Export all paycodes data as CSV
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

            $filename = $this->generateExportFilename('paycodes-all');

            return $this->exportAsCsv($allData, $this->tableColumns, $filename);
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
     * Fetch data from WFM API
     */
    protected function fetchData(): ?Response
    {
        return $this->makeAuthenticatedApiCall(function () {
            return $this->wfmService->getPaycodes();
        });
    }

    /**
     * Initialize endpoint configuration
     */
    protected function initializeEndpoint(): void
    {
        $this->tableColumns = [
            ['field' => 'name', 'label' => 'Name'],
            ['field' => 'description', 'label' => 'Description'],
            ['field' => 'type', 'label' => 'Type'],
            ['field' => 'money', 'label' => 'Money'],
            ['field' => 'excusedAbsence', 'label' => 'Excuses Absence'],
            ['field' => 'totals', 'label' => 'Include in Totals'],
            ['field' => 'unit', 'label' => 'Unit'],
            ['field' => 'associatedDurationPayCode.qualifier', 'label' => 'Associated Duration Pay Code'],
            ['field' => 'combined', 'label' => 'Combined'],
            ['field' => 'visibleToTimecardSchedule', 'label' => 'Visible in Timecard and Schedule'],
            ['field' => 'visibleToReports', 'label' => 'Visible in Reports'],
        ];

        $this->initializePaginationData();
    }

    /**
     * Export current page/filtered data as CSV
     */
    public function exportSelectionsToCsv(): StreamedResponse|RedirectResponse
    {
        $filename = $this->generateExportFilename('paycodes-selections');

        return $this->exportTableDataAsCsv($filename);
    }
}
