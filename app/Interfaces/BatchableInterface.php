<?php

namespace App\Interfaces;

interface BatchableInterface
{
    public function shouldBatch(): bool;

    public function getBatchSize(): int;

    public function getBatchParams(int $index, int $count): array;
}
