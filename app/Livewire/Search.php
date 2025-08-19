<?php

namespace App\Livewire;

use App\Services\SearchRegistryService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Search extends Component
{
    #[Validate('nullable|string|max:100')]
    public string $searchQuery = '';

    public Collection $results;

    // Track search performance for Algolia analytics
    public ?float $searchTime = null;

    /**
     * Get the display name for a model type
     */
    public function getModelDisplayName(string $modelType): string
    {
        $config = SearchRegistryService::getModelConfig($modelType);

        return $config['display_name'] ?? class_basename($modelType);
    }

    public function mount(): void
    {
        $this->results = new Collection;
    }

    /**
     * Redirects to the detail page of the selected search result after authorization.
     */
    public function openResult(string $modelType, int $id): void
    {
        try {
            if (! SearchRegistryService::isRegistered($modelType)) {
                throw new Exception("Unsupported model type: $modelType");
            }

            $config = SearchRegistryService::getModelConfig($modelType);
            $modelClass = $config['class'];

            $item = $modelClass::findOrFail($id);

            // Simple ownership check
            if (auth()->id() !== $item->user_id) {
                throw new Exception('You do not have permission to view this item.');
            }

            $routeConfig = SearchRegistryService::getRouteConfig($modelType);

            $this->resetSearch();

            if ($routeConfig['has_parameter']) {
                $this->redirectRoute(
                    $routeConfig['route_name'],
                    [$routeConfig['route_parameter'] => $id],
                    navigate: true
                );
            } else {
                $this->redirectRoute(
                    $routeConfig['route_name'],
                    navigate: true
                );
            }

        } catch (ModelNotFoundException) {
            Log::warning('Attempted to open non-existent item.', [
                'modelType' => $modelType,
                'id' => $id,
                'user_id' => auth()->id(),
            ]);
            $this->dispatch('notify', message: 'Error: The selected item could not be found.', type: 'error');
            $this->resetSearch();
        } catch (Exception $e) {
            Log::error("Error opening search result: {$e->getMessage()}", [
                'modelType' => $modelType,
                'id' => $id,
                'user_id' => auth()->id(),
                'exception' => $e,
            ]);
            $this->dispatch('notify', message: 'Error: Unable to open the selected item.', type: 'error');
            $this->resetSearch();
        }
    }

    public function resetSearch(): void
    {
        $this->reset(['searchQuery', 'searchTime']);
        $this->results = new Collection;
    }

    public function render(): View
    {
        return view('livewire.search');
    }

    public function updatedSearchQuery(): void
    {
        $this->validateOnly('searchQuery');

        if (empty(trim($this->searchQuery))) {
            $this->resetSearch();

            return;
        }

        // Minimum query length for better Algolia performance
        if (strlen(trim($this->searchQuery)) < 2) {
            $this->results = new Collection;

            return;
        }

        try {
            $startTime = microtime(true);

            $this->results = SearchRegistryService::searchAll($this->searchQuery, auth()->id());

            $this->searchTime = microtime(true) - $startTime;

        } catch (Exception $e) {
            Log::error("Search failed: {$e->getMessage()}", [
                'query' => $this->searchQuery,
                'user_id' => auth()->id(),
                'exception' => $e,
            ]);
            $this->results = new Collection;
        }
    }
}
