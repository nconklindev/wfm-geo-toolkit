<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\Geocoder\Facades\Geocoder;

class AddressSearch extends Component
{
    #[Validate(['string', 'min:3', 'max:255', 'regex:/^[a-zA-Z0-9\s\-\,\.\(\)\/]+$/'])]
    public string $address = '';

    public $lat = null;

    public $lng = null;

    public function search(string $address): void
    {

        $result = Geocoder::getCoordinatesForAddress($address);

        // Set the latitude and longitude properties
        if ($result && isset($result['lat'], $result['lng'])) {
            $this->lat = $result['lat'];
            $this->lng = $result['lng'];
        }

        // Since Spatie Geocoder isn't able to return suggestions, this is the next best thing
        // We'll just not update anything on the form, relying on the absence of results to indicate that nothing was found
        if ($result['formatted_address'] === 'result_not_found') {
            //            return [];
        }

        $coordinateData = [
            'latitude' => $this->lat,
            'longitude' => $this->lng,
            'formatted_address' => $result['formatted_address'],
        ];

        //        Log::debug("Dispatching coordinates-updated with data: ".json_encode($coordinateData));

        // Use a CONSISTENT format for both the browser and Livewire events
        // Dispatch as an object directly, not wrapped in an array
        $this->dispatch('coordinates-updated', $coordinateData);

        //        return $result;
    }

    public function render(): View
    {
        return view('livewire.address-search');
    }
}
