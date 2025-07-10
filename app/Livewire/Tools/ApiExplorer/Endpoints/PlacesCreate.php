<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Livewire\Attributes\Validate;

class PlacesCreate extends BaseApiEndpoint
{
    // Rename for consistency with base class
    #[Validate('required_if:inputMode,json|json')]
    public string $jsonInput = '';

    // Form-specific properties
    #[Validate('required_if:inputMode,form|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:500')]
    public string $description = '';

    #[Validate('required_if:inputMode,form|numeric|between:-90,90')]
    public string $latitude = '';

    #[Validate('required_if:inputMode,form|numeric|between:-180,180')]
    public string $longitude = '';

    #[Validate('required_if:inputMode,form|integer|min:1|max:10000')]
    public string $radius = '';

    #[Validate('required_if:inputMode,form|integer|min:1|max:10000')]
    public string $accuracy = '100';

    #[Validate([
        'array',
        'required_if:inputMode,form',
        'validationOrder.*' => ['required', 'in:GPS,WIFI'],
    ])]
    public array $validationOrder = [];

    public function render()
    {
        return view('livewire.tools.api-explorer.endpoints.places-create');
    }

    protected function initializeEndpoint(): void
    {
        $this->validationOrder = ['GPS'];
        $this->generateSampleData();
    }

    public function generateSampleData(): void
    {
        $sampleData = [
            [
                'name' => 'Warehouse',
                'description' => 'Distribution center',
                'latitude' => 40.7589,
                'longitude' => -73.9851,
                'radius' => 150,
                'accuracy' => 100,
                'active' => true,
                'validationOrder' => ['GPS', 'WIFI'],
            ],
        ];

        $this->jsonInput = json_encode($sampleData, JSON_PRETTY_PRINT);
        $this->resetValidation();
    }

    protected function validateJsonData($data): void
    {
        if (! is_array($data)) {
            throw new Exception('Data must be an array');
        }
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    protected function makeApiCall()
    {
        $placeData = $this->preparePlaceData();

        return $this->wfmService->createKnownPlace($placeData);
    }

    /**
     * @throws Exception
     */
    private function preparePlaceData(): array
    {
        if ($this->inputMode === 'json') {
            $data = $this->getJsonData();

            // API expects a single place object, not an array
            if (isset($data[0]) && is_array($data[0])) {
                return $data[0];
            }

            return $data;
        }

        // Form mode
        $wfmPlaces = $this->wfmService->getKnownPlaces();
        if (empty($wfmPlaces)) {
            throw new Exception('Failed to retrieve existing known places from WFM.');
        }

        $placeIds = $this->wfmService->extractPlaceIds($wfmPlaces);
        $nextId = $this->wfmService->getNextAvailableId($placeIds);

        return [
            'id' => $nextId,
            'name' => $this->name,
            'description' => $this->description ?: '',
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'radius' => (int) $this->radius,
            'accuracy' => (int) $this->accuracy,
            'active' => true,
            'validationOrder' => array_map('strtoupper', $this->validationOrder) ?: ['GPS'],
        ];
    }

    public function createKnownPlace(): void
    {
        $this->executeApiCall();
    }

    protected function handleSuccessfulResponse($response): void
    {
        if ($this->inputMode === 'form') {
            $this->reset(['name', 'description', 'latitude', 'longitude', 'radius', 'accuracy']);
            $this->loadFormSample();
        }
    }

    public function loadFormSample(): void
    {
        $this->name = 'Main Office';
        $this->description = 'Corporate headquarters';
        $this->latitude = '40.7128';
        $this->longitude = '-74.0060';
        $this->radius = '100';
        $this->accuracy = '100';
        $this->resetValidation();
    }
}
