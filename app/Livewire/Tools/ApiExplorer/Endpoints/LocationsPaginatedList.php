<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiComponent;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;

class LocationsPaginatedList extends BaseApiComponent
{
    public string $errorMessage = '';

    #[Validate('required|string|max:255')]
    public string $qualifier = '';

    #[Validate('required|date|max:255')]
    public string $date = '';

    public function getCacheKey(): string
    {
        $id = md5(session()->id());

        return 'locations_'.$id;
    }

    public function getCacheTtl(): int
    {
        return 3600;
    }

    protected function getApiParams(): array
    {
        return [
            'where' => ['descendantsOf' => ['context' => 'ORG',
                'date' => $this->date,
                'locationRef' => ['qualifier' => $this->qualifier],
            ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getApiServiceCall(): callable
    {
        return fn ($params) => $this->wfmService->getLocationsPaginated($params);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDataKeyFromResponse(): ?string
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    protected function getTotalFromResponseData(array $data): ?int
    {
        return count($data);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDataForCsvExport(): Collection
    {
        // Try to get cached data first
        if (! empty($this->data)) {
            return collect($this->data);
        }

        // If no cached data, and we're authenticated, fetch fresh data
        if ($this->isAuthenticated) {
            $this->loadData();

            return collect($this->data);
        }

        // Return an empty collection if not authenticated or no data
        return collect();
    }

    /**
     * {@inheritDoc}
     */
    protected function getCsvColumns(): array
    {
        return $this->getTableColumns();
    }

    /**
     * {@inheritDoc}
     */
    protected function getTableColumns(): array
    {
        return [
            ['field' => 'nodeId', 'label' => 'ID'],
            ['field' => 'orgNodeTypeRef.qualifier', 'label' => 'Type'],
            ['field' => 'name', 'label' => 'Name'],
            ['field' => 'fullName', 'label' => 'Full Name'],
            ['field' => 'orgPath', 'label' => 'Org Path'],
            ['field' => 'effectiveDate', 'label' => 'Effective Date'],
            ['field' => 'expirationDate', 'label' => 'Expiration Date'],
            ['field' => 'transferable', 'label' => 'Transferable'],
            ['field' => 'costCenterRef.qualifier', 'label' => 'Cost Center'],
        ];
    }
}
