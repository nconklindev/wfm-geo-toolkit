<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiComponent;
use Illuminate\Support\Collection;

class RetrieveKnownIpAddresses extends BaseApiComponent
{
    public string $errorMessage = '';

    public function getCacheKey(): string
    {
        $id = md5(session()->id());

        return 'retrieve_known_ip_addresses_'.$id;
    }

    public function getCacheTtl(): int
    {
        return 3600;
    }

    protected function getApiParams(): array
    {
        return []; // No params for this one
    }

    /**
     * {@inheritDoc}
     */
    protected function getApiServiceCall(): callable
    {
        return fn (array $params) => $this->wfmService->getKnownIpAddresses();
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
            ['field' => 'name', 'label' => 'Name'],
            ['field' => 'startingIPRange', 'label' => 'Start'],
            ['field' => 'endingIPRange', 'label' => 'End'],
        ];
    }
}
