<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\PaginatesApiData;
use Illuminate\Http\Client\ConnectionException;
use Log;

class DataElementsList extends BaseApiEndpoint
{
    use PaginatesApiData;

    public function render()
    {
        $paginatedData = $this->getPaginatedData();

        return view('livewire.tools.api-explorer.endpoints.data-elements-list', [
            'paginatedData' => $paginatedData,
        ]);
    }

    /**
     * Override the searchable fields for data elements
     */
    protected function getSearchableFields(): array
    {
        return [
            'key',
            'dataProvider',
            'categories',
            'metadata.dataType',
            'metadata.entity',
        ];
    }

    protected function initializeEndpoint(): void
    {
        // Set table columns specific to data elements
        $this->tableColumns = [
            [
                'field' => 'key',
                'label' => 'Key',
            ],
            [
                'field' => 'label',
                'label' => 'Label',
            ],
            [
                'field' => 'dataProvider',
                'label' => 'Data Provider',
            ],
            [
                'field' => 'metadata.dataType',
                'label' => 'Data Type',
            ],
            [
                'field' => 'metadata.entity',
                'label' => 'Entity Name',
            ],
        ];

        // Initialize pagination data
        $this->initializePaginationData();
    }

    protected function makeApiCall()
    {
        if (! $this->isAuthenticated) {
            return null;
        }

        try {
            $response = $this->makeAuthenticatedApiCall(function () {
                return $this->wfmService->getDataElementsPaginated([]);
            });

            $this->processApiResponseData($response, 'DataElementsList');

            return $response;

        } catch (ConnectionException $ce) {
            $this->errorMessage = 'Unable to connect to API. Please check your network connection and try again.';
            Log::error('Connection error in DataElementsList', [
                'error' => $ce->getMessage(),
            ]);
            $this->totalRecords = 0;

            return null;
        }
    }

    /**
     * Override the data processing for data elements API response
     * The data elements endpoint returns data directly as an array, not wrapped in 'records'
     */
    protected function processApiResponseData($response, string $componentName = ''): void
    {
        if ($response && $response->successful()) {
            $data = $response->json();

            // Data elements API returns the array directly, not wrapped in 'records'
            $records = is_array($data) ? $data : [];

            // Cache the full dataset instead of storing in the component state
            $this->cacheKey = $this->generateCacheKey();
            cache()->put($this->cacheKey, collect($records), now()->addMinutes(30));

            $this->totalRecords = count($records);

            Log::info('Data Elements Cached', [
                'component' => $componentName ?: get_class($this),
                'total_records_available' => $this->totalRecords,
                'records_fetched' => count($records),
                'cache_key' => $this->cacheKey,
                'memory_usage_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ]);
        } else {
            $this->totalRecords = 0;
        }
    }
}
