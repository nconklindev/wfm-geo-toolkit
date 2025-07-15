<?php

namespace App\Traits;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

trait HandlesBatchedApiRequests
{
    /**
     * Fetch all records from a paginated API endpoint using smart batching
     */
    protected function fetchAllRecordsWithSmartBatching(
        callable $authenticatedApiCallFunction,
        callable $apiCallFunction,
        int $initialBatchSize = 250,
        int $maxBatchSize = 1000,
        string $logContext = 'SmartBatching'
    ): Collection {
        $batchSize = $initialBatchSize;
        $index = 0;
        $allRecords = collect();
        $hasMoreData = true;
        $requestCount = 0;
        $startTime = microtime(true);

        while ($hasMoreData) {
            $requestCount++;

            $requestData = [
                'count' => $batchSize,
                'index' => $index,
            ];

            Log::info("$logContext: Making API request", [
                'request_number' => $requestCount,
                'index' => $index,
                'batch_size' => $batchSize,
                'total_records_so_far' => $allRecords->count(),
            ]);

            // Use the authenticated API call function passed from the component
            $response = $authenticatedApiCallFunction(function () use ($apiCallFunction, $requestData) {
                return $apiCallFunction($requestData);
            });

            if (! $response || ! $response->successful()) {
                $this->handleBatchingError($response, $requestData, $logContext);

                // Try with a smaller batch size if we suspect limit issues
                if ($response && $response->status() === 400 && $batchSize > 100) {
                    $batchSize = 100;
                    Log::info("$logContext: Retrying with smaller batch size", [
                        'new_batch_size' => $batchSize,
                        'original_batch_size' => $requestData['count'],
                    ]);

                    continue;
                }

                break;
            }

            $data = $response->json();
            $records = collect($data['records'] ?? []);

            if ($records->isEmpty()) {
                Log::info("$logContext: No more records found", [
                    'final_total' => $allRecords->count(),
                    'final_index' => $index,
                ]);
                $hasMoreData = false;
                break;
            }

            // Transform and add records
            $transformedRecords = $this->transformApiData($records->toArray());
            $allRecords = $allRecords->concat($transformedRecords->toArray());
            $index += $batchSize;

            // Smart batch size optimization
            if ($records->count() === $batchSize && $batchSize < $maxBatchSize) {
                $newBatchSize = min($maxBatchSize, $batchSize * 2);
                Log::info("$logContext: Increasing batch size", [
                    'old_batch_size' => $batchSize,
                    'new_batch_size' => $newBatchSize,
                ]);
                $batchSize = $newBatchSize;
            }

            // Check if we've reached the end
            if ($records->count() < $batchSize) {
                Log::info("$logContext: Reached end of data", [
                    'requested' => $batchSize,
                    'received' => $records->count(),
                    'total_records' => $allRecords->count(),
                ]);
                $hasMoreData = false;
            }

            // Safety check
            if ($requestCount > 50) {
                Log::warning("$logContext: Too many requests, stopping to prevent infinite loop", [
                    'request_count' => $requestCount,
                    'total_records' => $allRecords->count(),
                ]);
                break;
            }
        }

        Log::info("$logContext: Complete", [
            'total_records' => $allRecords->count(),
            'total_requests' => $requestCount,
            'final_batch_size' => $batchSize,
        ]);

        return $allRecords;
    }

    /**
     * Handle errors during batching
     */
    protected function handleBatchingError(?Response $response, array $requestData, string $context): void
    {
        $this->errorMessage = 'Failed to load data from API. Please try again.';

        Log::error("$context: API call failed", [
            'status' => $response ? $response->status() : 'no_response',
            'requested_count' => $requestData['count'],
            'hostname' => $this->hostname ?? 'unknown',
            'component' => get_class($this),
        ]);
    }

    /**
     * Override this method in the component to transform API data
     */
    protected function transformApiData(array $data): Collection
    {
        return collect($data);
    }
}
