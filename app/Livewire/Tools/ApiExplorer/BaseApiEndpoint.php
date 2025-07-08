<?php

namespace App\Livewire\Tools\ApiExplorer;

use App\Services\WfmService;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Log;
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

    // Store response metadata without full data
    public ?array $responseMetadata = null;

    // Store the cache key for raw JSON viewer
    public string $rawJsonCacheKey = '';

    protected WfmService $wfmService;

    public function boot(WfmService $wfmService): void
    {
        $this->wfmService = $wfmService;
        $this->setupAuthenticationFromSession();
    }

    protected function setupAuthenticationFromSession(): void
    {
        if (session('wfm_authenticated') && session('wfm_access_token')) {
            $this->isAuthenticated = true;

            $this->wfmService->setAccessToken(session('wfm_access_token'));

            if (! empty($this->hostname)) {
                $this->wfmService->setHostname($this->hostname);
            } elseif (session('wfm_credentials.hostname')) {
                $this->wfmService->setHostname(session('wfm_credentials.hostname'));
                $this->hostname = session('wfm_credentials.hostname');
            }
        } else {
            $this->isAuthenticated = false;
        }
    }

    public function mount(bool $isAuthenticated = false, string $hostname = ''): void
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

        if (! empty($this->hostname)) {
            $this->wfmService->setHostname($this->hostname);
        }

        $this->isLoading = true;
        $this->errorMessage = null;

        try {
            $this->validate();

            // Let child class handle the actual API call
            $response = $this->makeApiCall();

            if ($response) {
                // Validate authentication state first
                if (! $this->validateAuthenticationState($response)) {
                    return; // Authentication failed, error messages already set
                }

                // Store basic response metadata
                $this->responseMetadata = [
                    'status' => $response->status(),
                    'successful' => $response->successful(),
                    'headers' => $response->headers(),
                    'timestamp' => now()->toISOString(),
                ];

                if (! $response->successful()) {
                    $errorData = $response->json();
                    $this->errorMessage = "API Error {$response->status()}: ".
                        ($errorData['message'] ?? $errorData['error'] ?? 'Unknown error');

                    // For errors, always show the response data
                    $this->apiResponse = [
                        'status' => $response->status(),
                        'data' => $errorData,
                    ];
                } else {
                    $this->handleSuccessfulResponse($response);

                    // Always show summary response - never load full data here
                    $data = $response->json();
                    $recordCount = is_array($data) ? count($data) : (isset($data['records']) ? count($data['records']) : 0);

                    $this->apiResponse = [
                        'status' => $response->status(),
                        'data' => [
                            'message' => "Data loaded successfully - $recordCount records",
                            'record_count' => $recordCount,
                            'click_to_view' => 'Click "Show Raw JSON" to view full response',
                        ],
                    ];

                    // Set the cache key for the raw JSON viewer component
                    if (method_exists($this, 'generateCacheKey')) {
                        $this->rawJsonCacheKey = $this->generateCacheKey();
                    }
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
     * Validate that the current authentication is still valid by testing it
     */
    protected function validateAuthenticationState($response): bool
    {
        // Check for authentication failure status codes
        if ($response && (
            $response->status() === 401 ||
            $response->status() === 403 ||
            ($response->status() === 400 && $this->isAuthenticationError($response))
        )) {
            $this->handleAuthenticationFailure();

            return false;
        }

        return true;
    }

    /**
     * Check if a 400 response is actually an authentication error
     */
    protected function isAuthenticationError($response): bool
    {
        $responseData = $response->json();

        if (! $responseData) {
            return false;
        }

        $errorMessage = strtolower($responseData['message'] ?? $responseData['error'] ?? '');

        return str_contains($errorMessage, 'token') ||
               str_contains($errorMessage, 'auth') ||
               str_contains($errorMessage, 'unauthorized') ||
               str_contains($errorMessage, 'forbidden') ||
               str_contains($errorMessage, 'expired');
    }

    /**
     * Handle authentication failure by clearing session and updating component state
     */
    protected function handleAuthenticationFailure(): void
    {
        // Clear session authentication data
        session()->forget(['wfm_authenticated', 'wfm_access_token']);

        // Update component state
        $this->isAuthenticated = false;

        // Set user-friendly error message
        $this->errorMessage = 'Your authentication session has expired. Please re-enter your credentials to continue.';

        // Log the authentication failure
        Log::warning('Authentication session expired', [
            'component' => get_class($this),
            'hostname' => $this->hostname,
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Override in child classes to handle successful responses
     */
    protected function handleSuccessfulResponse($response): void
    {
        // Base implementation - can be overridden
    }

    /**
     * Override in child classes to process API response for additional data extraction
     */
    protected function processApiResponse($response): void
    {
        // Base implementation - can be overridden by child classes
    }

    /**
     * Safely make an API call with authentication validation
     */
    protected function makeAuthenticatedApiCall(callable $apiCallFunction)
    {
        if (! $this->isAuthenticated) {
            return null;
        }

        try {
            $response = $apiCallFunction();

            if (! $this->validateAuthenticationState($response)) {
                return null;
            }

            return $response;
        } catch (ConnectionException $e) {
            $this->errorMessage = 'Unable to connect to API. Please check your network connection and try again.';
            Log::error('Connection error in API call', [
                'error' => $e->getMessage(),
                'component' => get_class($this),
                'hostname' => $this->hostname,
            ]);

            return null;
        }
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
