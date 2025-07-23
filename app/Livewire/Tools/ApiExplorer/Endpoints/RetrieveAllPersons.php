<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseBatchableApiComponent;
use Illuminate\Support\Collection;
use stdClass;

class RetrieveAllPersons extends BaseBatchableApiComponent
{
    public string $errorMessage = '';

    public int $initialBatchSize = 500;

    public int $maxBatchSize = 1000;

    public function getCacheKey(): string
    {
        $id = md5(session()?->id());

        return 'persons_'.$id;
    }

    // Set cache TTL to 30 minutes
    public function getCacheTtl(): int
    {
        return 30 * 60;
    }

    public function getBatchParams(int $index, int $count): array
    {
        return [
            'where' => new stdClass,
            'index' => $index,
            'count' => $count,
        ];
    }

    protected function getApiParams(): array
    {
        return [
            'where' => new stdClass,
            'index' => 0,
            'count' => $this->initialBatchSize,
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getApiServiceCall(): callable
    {
        return fn (array $params) => $this->wfmService->getAllPersonsPaginated($params);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDataKeyFromResponse(): ?string
    {
        return 'records';
    }

    /**
     * {@inheritDoc}
     */
    protected function getTotalFromResponseData(array $data): ?int
    {
        // Developer docs page shows a totalElements property
        return $data['totalElements'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDataForCsvExport(): Collection
    {
        // First, try to get from cached data (much faster)
        if (! empty($this->data)) {
            return collect($this->data);
        }

        // Fall back to fetching fresh data if no cached data
        return collect($this->fetchDataFromApi());
    }

    /**
     * {@inheritDoc}
     */
    protected function getCsvColumns(): array
    {
        // No formatting necessary for this endpoint
        return $this->getTableColumns();
    }

    /**
     * {@inheritDoc}
     */
    protected function getTableColumns(): array
    {
        return [
            ['field' => 'personId', 'label' => 'Database ID'],
            ['field' => 'personNumber', 'label' => 'Employee ID'],
            ['field' => 'firstName', 'label' => 'First Name'],
            ['field' => 'lastName', 'label' => 'Last Name'],
            ['field' => 'employmentStatus', 'label' => 'Employment Status'],
            ['field' => 'userAccountStatus', 'label' => 'User Account Status'],
        ];
    }

    protected function transformApiData(array $data): Collection
    {
        // Keep the data as-is for now - the view handles boolean display
        return collect($data);
    }
}
