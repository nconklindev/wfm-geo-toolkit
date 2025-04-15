<?php

namespace App\Livewire;

use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LocationInput extends Component
{

    #[Validate('string')]
    public string $currentLocation = '';
    public ?Collection $types = null;
    public array $savedLocations = []; // TODO: We need to somehow send this when the form submits

    public function mount($types): void
    {
        $this->types = $types;
    }

    public function addLocationToList(): void
    {
        if (!empty(trim($this->currentLocation))) {
            $nodes = $this->parseLocationPath($this->currentLocation);

            // Add the array of nodes to savedLocations
            $this->savedLocations[] = array_values($nodes);
            $this->reset('currentLocation');
        }
    }

    /**
     * Parse a location path string into an array of nodes, handling escaped slashes
     *
     * @param  string  $locationPath
     * @return array
     */
    private function parseLocationPath(string $locationPath): array
    {
        // Handle escaped slashes - replace them temporarily, then split, then restore
        $escapedLocation = preg_replace('/\\\\\\//', '{{ESCAPED_SLASH}}', $locationPath);

        // Split on slash
        $nodes = explode('/', $escapedLocation);

        // Clean up each node
        $nodes = array_map(function ($node) {
            // Restore escaped slashes
            return trim(str_replace('{{ESCAPED_SLASH}}', '/', $node));
        }, $nodes);

        // Filter out empty nodes
        return array_filter($nodes, fn($node) => !empty($node));
    }

    public function render()
    {
        return view('livewire.location-input');
    }

}
