<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\ExportsCsvData;
use App\Traits\PaginatesApiData;
use Illuminate\Http\Client\Response;
use Livewire\Attributes\Validate;
use stdClass;

class LaborCategoriesList extends BaseApiEndpoint
{
    use ExportsCsvData;
    use PaginatesApiData;

    #[Validate('required|string|max:255')]
    public string $name = '';

    public function render()
    {
        return view('livewire.tools.api-explorer.endpoints.labor-categories-list');
    }

    /**
     * Override to handle cache key regeneration when name changes
     */
    public function updatedName($propertyName): void
    {
        if ($propertyName === 'name') {
            // Regenerate cache key when name changes
            $this->cacheKey = $this->generateCacheKey();
            $this->clearCache();
        }
    }

    protected function initializeEndpoint(): void
    {
        // Configure the table structure
        $this->tableColumns = [
            ['field' => 'name', 'label' => 'Name'],
            ['field' => 'description', 'label' => 'Description'],
            ['field' => 'inactive', 'label' => 'Inactive'],
            ['field' => 'laborCategory.name', 'label' => 'Labor Category'],
        ];

        // Generate cache key
        $this->cacheKey = $this->generateCacheKey();
    }

    /**
     * Fetch data from API
     */
    protected function fetchData(): ?Response
    {
        // Build request data based on form input
        $requestData = ['where' => new stdClass];

        // If name is provided, add it to the request
        if (! empty($this->name)) {
            $requestData['where'] = [
                'entries' => [
                    'qualifiers' => [$this->name],
                ],
            ];
        }

        return $this->makeAuthenticatedApiCall(function () use ($requestData) {
            return $this->wfmService->getLaborCategoryEntries($requestData);
        });
    }

    /**
     * Override to store data without transforming boolean values
     */
    protected function storeData(array $data): void
    {
        // Store data without transforming boolean values
        // The view expects actual boolean values for proper display
        $this->tableData = $data;
        $this->totalRecords = count($data);

        // Cache the data
        if (! empty($this->cacheKey)) {
            cache()->put($this->cacheKey, collect($data), now()->addMinutes(30));
        }
    }

    /**
     * Override to specify boolean fields for this endpoint
     */
    protected function getBooleanFields(): array
    {
        return ['inactive'];
    }
}
