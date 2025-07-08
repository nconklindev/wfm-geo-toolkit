<?php

namespace App\Traits;

use Illuminate\Support\Collection;

trait CombinesMultipleApiCalls
{
    protected Collection $combinedData;

    /**
     * Create a mock response object that implements the interface expected by BaseApiEndpoint
     */
    protected function createMockResponse(?array $data = null, int $recordCount = 0): object
    {
        $responseData = $data ?? $this->combinedData?->toArray() ?? [];
        $actualRecordCount = $recordCount ?: count($responseData);

        return new class($responseData, $actualRecordCount)
        {
            private array $data;

            private int $recordCount;

            public function __construct(array $data, int $recordCount)
            {
                $this->recordCount = $recordCount;
                $this->data = [
                    'records' => $data,
                    'record_count' => $recordCount,
                    'message' => 'Request completed successfully',
                    'click_to_view' => 'Click "Show Raw JSON" below to view full response',
                ];
            }

            public function successful(): bool
            {
                return true;
            }

            public function status(): int
            {
                return 200;
            }

            public function json()
            {
                return $this->data;
            }

            public function headers(): array
            {
                return [];
            }
        };
    }

    /**
     * Make multiple API calls and combine the results
     */
    protected function makeMultipleApiCalls(array $apiCallFunctions, ?callable $uniqueKeyGenerator = null): Collection
    {
        $responses = [];

        foreach ($apiCallFunctions as $apiCallFunction) {
            $response = $this->makeAuthenticatedApiCall($apiCallFunction);
            if ($response) {
                $responses[] = $response;
            }
        }

        return $this->combineApiResponses($responses, $uniqueKeyGenerator);
    }

    /**
     * Combine multiple API responses into a single collection
     */
    protected function combineApiResponses(array $responses, ?callable $uniqueKeyGenerator = null): Collection
    {
        $allRecords = [];

        foreach ($responses as $response) {
            if ($response && $response->successful()) {
                $data = $response->json();
                if (isset($data['records']) && is_array($data['records'])) {
                    $allRecords = array_merge($allRecords, $data['records']);
                }
            }
        }

        $collection = collect($allRecords);

        // Apply unique key generator if provided
        if ($uniqueKeyGenerator) {
            $collection = $collection->unique($uniqueKeyGenerator);
        }

        return $collection;
    }
}
