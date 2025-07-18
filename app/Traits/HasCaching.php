<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait HasCaching
{
    protected function rememberCachedData(string $key, callable $callback, int $ttl = 3600): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    protected function clearCache(?string $key = null): void
    {
        if ($key) {
            Cache::forget($key);
        } else {
            Cache::forget($this->getCacheKey());
        }
    }

    protected function storeCachedData(string $key, mixed $data, int $ttl = 3600): mixed
    {
        Cache::put($key, $data, $ttl);

        return $data;
    }

    protected function getCachedDataDirect(string $key): mixed
    {
        return Cache::get($key);
    }
}
