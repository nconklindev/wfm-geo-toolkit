<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiComponent;
use Illuminate\Support\Collection;

class HyperfindQueriesList extends BaseApiComponent
{
    public string $errorMessage = '';

    public function getCacheKey(): string
    {
        $id = md5(session()?->id());

        return 'public_hyperfind_queries_list_'.$id;
    }

    public function getCacheTtl(): int
    {
        return 3600;
    }

    public function transformForCsv(array $data): array
    {
        // No custom transformations needed for CSV
        return $this->transformForView($data);
    }

    public function transformForView(array $data): array
    {
        return collect($data)->map(function ($hyperfind, $index) {
            if (! is_array($hyperfind)) {
                return null;
            }

            return [
                'id' => $hyperfind['id'] ?? 'N/A',
                'name' => $hyperfind['name'] ?? 'Unknown',
                'description' => $hyperfind['description'] ?? '',
            ];
        })->filter()->toArray(); // Remove null entries
    }

    protected function getApiParams(): array
    {
        return [
            'all_details' => 'true',
        ];
    }

    protected function getApiServiceCall(): callable
    {
        return fn (array $params) => $this->wfmService->getHyperfindQueries($params);
    }

    protected function getDataKeyFromResponse(): ?string
    {
        return 'hyperfindQueries';
    }

    protected function getTotalFromResponseData(array $data): ?int
    {
        // This endpoint doesn't provide a total count, so count the data array
        if (isset($data['hyperfindQueries']) && is_array($data['hyperfindQueries'])) {
            return count($data['hyperfindQueries']);
        }

        return null; // Fall back to common patterns
    }

    // Custom total extraction for this endpoint

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
     * Get CSV column definitions
     * Required by HasCsvExport trait
     */
    protected function getCsvColumns(): array
    {
        // For this simple endpoint, CSV columns match table columns
        return $this->getTableColumns();
    }

    /**
     *   {@inheritDoc}
     */
    protected function getTableColumns(): array
    {
        return [
            ['field' => 'id', 'label' => 'ID'],
            ['field' => 'name', 'label' => 'Name'],
            ['field' => 'description', 'label' => 'Description'],
        ];
    }
}
