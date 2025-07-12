<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\ExportsCsvData;
use App\Traits\PaginatesApiData;
use Illuminate\Http\Client\Response;
use Illuminate\View\View;

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
     * Fetch data from WFM API
     */
    protected function fetchData(): ?Response
    {
        return $this->makeAuthenticatedApiCall(function () {
            return $this->wfmService->getDataElementsPaginated();
        });
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
}
