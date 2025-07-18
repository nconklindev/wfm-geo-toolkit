<?php

namespace App\Livewire\Tools\ApiExplorer;

use App\Interfaces\BatchableInterface;
use App\Traits\HasSmartBatching;

abstract class BaseBatchableApiComponent extends BaseApiComponent implements BatchableInterface
{
    use HasSmartBatching;

    public function shouldBatch(): bool
    {
        return true;
    }

    // Default batching behavior

    public function getBatchSize(): int
    {
        return 250;
    }

    public function getBatchParams(int $index, int $count): array
    {
        return [
            'index' => $index,
            'count' => $count,
        ];
    }

    protected function fetchDataFromApi(): array
    {
        $params = $this->getApiParams();
        $apiMethod = $this->getApiServiceCall();
        $response = $this->executeBatchedApiCall($apiMethod, $params);

        return $response['data'] ?? [];
    }
}
