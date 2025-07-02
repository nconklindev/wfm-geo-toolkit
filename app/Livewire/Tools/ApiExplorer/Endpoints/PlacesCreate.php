<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Services\WfmService;
use Exception;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Log;
use Throwable;

class PlacesCreate extends Component
{
    // Input mode toggle
    public string $inputMode = 'form'; // 'form' or 'json'

    // JSON mode
    #[Validate('required_if:inputMode,json|json')]
    public string $placesJson = '';

    // Form mode - single place
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
        'validationOrder.*' => [
            'required',
            'in:GPS,WIFI',
        ],
    ])]
    public array $validationOrder = [];

    // Response data
    public ?array $apiResponse = null;

    public ?string $errorMessage = null;

    public bool $isLoading = false;

    // Props passed from parent
    public bool $isAuthenticated = false;

    public string $hostname = '';

    protected WfmService $wfmService;

    public function boot(WfmService $wfmService)
    {
        $this->wfmService = $wfmService;

        // Set up authentication from session if available
        $this->setupAuthenticationFromSession();
    }

    private function setupAuthenticationFromSession(): void
    {
        // Get authentication state from session
        if (session('wfm_authenticated') && session('wfm_access_token')) {
            $this->isAuthenticated = true;

            // Set up the WfmService with session data
            if ($this->wfmService) {
                $this->wfmService->setAccessToken(session('wfm_access_token'));

                if (! empty($this->hostname)) {
                    $this->wfmService->setHostname($this->hostname);
                } elseif (session('wfm_credentials.hostname')) {
                    $this->wfmService->setHostname(session('wfm_credentials.hostname'));
                    $this->hostname = session('wfm_credentials.hostname');
                }
            }
        } else {
            $this->isAuthenticated = false;
        }
    }

    public function mount(bool $isAuthenticated = false, string $hostname = '')
    {
        $this->isAuthenticated = $isAuthenticated;
        $this->hostname = $hostname;
        $this->validationOrder = ['GPS'];

        // Set up the WfmService from session data
        $this->setupAuthenticationFromSession();

        // Generate sample data on mount
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

        $this->placesJson = json_encode($sampleData, JSON_PRETTY_PRINT);
        $this->resetValidation();
    }

    public function switchInputMode(string $mode): void
    {
        if (in_array($mode, ['form', 'json'])) {
            $this->inputMode = $mode;
            $this->resetErrorBag();
            $this->resetValidation();
        }
    }

    public function validateJson(): void
    {
        try {
            $data = json_decode($this->placesJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON: '.json_last_error_msg());
            }

            if (! is_array($data)) {
                throw new Exception('Data must be an array');
            }

            $this->errorMessage = null;
            $this->dispatch('json-validated', ['message' => 'JSON is valid!']);

        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function createKnownPlace(): void
    {
        // Always refresh authentication state from session before making API calls
        $this->setupAuthenticationFromSession();

        if (! $this->isAuthenticated) {
            $this->errorMessage = 'Please authenticate first using the credentials form above.';

            return;
        }

        if (! $this->wfmService) {
            $this->errorMessage = 'WFM Service not available.';

            return;
        }

        // Ensure hostname is set on the service
        if (! empty($this->hostname)) {
            $this->wfmService->setHostname($this->hostname);
        }

        $this->isLoading = true;
        $this->errorMessage = null;
        $this->apiResponse = null;
        $warningMessage = null; // Track warnings separately from errors

        try {
            // Validate the form using Livewire's built-in validation
            $this->validate();

            $placeData = [];

            if ($this->inputMode === 'json') {
                // Decode and basic validation for JSON
                $data = json_decode($this->placesJson, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON: '.json_last_error_msg());
                }

                if (! is_array($data)) {
                    throw new Exception('Data must be an object or array');
                }

                // API expects a single place object, not an array
                // If user provides an array, take the first item
                if (isset($data[0]) && is_array($data[0])) {
                    $placeData = $data[0];
                    Log::info('Taking first item from JSON array');
                } else {
                    // User provided a single object
                    $placeData = $data;
                }

            } else {
                // Form mode - create single place
                // Get existing places to determine next ID
                $wfmPlaces = $this->wfmService->getKnownPlaces();

                if (empty($wfmPlaces)) {
                    $token = $this->wfmService->getAccessToken();
                    if (empty($token)) {
                        throw new Exception('No access token available. Please re-authenticate.');
                    }

                    if (empty($this->hostname)) {
                        throw new Exception('No hostname configured for API calls.');
                    }

                    throw new Exception('Failed to retrieve existing known places from WFM. This could be due to API permissions, network issues, or the WFM instance not having any places yet.');
                }

                $placeIds = $this->wfmService->extractPlaceIds($wfmPlaces);
                $nextId = $this->wfmService->getNextAvailableId($placeIds);

                $placeData = [
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

            // Make the API call using the service's token
            $response = $this->wfmService->createKnownPlace($placeData);

            $this->apiResponse = [
                'status' => $response->status(),
                'data' => $response->json(),
            ];

            if (! $response->successful()) {
                $errorData = $response->json();
                $this->errorMessage = "API Error {$response->status()}: ".
                    ($errorData['message'] ?? $errorData['error'] ?? 'Unknown error');
            } else {
                // Success! Only show warning message if we have one, not error
                if ($warningMessage) {
                    $this->errorMessage = $warningMessage; // This is actually a warning, not an error
                }

                if ($this->inputMode === 'form') {
                    // Clear form on success
                    $this->reset(['name', 'description', 'latitude', 'longitude', 'radius', 'accuracy']);
                    $this->loadFormSample(); // Load sample data for next entry
                }
            }

        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        } catch (Throwable $e) {
            $this->errorMessage = 'An unexpected error occurred: '.$e->getMessage();
        } finally {
            $this->isLoading = false;
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

    public function render()
    {
        return view('livewire.tools.api-explorer.endpoints.places-create');
    }
}
