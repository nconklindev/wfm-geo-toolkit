<?php

namespace App\Livewire;

use App\Models\BusinessStructureNode;
use App\Models\KnownPlace;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;

// <-- Import AuthorizationException

class Search extends Component
{
    #[Validate('nullable|string|max:100')] // Allow empty search, add max length
    public string $searchQuery = '';

    // Initialize results as an empty collection
    public Collection $results;

    // Use mount to initialize the collection properly
    public function mount(): void
    {
        $this->results = new Collection();
    }

    /**
     * Redirects to the detail page of the selected search result after authorization.
     *
     * @param  string  $modelType  The class basename (e.g., 'KnownPlace', 'BusinessStructureNode')
     * @param  int  $id  The ID of the model instance
     *
     * @return void
     * @throws ModelNotFoundException|AuthorizationException|Exception
     */
    public function openResult(string $modelType, int $id): void
    {
        try {
            $modelClass = 'App\\Models\\'.$modelType;

            if (!class_exists($modelClass)) {
                throw new Exception("Unsupported model type: $modelType");
            }

            $item = $modelClass::findOrFail($id);

            // Authorize the action
            $this->authorize('view', $item);

            // Define the route parameters
            $routeParameters = [];
            $routeName = match ($modelType) {
                'KnownPlace' => 'known-places.show',
                'BusinessStructureNode' => 'locations.show', // Adjust if needed
                default => throw new Exception("Unsupported model type for routing: $modelType"),
            };

            $parameterName = match ($modelType) {
                'KnownPlace' => 'knownPlace', // Use the exact name from the route definition
                'BusinessStructureNode' => 'node',
                default => strtolower(Str::camel($modelType)), // Fallback (might need adjustment for other types)
            };

            $routeParameters[$parameterName] = $id; // Use the determined parameter name and the ID

            $this->resetSearch();

            $this->redirectRoute($routeName, $routeParameters, navigate: true);

        } catch (ModelNotFoundException) {
            Log::warning("Attempted to open non-existent item.",
                ['modelType' => $modelType, 'id' => $id, 'user_id' => auth()->id()]);
            $this->dispatch('notify', message: 'Error: The selected item could not be found.', type: 'error');
            $this->resetSearch();
        } catch (AuthorizationException) { // <-- Catch specific exception
            Log::warning("Authorization denied for opening search result.",
                ['modelType' => $modelType, 'id' => $id, 'user_id' => auth()->id()]);
            $this->dispatch('notify', message: 'Error: You do not have permission to view this item.', type: 'error');
            $this->resetSearch();
        } catch (Exception $e) {
            // Check if it's a missing parameter error specifically
            if (str_contains($e->getMessage(), 'Missing required parameter')) {
                Log::error("Error opening search result: Missing route parameter.", [
                    'modelType' => $modelType,
                    'id' => $id,
                    'user_id' => auth()->id(),
                    'routeName' => $routeName ?? 'unknown',
                    'calculatedParamName' => $parameterName ?? 'unknown',
                    'exception' => $e->getMessage() // Log only the message for brevity
                ]);
            } else {
                // General error handling
                Log::error("Error opening search result: {$e->getMessage()}",
                    ['modelType' => $modelType, 'id' => $id, 'user_id' => auth()->id(), 'exception' => $e]);
            }
            $this->resetSearch();
        }
    }

    public function render(): View
    {
        return view('livewire.search');
    }

    public function updatedSearchQuery(): void
    {
        $this->validateOnly('searchQuery'); // Validate just the query

        if (empty(trim($this->searchQuery))) {
            $this->resetSearch(); // Clear results if the query is empty
            return;
        }

        // --- Search Multiple Models ---
        // Ensure both models have a 'user_id' check and a 'search' scope or similar functionality

        // Limit results per model if needed (e.g., ->limit(5))
        $knownPlaces = KnownPlace::search($this->searchQuery)
            ->where('user_id', auth()->user()->id)
            ->query(fn(Builder $query) => $query->with('group'))
            ->get();

        $locations = BusinessStructureNode::search($this->searchQuery)
            ->where('user_id', auth()->user()->id) // Basic name search example
            ->get();

        // Merge the results
        $this->results = $knownPlaces->merge($locations);

        // Sort the merged results if desired (e.g., by name)
        $this->results = $this->results->sortBy('name')->values();
    }

    public function resetSearch(): void
    {
        $this->reset('searchQuery');
        $this->results = new Collection(); // Also clear results
    }
}
