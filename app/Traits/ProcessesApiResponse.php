<?php

namespace App\Traits;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

trait ProcessesApiResponse
{
    protected function extractDataFromResponse(?Response $response): array
    {
        if (! $response || ! $response->successful()) {
            Log::info('ProcessesApiResponse: No response or unsuccessful', [
                'component' => get_class($this),
                'has_response' => (bool) $response,
                'status' => $response ? $response->status() : 'no response',
            ]);

            return [];
        }

        $data = $response->json();

        // Use the concrete class's data key specification
        $dataKey = $this->getDataKeyFromResponse();

        Log::info('ProcessesApiResponse: Extracting data', [
            'component' => get_class($this),
            'dataKey' => $dataKey,
            'response_keys' => array_keys($data),
            'has_data_key' => isset($data[$dataKey]),
        ]);

        if ($dataKey && isset($data[$dataKey])) {
            $extractedData = $data[$dataKey];

            Log::info('ProcessesApiResponse: Data extracted successfully', [
                'component' => get_class($this),
                'dataKey' => $dataKey,
                'extracted_count' => is_countable($extractedData) ? count($extractedData) : 'not countable',
                'extracted_type' => gettype($extractedData),
            ]);

            return $extractedData;
        }

        // If no specific key is provided, use common patterns
        if ($dataKey === null) {
            return $this->extractDataUsingCommonPatterns($data);
        }

        // If a specific key was provided but not found, return empty array
        Log::warning('ProcessesApiResponse: Data key not found in response', [
            'component' => get_class($this),
            'dataKey' => $dataKey,
            'available_keys' => array_keys($data),
        ]);

        return [];
    }

    protected function extractDataUsingCommonPatterns(array $data): array
    {
        // Try common data wrapper keys
        $commonKeys = ['data', 'records', 'items', 'results'];

        foreach ($commonKeys as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                return $data[$key];
            }
        }

        // If it's an array of items (no wrapper), return as is
        if (! empty($data) && $this->isArrayOfItems($data)) {
            return $data;
        }

        return [];
    }

    protected function isArrayOfItems(array $data): bool
    {
        // Check if it's a numerically indexed array of objects/arrays
        return array_is_list($data)
            &&
               ! empty($data) &&
               (is_array($data[0]) || is_object($data[0]));
    }

    protected function extractTotalFromResponse(?Response $response): int
    {
        if (! $response || ! $response->successful()) {
            return 0;
        }

        $data = $response->json();

        // Use the concrete class's total extraction logic
        $total = $this->getTotalFromResponseData($data);

        return $total ?? $this->extractTotalUsingCommonPatterns($data);
    }

    protected function extractTotalUsingCommonPatterns(array $data): int
    {
        // Try different total fields
        $totalFields = ['totalRecords', 'total', 'count', 'totalCount'];

        foreach ($totalFields as $field) {
            if (isset($data[$field]) && is_numeric($data[$field])) {
                return (int) $data[$field];
            }
        }

        // If we have a specific data key, count those items
        $dataKey = $this->getDataKeyFromResponse();
        if ($dataKey && isset($data[$dataKey]) && is_array($data[$dataKey])) {
            return count($data[$dataKey]);
        }

        // If it's just an array, count the items
        if ($this->isArrayOfItems($data)) {
            return count($data);
        }

        return 0;
    }

    protected function setSuccessfulApiResponse(): void
    {
        $recordCount = count($this->data);

        // Create a user-friendly API response
        $this->apiResponse = [
            'status' => 200,
            'data' => [
                'message' => "Data loaded successfully - $recordCount records",
                'record_count' => $recordCount,
                'click_to_view' => 'Click "Show Raw JSON" below to view full response',
                'cached' => false,
            ],
        ];

        // Set the raw JSON cache key for the raw JSON viewer
        $this->rawJsonCacheKey = $this->getCacheKey();
    }

    protected function logApiResponse(Response $response, string $context = ''): void
    {
        Log::info('API Response', [
            'context' => $context,
            'status' => $response->status(),
            'component' => get_class($this),
            'response_size' => strlen($response->body()),
        ]);
    }
}
