<?php

namespace App\Livewire\Tools;

use App\Point;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;


class Plotter extends Component
{
    // Explicitly set the between values as floats to prevent the validation from failing
    #[Validate('required|decimal:0,10|between:-90.00,90.00')]
    public float $latitude;
    #[Validate('required|decimal:0,10|between:-180.00,180.00')]
    public float $longitude;
    #[Validate('required|integer|between:1,1000')]
    public int $radius;
    #[Validate('required|integer|between:1,1000')]
    public int $accuracy;
    #[Validate('nullable|string|max:255')]
    public ?string $label;
    #[Validate('nullable|hex_color')]
    public string $color = "#3b82f6";

    /**
     * @var Point[]
     */
    public array $points = [];

    public function addPoint(): void
    {
        $this->validate();
        $point = new Point(
            latitude: $this->latitude,
            longitude: $this->longitude,
            label: $this->label ?? '',
            radius: $this->radius,
            accuracy: $this->accuracy,
            color: $this->color
        );

        $this->points[] = $point;

        // Dispatch event with the new point to update the map
        $this->dispatch('points-updated', $this->formatPointsForMap());

        $this->reset('latitude', 'longitude', 'label', 'radius', 'accuracy', 'color');
        $this->color = "#3b82f6";
    }

    /**
     * Format points for the map
     */
    private function formatPointsForMap(): array
    {
        // Ensure this formats the points correctly for the JS update function
        return array_map(function ($index, $point) {
            // Ensure Point properties are accessed correctly
            if (!$point instanceof Point) {
                return null;
            } // Basic safety check

            return [
                'id' => $index,
                'latitude' => $point->latitude,
                'longitude' => $point->longitude,
                'label' => $point->label,
                'radius' => $point->radius,
                'accuracy' => $point->accuracy, // Assuming Point has accuracy
                'color' => $point->color // Assuming Point has color
            ];
        }, array_keys($this->points), $this->points);

    }

    public function clearAllPoints(): void
    {
        $this->points = [];
        $this->dispatch('points-updated', []);
    }

    public function flyTo(int $index): void
    {
        if (isset($this->points[$index])) {
            $point = $this->points[$index];
            // Dispatch a browser event with the coordinates to fly to
            $this->dispatch('fly-to-point', [
                'latitude' => $point->latitude,
                'longitude' => $point->longitude,
                'radius' => $point->radius,
            ]);
        }
    }

    public function mount(): void
    {
        $this->points = [];
        $this->color = "#3b82f6";
    }

    public function removePoint(int $index): void
    {
        if (isset($this->points[$index])) {
            unset($this->points[$index]);
            $this->points = array_values($this->points); // Re-index the array

            // Inform the map that points have changed - need to refresh all points
            $this->dispatch('points-updated', $this->formatPointsForMap());
        }
    }

    #[Layout('components.layouts.guest')]
    #[Title('Plotter | WFM Geo Toolkit')]
    public function render(): View
    {
        // Send all points to the view to initialize the map with existing points
        return view('livewire.tools.plotter', [
            'mapPoints' => $this->formatPointsForMap()
        ]);
    }

    /**
     * Update the coordinates when received from the AddressSearch component
     *
     * @param  array  $data
     */
    #[On('coordinates-updated')]
    public function updateCoordinates(array $data): void
    {
        Log::debug('Received coordinates from AddressSearch component: '.json_encode($data));
        if (isset($data['latitude'], $data['longitude'])) {
            $this->latitude = $data['latitude'];
            $this->longitude = $data['longitude'];
            $this->label = $data['formatted_address'];
        }
    }
}
