<?php

namespace App\Livewire;

use Livewire\Attributes\Validate;
use Livewire\Component;

class LocationInput extends Component
{
    #[Validate([
        'string',
        // Simple regex allowing alphanumeric, space, hyphen, underscore, ampersand, comma, parentheses, and forward slash
        'regex:/^[a-zA-Z0-9\s\-_&(),\/]+$/',
    ], message: [
        'regex' => 'Invalid characters in location path. Only letters, numbers, spaces, slashes (/), hyphens (-), underscores (_), ampersands (&), commas (,), and parentheses () are allowed.',
    ])]
    public string $currentLocation = '';

    /** @var array<int, array<int, string>> */
    public array $locations = [];

    /**
     * Initialise the component.
     *
     * @param  array  $existingLocations  Pre-populate with existing locations if editing. Should be in the format [['Seg1', 'Seg2'], ['Path2']].
     */
    public function mount(array $assignedLocations = []): void
    {
        // Initialize with existing data if provided (e.g., for edit forms)
        $this->locations = $assignedLocations;
    }

    /**
     * Validate the current location input, parse it,
     * prevent duplicates, and add it to the list.
     */
    public function addLocationToList(): void
    {
        // Trim input before validation
        $this->currentLocation = trim($this->currentLocation);

        // Validate the raw input string format
        $this->validate();

        if (! empty($this->currentLocation)) {
            $nodes = $this->parseLocationPath($this->currentLocation);

            // Check if parsing resulted in any valid nodes
            if (empty($nodes)) {
                $this->addError('currentLocation', 'Location path cannot be empty or contain only slashes.');

                return;
            }

            // Check for duplicates (compare the array of nodes)
            foreach ($this->locations as $existingLocation) {
                // Ensure both arrays have the same number of elements and the same values in the same order
                if ($existingLocation === $nodes) {
                    $this->addError('currentLocation', 'This location path has already been added.');

                    return; // Stop processing if duplicate found
                }
            }

            // Add the array of nodes (segments) to locations
            $this->locations[] = $nodes;
            $this->reset('currentLocation');
            $this->resetErrorBag('currentLocation'); // Clear specific error after a successful addition

        } else {
            // Handle case where input becomes empty after trimming
            $this->addError('currentLocation', 'Location path cannot be empty.');
        }
    }

    /**
     * Parse a location path string into an array of nodes (segments).
     *
     * @return array<int, string> Returns an array of non-empty, trimmed path segments.
     */
    private function parseLocationPath(string $locationPath): array
    {
        // Split on slash
        $nodes = explode('/', $locationPath);

        // Trim whitespace from each node
        $nodes = array_map('trim', $nodes);

        // Filter out any potentially empty nodes resulting from multiple slashes (e.g., "Acme//NC") or leading/trailing slashes
        return array_values(array_filter($nodes, fn ($node) => $node !== ''));
    }

    /**
     * Remove a location from the list by its index.
     */
    public function removeLocation(int $index): void
    {
        if (isset($this->locations[$index])) {
            unset($this->locations[$index]);
            // Re-index the array numerically to avoid issues with loops/keys after removal
            $this->locations = array_values($this->locations);
        }
    }

    public function render()
    {
        return view('livewire.location-input');
    }
}
