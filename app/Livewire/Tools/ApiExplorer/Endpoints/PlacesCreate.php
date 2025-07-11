<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use Exception;
use Illuminate\Http\Client\Response;
use Livewire\Attributes\Validate;

class PlacesCreate extends BaseApiEndpoint
{
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
    }

    /**
     * This is a creation endpoint, so it doesn't fetch data
     */
    protected function fetchData(): ?Response
    {
        return null;
    }

    /**
     * Override executeRequest for creation logic
     */
    public function executeRequest(): void
    {
        $this->createKnownPlace();
    }

    /**
     * Create a known place using the API
     */
    public function createKnownPlace(): void
    {
        if (! $this->isAuthenticated) {
            $this->errorMessage = 'Please authenticate first using the credentials form above.';

            return;
        }

        $this->isLoading = true;
        $this->errorMessage = null;

        try {
            // Validate input based on mode
            if ($this->inputMode === 'json') {
                $this->validate(['jsonInput' => 'required|json']);
            } else {
                $this->validate();
            }

            // Prepare and make API call
            $placeData = $this->preparePlaceData();

            $response = $this->makeAuthenticatedApiCall(function () use ($placeData) {
                return $this->wfmService->createKnownPlace($placeData);
            });

            if ($response && $response->successful()) {
                $this->handleSuccessfulResponse($response);

                // Set success response
                $this->apiResponse = [
                    'status' => $response->status(),
                    'data' => [
                        'message' => 'Known place created successfully',
                        'created_place' => $placeData['name'] ?? 'Unknown',
                        'response' => $response->json(),
                    ],
                ];
            }

        } catch (Exception $e) {
            $this->handleError($e);
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Prepare place data for API call
     *
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

        // Form mode - get next available ID
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

    /**
     * Handle successful API response
     */
    protected function handleSuccessfulResponse($response): void
    {
        if ($this->inputMode === 'form') {
            $this->reset(['name', 'description', 'latitude', 'longitude', 'radius', 'accuracy']);
            $this->loadFormSample();
        }
    }

    /**
     * Load sample form data
     */
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
