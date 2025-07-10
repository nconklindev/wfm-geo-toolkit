<?php

namespace App\Livewire\Tools\ApiExplorer;

use App\Services\WfmService;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
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

                    // Use the child class's implementation to extract record count
                    $recordCount = $this->extractRecordCount($response);

                    //                    Log::debug('DEBUG: BaseApiEndpoint record count extracted', [
                    //                        'component' => get_class($this),
                    //                        'record_count' => $recordCount,
                    //                    ]);

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
     * Extract record count from response - can be overridden by child classes
     */
    protected function extractRecordCount($response): int
    {
        $data = $response->json();

        // Default implementation - checks for 'records' key first, then falls back to array count
        if (isset($data['records']) && is_array($data['records'])) {
            return count($data['records']);
        }

        // If the data itself is an array, count it
        if (is_array($data)) {
            return count($data);
        }

        // If there's a record_count field in the response, use it
        if (isset($data['record_count']) && is_numeric($data['record_count'])) {
            return (int) $data['record_count'];
        }

        return 0;
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

    /**
     * Make an authenticated API call with intelligent retry logic for rate limits
     */
    protected function makeAuthenticatedApiCallWithRetry(callable $apiCallFunction, int $maxRetries = 1): ?Response
    {
        if (! $this->isAuthenticated) {
            return null;
        }

        $attempt = 0;

        while ($attempt <= $maxRetries) {
            try {
                $response = $apiCallFunction();

                if (! $this->validateAuthenticationState($response)) {
                    return null;
                }

                // If successful, return the response
                if ($response && $response->successful()) {
                    return $response;
                }

                // If not successful, analyze the error
                if ($response) {
                    $responseData = $response->json();
                    $errorAnalysis = $this->analyzeApiError($responseData);

                    // If it's a retryable error, and we have retries left
                    if ($errorAnalysis['should_retry'] && $attempt < $maxRetries) {
                        $attempt++;

                        Log::info('Retrying API call', [
                            'attempt' => $attempt,
                            'max_retries' => $maxRetries,
                            'error_type' => $errorAnalysis['type'],
                            'component' => get_class($this),
                        ]);

                        // Add a small delay before retry
                        sleep(1); // 1-second delay

                        continue;
                    }

                    // Set the error message from analysis
                    $this->errorMessage = $errorAnalysis['user_message'];

                    Log::error('API call failed after retries', [
                        'error_type' => $errorAnalysis['type'],
                        'attempts' => $attempt + 1,
                        'component' => get_class($this),
                        'hostname' => $this->hostname,
                    ]);
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

        return null;
    }

    /**
     * Analyze API error response and return error details with extracted limits
     */
    protected function analyzeApiError($responseData): array
    {
        if (! is_array($responseData)) {
            return [
                'type' => 'unknown',
                'user_message' => 'Unknown error occurred',
                'max_count' => null,
                'should_retry' => false,
            ];
        }

        $message = strtolower($responseData['message'] ?? '');
        $errorCode = strtolower($responseData['errorCode'] ?? '');
        $fullText = $message.' '.$errorCode;

        // System/Rate limit errors
        $limitKeywords = ['limit', 'exceeded', 'maximum', 'max', 'count', 'system limit', 'too many', 'quota', 'rate limit'];
        foreach ($limitKeywords as $keyword) {
            if (str_contains($fullText, $keyword)) {
                $maxCount = $this->extractMaxCountFromError($responseData);
                $userMessage = $maxCount
                    ? "API limit exceeded. This tenant has a maximum record limit of $maxCount per request. Please use filters to reduce the dataset size, if possible."
                    : 'API limit exceeded. This tenant has restrictions on the number of records that can be requested. Please use filters to reduce the dataset size, if possible.';

                return [
                    'type' => 'limit_exceeded',
                    'user_message' => $userMessage,
                    'max_count' => $maxCount,
                    'should_retry' => $maxCount !== null, // Retry if we can extract a limit
                ];
            }
        }

        // Authentication/Permission errors
        $authKeywords = ['unauthorized', 'forbidden', 'permission', 'access denied', 'invalid token', 'expired'];
        foreach ($authKeywords as $keyword) {
            if (str_contains($fullText, $keyword)) {
                return [
                    'type' => 'authentication',
                    'user_message' => 'Authentication or permission error. Please check your credentials and try again.',
                    'max_count' => null,
                    'should_retry' => false,
                ];
            }
        }

        // Timeout errors
        $timeoutKeywords = ['timeout', 'timed out', 'connection timeout', 'request timeout'];
        foreach ($timeoutKeywords as $keyword) {
            if (str_contains($fullText, $keyword)) {
                return [
                    'type' => 'timeout',
                    'user_message' => 'Request timed out. The server took too long to respond. Please try again or reduce your request size.',
                    'max_count' => null,
                    'should_retry' => true,
                ];
            }
        }

        // Server errors
        $serverKeywords = ['server error', 'internal error', 'service unavailable', 'bad gateway'];
        foreach ($serverKeywords as $keyword) {
            if (str_contains($fullText, $keyword)) {
                return [
                    'type' => 'server_error',
                    'user_message' => 'Server error occurred. Please try again later or contact support if the issue persists.',
                    'max_count' => null,
                    'should_retry' => true,
                ];
            }
        }

        // Default fallback
        return [
            'type' => 'generic',
            'user_message' => 'API error: '.($responseData['message'] ?? 'Unknown error occurred'),
            'max_count' => null,
            'should_retry' => false,
        ];
    }

    /**
     * Extract the maximum count limit from the API error message
     */
    protected function extractMaxCountFromError(array $responseData): ?int
    {
        $message = $responseData['message'] ?? '';

        // Look for patterns like "max count: 1000" or "maximum: 500" or "limit of 2000"
        $patterns = [
            '/max count:\s*(\d+)/i',
            '/maximum:\s*(\d+)/i',
            '/limit of\s*(\d+)/i',
            '/max:\s*(\d+)/i',
            '/limit:\s*(\d+)/i',
            '/maximum count:\s*(\d+)/i',
            '/count limit:\s*(\d+)/i',
            '/records limit:\s*(\d+)/i',
            '/record limit:\s*(\d+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                return (int) $matches[1];
            }
        }

        return null;
    }
}
