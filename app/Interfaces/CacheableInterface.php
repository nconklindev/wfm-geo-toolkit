<?php

namespace App\Interfaces;

interface CacheableInterface
{
    public function getCacheKey(): string;

    public function getCacheTtl(): int;
}
