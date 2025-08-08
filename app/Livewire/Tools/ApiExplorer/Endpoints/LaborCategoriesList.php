<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiComponent;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;

class LaborCategoriesList extends BaseApiComponent
{
    public string $errorMessage = '';

    #[Validate('required|string|max:255')]
    public string $name = '';

    public function getCacheKey(): string
    {
        $id = md5(session()->id());

        return "labor_categories_{$id}_$this->name";
    }

    public function getCacheTtl(): int
    {
        return 3600;
    }

    protected function getApiParams(): array
    {
        return [
            'where' => [
                'entries' => [
                    'qualifiers' => [$this->name],
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getApiServiceCall(): callable
    {
        return fn ($params) => $this->wfmService->getLaborCategoryEntries($params);
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
            ['field' => 'description', 'label' => 'Description'],
            ['field' => 'inactive', 'label' => 'Inactive'],
            ['field' => 'laborCategory.name', 'label' => 'Labor Category'],
        ];
    }
}
