<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\ExportsCsvData;
use App\Traits\PaginatesApiData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\Response;

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
}
