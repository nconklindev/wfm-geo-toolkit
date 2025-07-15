<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\ExportsCsvData;
use App\Traits\PaginatesApiData;
use Illuminate\Http\Client\Response;
use Livewire\Attributes\Validate;

class LocationsPaginatedList extends BaseApiEndpoint
{
    use ExportsCsvData;
    use PaginatesApiData;

    #[Validate('required|string|max:255')]
    public string $qualifier = '';

    #[Validate('required|date|max:255')]
    public string $date = '';

    public function render()
    {
        $paginatedData = $this->getPaginatedData();

        return view(
            'livewire.tools.api-explorer.endpoints.locations-paginated-list',
            ['paginatedData' => $paginatedData],
        );
    }

    protected function initializeEndpoint(): void
    {
        $this->tableColumns = [
            ['field' => 'nodeId', 'label' => 'ID'],
            ['field' => 'orgNodeTypeRef.qualifier', 'label' => 'Type'],
            ['field' => 'name', 'label' => 'Name'],
            ['field' => 'fullName', 'label' => 'Full Name'],
            ['field' => 'orgPath', 'label' => 'Org Path'],
            ['field' => 'effectiveDate', 'label' => 'Effective Date'],
            ['field' => 'expirationDate', 'label' => 'Expiration Date'],
            ['field' => 'transferable', 'label' => 'Transferable'],
            ['field' => 'costCenterRef.qualifier', 'label' => 'Cost Center' ?? '-'],
        ];

        $this->initializePaginationData();
    }

    protected function fetchData(): ?Response
    {
        $this->validate();

        $body = [
            'where' => ['descendantsOf' => ['context' => 'ORG',
                'date' => $this->date,
                'locationRef' => ['qualifier' => $this->qualifier],
            ],
            ],
        ];

        return $this->makeAuthenticatedApiCall(function () use ($body,
        ) {
            return $this->wfmService->getLocationsPaginated($body);
        });
    }
}
