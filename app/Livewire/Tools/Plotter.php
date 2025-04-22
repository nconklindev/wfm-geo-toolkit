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
    #[Validate('required|decimal:2,10|between:-90.00,90.00')]
    public float $latitude;
    #[Validate('required|decimal:2,10|between:-180.00,180.00')]
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


    public function addPoint(): void
    {
        $this->validate();
        $this->points[] = new Point(
            latitude: $this->latitude,
            longitude: $this->longitude,
            label: $this->label ?? '',
            radius: $this->radius,
            accuracy: $this->accuracy,
            color: $this->color
        );
//        dd($this->points);

        $this->reset('latitude', 'longitude', 'label', 'radius', 'accuracy', 'color');
    }

    public function removePoint(int $index): void
    {
        if (isset($this->points[$index])) {
            unset($this->points[$index]);
            $this->points = array_values($this->points); // Re-index the array
        }
    }

    #[Layout('components.layouts.guest')]
    #[Title('Plotter | WFM Geo Toolkit')]
    public function render(): View
    {
        return view('livewire.tools.plotter');
    }
}
