<?php

namespace App\Livewire\Tools\ApiExplorer;

use App\Services\WfmService;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Log;

abstract class BaseApiEndpoint extends Component
{
    // Common properties
    public string $inputMode = 'form';

    public ?array $apiResponse = null;

    public ?string $errorMessage = null;

    public bool $isLoading = false;

    public bool $isAuthenticated = false;

    public string $hostname = '';

    // Data properties
    public int $totalRecords = 0;

    public array $tableColumns = [];

    public array $tableData = [];

    public string $cacheKey = '';

    // JSON input
    #[Validate('nullable|json')]
    public string $jsonInput = '';

    // Response metadata
    public ?array $responseMetadata = null;

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
        // 1. Set basic properties
        $this->isAuthenticated = $isAuthenticated;
        $this->hostname = $hostname;

        // 2. Check session for authentication
        $this->setupAuthenticationFromSession();

        // 3. Child class configures itself
        $this->initializeEndpoint();

        // 4. Try to restore from cache
        $this->restoreStateFromCache();
    }

    /**
     * Initialize endpoint - called after mount
     */
    protected function initializeEndpoint(): void
    {
        // Override in child classes
    }

    /**
     * Restore component state from cached data
     */
    protected function restoreStateFromCache(): void
    {
        if (empty($this->cacheKey)) {
            return;
        }

        $cachedData = cache()->get($this->cacheKey);

        if ($cachedData) {
            $data = $cachedData instanceof Collection ?
                $cachedData->toArray() :
                (is_array($cachedData) ? $cachedData : []);

            $this->tableData = $data;
            $this->totalRecords = count($data);
            $this->rawJsonCacheKey = $this->cacheKey;

            // Set a restored response to indicate data was loaded from the cache
            $this->apiResponse = [
                'status' => 200,
                'data' => [
                    'message' => "Showing cached data - $this->totalRecords records",
                    'record_count' => $this->totalRecords,
                    'click_to_view' => 'Click "Show Raw JSON" to view full response',
                    'cached' => true,
                ],
            ];
        }
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="flex items-center justify-center h-12 mx-auto w-full">
            <flux:icon.loading class="w-6 h-6 text-zinc-400 animate-spin" />
        </div>
        HTML;
    }

    /**
     * The main method called by UI - handles the complete flow
     *
     * Simple endpoints will only need to override ({@link BaseApiEndpoint::fetchData()})
     *
     * More complex endpoints should override this entire method to implement their custom implementation of this
     */
    public function executeRequest(): void
    {
        // 1. Check authentication
        if (! $this->isAuthenticated) {
            $this->errorMessage = 'Please authenticate first using the credentials form above.';

            return;
        }

        // 2. Set the loading state and clear the error message previous state
        $this->isLoading = true;
        $this->errorMessage = null;

        try {
            // Clear any existing cache
            $this->clearCache();

            // Make the API call
            $response = $this->fetchData();

            if ($response && $response->successful()) {
                // Process and store the data
                $this->processSuccessfulResponse($response);

                // Trigger pagination refresh if a trait is used
                if (method_exists($this, 'clearPaginationCache')) {
                    $this->clearPaginationCache();
                }
            }

        } catch (Exception $e) {
            $this->handleError($e);
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Clear all caches for this component
     */
    protected function clearCache(): void
    {
        if (! empty($this->cacheKey)) {
            cache()->forget($this->cacheKey);
        }
    }

    /**
     * Fetch data from API - must be implemented by child classes
     */
    abstract protected function fetchData(): ?Response;

    /**
     * Process successful API response
     */
    protected function processSuccessfulResponse(Response $response): void
    {
        // Extract and store data
        $data = $this->extractDataFromResponse($response);

        // Store in component and cache
        $this->storeData($data);

        // Set response metadata
        $this->responseMetadata = [
            'status' => $response->status(),
            'successful' => true,
            'timestamp' => now()->toISOString(),
        ];

        // Create a user-friendly API response
        $this->apiResponse = [
            'status' => $response->status(),
            'data' => [
                'message' => "Data loaded successfully - $this->totalRecords records",
                'record_count' => $this->totalRecords,
                'click_to_view' => 'Click "Show Raw JSON" to view full response',
                'cached' => false,
            ],
        ];

        // Set the raw JSON cache key
        $this->rawJsonCacheKey = $this->cacheKey;
    }

    /**
     * Extract data from API response
     */
    protected function extractDataFromResponse(Response $response): array
    {
        $data = $response->json();

        // Handle different response structures
        if (isset($data['data'])) {
            return is_array($data['data']) ? $data['data'] : [];
        }

        if (isset($data['records'])) {
            return is_array($data['records']) ? $data['records'] : [];
        }

        return is_array($data) ? $data : [];
    }

    /**
     * Store data in component and cache
     */
    protected function storeData(array $data): void
    {
        $this->tableData = $data;
        $this->totalRecords = count($data);

        // Cache the data
        if (! empty($this->cacheKey)) {
            cache()->put($this->cacheKey, collect($data), now()->addMinutes(30));
        }
    }

    /**
     * Handle errors consistently
     */
    protected function handleError(Exception $e): void
    {
        $this->errorMessage = 'An unexpected error occurred. Please try again later.';

        Log::error('API Endpoint Error', [
            'component' => get_class($this),
            'error' => $e->getMessage(),
            'user_id' => auth()->id(),
        ]);

        // Clear data on error
        $this->totalRecords = 0;
        $this->tableData = [];
        $this->clearCache();
    }

    /**
     * Clear component state and cache
     */
    public function clearData(): void
    {
        $this->tableData = [];
        $this->totalRecords = 0;
        $this->apiResponse = null;
        $this->errorMessage = null;
        $this->rawJsonCacheKey = '';
        $this->clearCache();

        if (method_exists($this, 'clearPaginationCache')) {
            $this->clearPaginationCache();
        }
    }

    /**
     * Make authenticated API call with error handling
     */
    protected function makeAuthenticatedApiCall(callable $apiCallFunction): ?Response
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

    protected function validateAuthenticationState($response): bool
    {
        if ($response && ($response->status() === 401 || $response->status() === 403)) {
            $this->handleAuthenticationFailure();

            return false;
        }

        return true;
    }

    protected function handleAuthenticationFailure(): void
    {
        session()->forget(['wfm_authenticated', 'wfm_access_token']);
        $this->isAuthenticated = false;
        $this->errorMessage = 'Your authentication session has expired. Please re-enter your credentials to continue.';
    }
}
