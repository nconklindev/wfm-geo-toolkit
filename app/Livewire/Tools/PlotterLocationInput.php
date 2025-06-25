<?php

namespace App\Livewire\Tools;

use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PlotterLocationInput extends Component
{
    #[Validate('nullable|string|max:255')]
    public ?string $currentLocation = '';

    #[Validate('array')]
    public array $locations = [];

    public function addLocation(): void
    {
        $this->validate(['currentLocation' => 'nullable|string|max:255']);

        if (! empty(trim($this->currentLocation))) {
            $location = trim($this->currentLocation);

            // Check for duplicates
            if (! in_array($location, $this->locations)) {
                $this->locations[] = $location;
                $this->dispatch('locations-updated', $this->locations)->to(Plotter::class);
            }

            $this->reset('currentLocation');
        }
    }

    public function removeLocation(int $index): void
    {
        if (isset($this->locations[$index])) {
            unset($this->locations[$index]);
            $this->locations = array_values($this->locations);
            $this->dispatch('locations-updated', $this->locations);
        }
    }

    #[On('reset-locations')]
    public function resetLocations(): void
    {
        $this->locations = [];
        $this->currentLocation = '';
        $this->dispatch('locations-updated', $this->locations);
    }

    public function render()
    {
        return view('livewire.tools.plotter-location-input');
    }
}
