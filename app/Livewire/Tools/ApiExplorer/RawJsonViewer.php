<?php

namespace App\Livewire\Tools\ApiExplorer;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class RawJsonViewer extends Component
{
    public string $cacheKey = '';

    public bool $isVisible = false;

    public ?array $jsonData = null;

    public bool $isLoading = false;

    public ?string $errorMessage = null;

    protected $rules = [
        'cacheKey' => 'required|string',
    ];

    public function mount(string $cacheKey = ''): void
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * Listen for cache key updates from parent component
     */
    public function updatedCacheKey(): void
    {
        // Clear existing data when cache key changes
        $this->jsonData = null;
        $this->errorMessage = null;
        $this->isVisible = false;
    }

    /**
     * Listen for clear events from parent component
     */
    #[On('clear-raw-json-viewer')]
    public function clearData(): void
    {
        $this->jsonData = null;
        $this->errorMessage = null;
        $this->isVisible = false;
    }

    public function toggleVisibility(?string $cacheKey = null): void
    {
        // Only respond if this is the correct cache key
        if ($cacheKey && $cacheKey !== $this->cacheKey) {
            return;
        }

        $this->isVisible = ! $this->isVisible;

        if ($this->isVisible && ! $this->jsonData && ! $this->errorMessage) {
            $this->loadJsonData();
        }

        if (! $this->isVisible) {
            $this->jsonData = null; // Free memory when hidden
            $this->errorMessage = null;
        }

        // Update the button text in the parent component
        $this->dispatch('raw-json-toggled', [
            'cacheKey' => $this->cacheKey,
            'isVisible' => $this->isVisible,
        ]);
    }

    public function loadJsonData(): void
    {
        if (empty($this->cacheKey)) {
            $this->errorMessage = 'No cache key provided';

            return;
        }

        $this->isLoading = true;
        $this->errorMessage = null;

        try {
            $cachedData = cache()->get($this->cacheKey);

            if ($cachedData === null) {
                $this->errorMessage = 'No cached data found for this request';

                return;
            }

            // Handle both Collection and array data
            if ($cachedData instanceof Collection) {
                $dataArray = $cachedData->toArray();
                $count = $cachedData->count();
            } elseif (is_array($cachedData)) {
                $dataArray = $cachedData;
                $count = count($cachedData);
            } else {
                $this->errorMessage = 'Invalid cached data format';

                return;
            }

            $this->jsonData = [
                'status' => 200,
                'data' => $dataArray,
                'total_records' => $count,
                'loaded_from' => 'cache',
                'cache_key' => $this->cacheKey,
            ];

        } catch (Exception $e) {
            $this->errorMessage = 'Error loading cached data: '.$e->getMessage();
            Log::error('RawJsonViewer: Failed to load cached data', [
                'cache_key' => $this->cacheKey,
                'error' => $e->getMessage(),
            ]);
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.tools.api-explorer.raw-json-viewer');
    }
}
