<?php

namespace App\Livewire\Tools\ApiExplorer;

use App\Services\WfmService;
use Exception;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

abstract class BaseApiEndpoint extends Component
{
    // Common properties
    public string $inputMode = 'form'; // 'form' or 'json'

    public ?array $apiResponse = null;

    public ?string $errorMessage = null;

    public bool $isLoading = false;

    public bool $isAuthenticated = false;

    public string $hostname = '';

    // JSON input (can be overridden by child classes for specific validation)
    #[Validate('nullable|json')]
    public string $jsonInput = '';

    protected WfmService $wfmService;

    public function boot(WfmService $wfmService)
    {
        $this->wfmService = $wfmService;
        $this->setupAuthenticationFromSession();
    }

    protected function setupAuthenticationFromSession(): void
    {
        if (session('wfm_authenticated') && session('wfm_access_token')) {
            $this->isAuthenticated = true;

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
        $this->setupAuthenticationFromSession();
        $this->initializeEndpoint();
    }

    /**
     * Called after mount to allow child classes to perform initialization
     */
    protected function initializeEndpoint(): void
    {
        // Override in child classes if needed
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
            if (empty($this->jsonInput)) {
                $this->errorMessage = null;
                $this->dispatch('json-validated', ['message' => 'Empty JSON is valid for this endpoint']);

                return;
            }

            $data = json_decode($this->jsonInput, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON: '.json_last_error_msg());
            }

            // Allow child classes to perform additional validation
            $this->validateJsonData($data);

            $this->errorMessage = null;
            $this->dispatch('json-validated', ['message' => 'JSON is valid!']);
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    /**
     * Override in child classes for endpoint-specific JSON validation
     */
    protected function validateJsonData($data): void
    {
        // Base implementation - can be overridden
    }

    /**
     * Method to set placeholder in all child classes that extend this
     */
    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="flex items-center justify-center h-12 mx-auto w-full">
            <!-- Loading spinner... -->
            <flux:icon.loading class="w-6 h-6 text-gray-400 animate-spin" />
        </div>
        HTML;
    }

    protected function executeApiCall(): void
    {
        $this->setupAuthenticationFromSession();

        if (! $this->isAuthenticated) {
            $this->errorMessage = 'Please authenticate first using the credentials form above.';

            return;
        }

        if (! $this->wfmService) {
            $this->errorMessage = 'WFM Service not available.';

            return;
        }

        if (! empty($this->hostname)) {
            $this->wfmService->setHostname($this->hostname);
        }

        $this->isLoading = true;
        $this->errorMessage = null;
        $this->apiResponse = null;

        try {
            $this->validate();

            // Let child class handle the actual API call
            $response = $this->makeApiCall();

            if ($response) {
                $this->apiResponse = [
                    'status' => $response->status(),
                    'data' => $response->json(),
                ];

                if (! $response->successful()) {
                    $errorData = $response->json();
                    $this->errorMessage = "API Error {$response->status()}: ".
                        ($errorData['message'] ?? $errorData['error'] ?? 'Unknown error');
                } else {
                    $this->handleSuccessfulResponse($response);
                }

                // Allow child classes to process the response for table data, etc.
                $this->processApiResponse($response);
            }
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        } catch (Throwable $e) {
            $this->errorMessage = 'An unexpected error occurred: '.$e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Child classes must implement this to make their specific API call
     */
    abstract protected function makeApiCall();

    /**
     * Override in child classes to handle successful responses
     */
    protected function handleSuccessfulResponse($response): void
    {
        // Base implementation - can be overridden
    }

    /**
     * Override in child classes to process API response for additional data extraction
     * (e.g., extracting table data, formatting response data, etc.)
     */
    protected function processApiResponse($response): void
    {
        // Base implementation - can be overridden by child classes
    }

    /**
     * Helper method to decode and validate JSON input
     *
     * @throws Exception
     */
    protected function getJsonData()
    {
        if (empty($this->jsonInput)) {
            return null;
        }

        $data = json_decode($this->jsonInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON: '.json_last_error_msg());
        }

        return $data;
    }
}
