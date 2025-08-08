<?php

namespace App\Livewire\Tools\ApiExplorer;

use App\Interfaces\BatchableInterface;
use App\Traits\HasSmartBatching;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BaseBatchableApiComponent extends BaseApiComponent implements BatchableInterface
{
    use HasSmartBatching;

    // Batching configuration properties
    public int $initialBatchSize = 250;

    public int $maxBatchSize = 1000;

    public int $maxRequests = 50;

    // Batching state properties
    protected int $totalProcessedRecords = 0;

    protected array $batchingErrors = [];

    /**
     * Get batch parameters for API requests
     */
    public function getBatchParams(int $index, int $count): array
    {
        return [
            'index' => $index,
            'count' => $count,
        ];
    }

    /**
     * Get batching statistics for debugging/monitoring
     */
    public function getBatchingStats(): array
    {
        return [
            'total_processed_records' => $this->totalProcessedRecords,
            'error_count' => count($this->batchingErrors),
            'current_batch_size' => $this->getBatchSize(),
            'max_batch_size' => $this->getMaxBatchSize(),
            'smart_batching_enabled' => $this->shouldBatch(),
        ];
    }

    /**
     * Get the initial batch size
     */
    public function getBatchSize(): int
    {
        return $this->initialBatchSize;
    }

    /**
     * Get maximum batch size
     */
    protected function getMaxBatchSize(): int
    {
        return $this->maxBatchSize;
    }

    /**
     * Should this component use batching?
     */
    public function shouldBatch(): bool
    {
        return $this->enableSmartBatching;
    }

    /**
     * Get maximum number of requests allowed
     */
    protected function getMaxRequests(): int
    {
        return $this->maxRequests;
    }

    /**
     * Override BaseApiComponent's fetchDataFromApi to use smart batching
     */
    protected function fetchDataFromApi(): array
    {
        if (! $this->shouldBatch()) {
            return parent::fetchDataFromApi();
        }

        $this->resetBatchingState();

        try {
            $allRecords = $this->fetchAllRecordsWithSmartBatching(
                fn (callable $apiCall) => $this->makeAuthenticatedApiCall($apiCall),
                fn (array $params) => $this->getApiServiceCall()($params),
                $this->initialBatchSize,
                $this->maxBatchSize,
                $this->getLogContext()
            );

            $this->totalProcessedRecords = $allRecords->count();

            return $allRecords->toArray();

        } catch (Throwable $e) {
            $this->handleBatchingException($e);

            return [];
        }
    }

    /**
     * Reset batching state before starting a new batch operation
     */
    protected function resetBatchingState(): void
    {
        $this->totalProcessedRecords = 0;
        $this->batchingErrors = [];

        // Clear the parent's error message if it exists
        $this->errorMessage = '';
    }

    /**
     * Get log context for batching operations
     */
    protected function getLogContext(): string
    {
        return class_basename($this).'Batching';
    }

    /**
     * Handle batching exceptions
     */
    protected function handleBatchingException(Throwable $e): void
    {
        $this->errorMessage = 'Batching operation failed: '.$e->getMessage();

        Log::error('Batching operation failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'component' => get_class($this),
            'total_processed' => $this->totalProcessedRecords,
        ]);
    }

    /**
     * Handle individual batch errors with retry logic
     */
    protected function handleBatchingError($response, array $requestParams, string $context): bool
    {
        $this->batchingErrors[] = [
            'context' => $context,
            'params' => $requestParams,
            'status' => $response ? $response->status() : null,
            'timestamp' => now(),
        ];

        // Use parent's error handling if errorMessage property exists
        $this->errorMessage = 'API request failed. Retrying with smaller batch size...';

        Log::error("$context: API call failed", [
            'status' => $response ? $response->status() : 'no_response',
            'request_params' => $requestParams,
            'hostname' => $this->hostname ?? 'unknown',
            'component' => get_class($this),
            'error_count' => count($this->batchingErrors),
        ]);

        // Retry with a smaller batch size for certain HTTP errors
        return $response && in_array($response->status(), [400, 413, 429], true)
            &&
               ($requestParams['count'] ?? 0) > 100;
    }
}
