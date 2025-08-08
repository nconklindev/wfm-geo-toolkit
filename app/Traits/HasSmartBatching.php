<?php

namespace App\Traits;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

trait HasSmartBatching
{
    public bool $enableSmartBatching = true;

    /**
     * Fetch all records from a paginated API endpoint using smart batching
     */
    protected function fetchAllRecordsWithSmartBatching(
        callable $authenticatedApiCallFunction,
        callable $apiCallFunction,
        ?int $initialBatchSize = null,
        ?int $maxBatchSize = null,
        string $logContext = 'SmartBatching'
    ): Collection {
        $batchSize = $initialBatchSize ?? $this->getBatchSize();
        $pageIndex = 0;
        $allRecords = collect();
        $hasMoreData = true;
        $requestCount = 0;

        while ($this->shouldBatch()) {
            $requestCount++;

            $requestParams = $this->getBatchParams($pageIndex, $batchSize);

            $this->logBatchRequest($logContext, $requestCount, $pageIndex, $batchSize, $allRecords->count());

            $response = $authenticatedApiCallFunction(function () use ($apiCallFunction, $requestParams) {
                return $apiCallFunction($requestParams);
            });

            if (! $this->isValidResponse($response)) {
                if ($this->handleBatchingError($response, $requestParams, $logContext)) {
                    // Retry with a smaller batch size if suggested by the error handler
                    $batchSize = $this->getReducedBatchSize($batchSize);

                    continue;
                }
                $hasMoreData = false;
                break;
            }

            $records = $this->extractRecordsFromResponse($response);

            if ($records->isEmpty()) {
                $this->logBatchCompletion($logContext, $allRecords->count(), $pageIndex);
                $hasMoreData = false;
                break;
            }

            // Transform and collect records
            $transformedRecords = $this->transformApiData($records->toArray());
            $allRecords = $allRecords->concat($transformedRecords->toArray());

            // Check if we've reached the end - if records returned < requested, we're done
            if ($records->count() < $batchSize) {
                $this->logBatchEnd($logContext, $batchSize, $records->count(), $allRecords->count());
                $hasMoreData = false;
                break;
            }

            // Move to next page
            $pageIndex++;

            // Safety check to prevent infinite loops
            if ($this->hasExceededMaxRequests($requestCount)) {
                $this->logMaxRequestsExceeded($logContext, $requestCount, $allRecords->count());
                $hasMoreData = false;
                break;
            }
        }

        $this->logFinalBatchResults($logContext, $allRecords->count(), $requestCount, $batchSize);

        return $allRecords;
    }

    protected function logBatchRequest(string $context, int $requestNumber, int $pageIndex, int $batchSize, int $totalSoFar): void
    {
        Log::info("$context: Making API request", [
            'request_number' => $requestNumber,
            'page_index' => $pageIndex,
            'batch_size' => $batchSize,
            'total_records_so_far' => $totalSoFar,
        ]);
    }

    /**
     * Check if the response is valid
     */
    protected function isValidResponse(?Response $response): bool
    {
        return $response && $response->successful();
    }

    /**
     * Handle batching errors and determine if retry should occur
     */
    protected function handleBatchingError(?Response $response, array $requestParams, string $context): bool
    {
        if (property_exists($this, 'errorMessage')) {
            $this->errorMessage = 'Failed to load data from API. Please try again.';
        }

        Log::error("$context: API call failed", [
            'status' => $response ? $response->status() : 'no_response',
            'request_params' => $requestParams,
            'hostname' => property_exists($this, 'hostname') ? $this->hostname : 'unknown',
            'component' => get_class($this),
        ]);

        // Suggest retry with a smaller batch size for certain errors
        return $response && $response->status() === 400 && ($requestParams['count'] ?? 0) > 100;
    }

    /**
     * Get reduced batch size for retries
     */
    protected function getReducedBatchSize(int $currentBatchSize): int
    {
        return max(100, (int) ($currentBatchSize / 2));
    }

    /**
     * Extract records from API response
     */
    protected function extractRecordsFromResponse(Response $response): Collection
    {
        $data = $response->json();

        return collect($data['records'] ?? $data['data'] ?? []);
    }

    protected function logBatchCompletion(string $context, int $finalTotal, int $finalPageIndex): void
    {
        Log::info("$context: No more records found", [
            'final_total' => $finalTotal,
            'final_page_index' => $finalPageIndex,
        ]);
    }

    /**
     * Transform API data (override in implementing class)
     */
    protected function transformApiData(array $data): Collection
    {
        return collect($data);
    }

    protected function logBatchEnd(string $context, int $requested, int $received, int $totalRecords): void
    {
        Log::info("$context: Reached end of data", [
            'requested' => $requested,
            'received' => $received,
            'total_records' => $totalRecords,
        ]);
    }

    /**
     * Check if max requests limit exceeded
     */
    protected function hasExceededMaxRequests(int $requestCount): bool
    {
        return $requestCount > $this->getMaxRequests();
    }

    /**
     * Get maximum number of requests allowed
     */
    protected function getMaxRequests(): int
    {
        return 50;
    }

    protected function logMaxRequestsExceeded(string $context, int $requestCount, int $totalRecords): void
    {
        Log::warning("$context: Too many requests, stopping to prevent infinite loop", [
            'request_count' => $requestCount,
            'total_records' => $totalRecords,
        ]);
    }

    // Logging methods

    protected function logFinalBatchResults(string $context, int $totalRecords, int $totalRequests, int $finalBatchSize): void
    {
        Log::info("$context: Complete", [
            'total_records' => $totalRecords,
            'total_requests' => $totalRequests,
            'final_batch_size' => $finalBatchSize,
        ]);
    }

    /**
     * Get maximum batch size (override in implementing class if needed)
     */
    protected function getMaxBatchSize(): ?int
    {
        return null;
    }

    /**
     * Execute a batched API call (legacy method for backward compatibility)
     */
    protected function executeBatchedApiCall(callable $apiMethod, array $baseParams): array
    {
        if (! $this->shouldBatch()) {
            return $apiMethod($baseParams);
        }

        $results = [];
        $batchSize = $this->getBatchSize();
        $totalBatches = $this->calculateTotalBatches();

        for ($i = 0; $i < $totalBatches; $i++) {
            $batchParams = array_merge($baseParams, ['page' => $i + 1]);
            $result = $apiMethod($batchParams);

            if (is_array($result)) {
                $results[] = $result;
            }

            Log::debug('Batching progress', ['batch' => $i + 1, 'total' => $totalBatches]);
        }

        return array_merge(...$results);
    }

    /**
     * Calculate total batches needed
     */
    protected function calculateTotalBatches(): int
    {
        $totalRecords = $this->getTotalRecords();
        $batchSize = $this->getBatchSize();

        return $totalRecords > 0 ? ceil($totalRecords / $batchSize) : 1;
    }

    /**
     * Get total records count (override in implementing class if available)
     */
    protected function getTotalRecords(): int
    {
        return $this->totalRecords ?: 0;
    }
}
