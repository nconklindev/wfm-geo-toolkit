<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiComponent;
use Illuminate\Support\Collection;

class DataElementsList extends BaseApiComponent
{
    public string $errorMessage = '';

    public function getCacheKey(): string
    {
        $id = md5(session()?->id());

        return 'data_elements_list_'.$id;
    }

    public function getCacheTtl(): int
    {
        return 3600;
    }

    public function transformForView(array $data): array
    {
        return array_map(static function ($item) {
            return [
                'key' => $item['key'] ?? 'Unknown',
                'label' => $item['label'] ?? 'Unknown',
                'dataProvider' => $item['dataProvider'] ?? 'Unknown',
                'dataType' => $item['metadata']['dataType'] ?? 'Unknown',
                'entity' => $item['metadata']['entity'] ?? 'Unknown',
            ];
        }, $data);
    }

    protected function getApiParams(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    protected function getApiServiceCall(): callable
    {
        return fn (array $params) => $this->wfmService->getDataElementsPaginated($params);
    }

    protected function getDataKeyFromResponse(): ?string
    {
        return null;
    }

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

        // If no cached data and we're authenticated, fetch fresh data
        if ($this->isAuthenticated) {
            $this->loadData();

            return collect($this->data);
        }

        // Return empty collection if not authenticated or no data
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
            ['field' => 'key', 'label' => 'Key'],
            ['field' => 'label', 'label' => 'Label'],
            ['field' => 'dataProvider', 'label' => 'Data Provider'],
            ['field' => 'dataType', 'label' => 'Data Type'], // Flattened in transformForView
            ['field' => 'entity', 'label' => 'Entity Name'], // Flattened in transformForView
        ];
    }
}
