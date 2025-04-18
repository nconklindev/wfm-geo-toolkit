<?php

namespace App\Livewire;

use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LocationInput extends Component
{

    #[Validate([
        'string',
        'regex:/^[a-zA-Z0-9\s\-_&(),]+(\/[a-zA-Z0-9\s\-_&(),]+)*$/'
    ], message: [
        'regex' => 'Invalid location path.'
    ])]
    public string $currentLocation = '';
    public ?Collection $types = null;
    public array $savedLocations = []; // TODO: We need to somehow send this when the form submits

    public function mount($types): void
    {
        $this->types = $types;
    }

    public function addLocationToList(): void
    {
        $this->validate();
        
        if (!empty(trim($this->currentLocation))) {
            $nodes = $this->parseLocationPath($this->currentLocation);

            // Validate that the number of nodes doesn't exceed the number of types
            if (count($nodes) > $this->types->count()) {
                $this->addError('currentLocation', 'You have entered more location nodes than available types.');
            }

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
        // Split on slash
        $nodes = explode('/', $locationPath);

        // Trim each node
        $nodes = array_map('trim', $nodes);

        // Filter out empty nodes
        return array_filter($nodes, fn($node) => !empty($node));
    }

    public function render()
    {
        return view('livewire.location-input');
    }

}
