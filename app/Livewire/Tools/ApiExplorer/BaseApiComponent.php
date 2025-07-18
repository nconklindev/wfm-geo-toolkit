<?php

namespace App\Livewire\Tools\ApiExplorer;

use App\Interfaces\CacheableInterface;
use App\Interfaces\DataTransformerInterface;
use App\Interfaces\PaginatableInterface;
use App\Services\WfmService;
use App\Traits\HandlesAuthentication;
use App\Traits\HasApiDataTable;
use App\Traits\HasCaching;
use App\Traits\HasCsvExport;
use App\Traits\HasPagination;
use App\Traits\ProcessesApiResponse;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Component;

abstract class BaseApiComponent extends Component implements CacheableInterface, DataTransformerInterface, PaginatableInterface
{
    use HandlesAuthentication;
    use HasApiDataTable;
    use HasCaching;
    use HasCsvExport;
    use HasPagination;
    use ProcessesApiResponse;

    public ?array $apiResponse = null;

    public string $rawJsonCacheKey = '';

    #[Locked]
    protected array $data = [];

    protected bool $isLoading = false;

    public function boot(): void
    {
        $this->wfmService = app(WfmService::class);
    }

    /**
     * Handle pagination updates - ensures data is available when paginating
     */
    public function updatedPage(): void
    {
        // Ensure we have data loaded when paginating
        if (empty($this->data) && $this->isAuthenticated) {
            $this->loadCachedDataIfAvailable();
        }

        // Recreate paginated data for the new page
        if (! empty($this->data)) {
            $this->createPaginatedData();
        }
    }

    /**
     * Load cached data if available, without making API calls.
     * This method follows the existing caching patterns and properly
     * initializes the table data structures if cached data exists.
     */
    protected function loadCachedDataIfAvailable(): void
    {
        if (! $this->isAuthenticated) {
            return;
        }

        $cacheKey = $this->getCacheKey();
        $cachedData = $this->getCachedDataDirect($cacheKey);

        if ($cachedData !== null) {
            $this->data = $cachedData;
            $this->totalRecords = count($this->data);

            // Initialize table data structures with cached data
            $this->initializeTableData();

            // Set up the successful response indicator for cached data
            $this->setSuccessfulCachedResponse();

            Log::info('BaseApiComponent: Cached data loaded on mount', [
                'component' => get_class($this),
                'data_count' => count($this->data),
                'cache_key' => $cacheKey,
            ]);
        }
    }

    /**
     * Set up response indicators for cached data.
     * Similar to setSuccessfulApiResponse() but indicates a cached data source.
     */
    protected function setSuccessfulCachedResponse(): void
    {
        $recordCount = count($this->data);

        $this->apiResponse = [
            'status' => 200,
            'data' => [
                'message' => "Cached data loaded - $recordCount records",
                'record_count' => $recordCount,
                'click_to_view' => 'Click "Show Raw JSON" below to view cached response',
                'cached' => true,
            ],
        ];

        $this->rawJsonCacheKey = $this->getCacheKey();
    }

    // Base render method that concrete classes can override if needed

    public function placeholder(): string
    {
        return <<<'HTML'
            <div class="flex items-center justify-center h-12 mx-auto w-full">
                <flux:icon.loading class="w-6 h-6 text-zinc-400 animate-spin" />
            </div>
            HTML;
    }

    /**
     * Livewire lifecycle hook - called on every request including pagination
     */
    public function hydrate(): void
    {
        $this->setupAuthenticationFromSession();

        // Reload cached data if we don't have any data loaded
        if (empty($this->data) && $this->isAuthenticated) {
            $this->loadCachedDataIfAvailable();
        }
    }

    public function render(): View
    {
        $viewName = $this->getViewName();

        return view($viewName, ['paginatedData' => $this->paginatedData]);
    }

    protected function getViewName(): string
    {
        // Convert class name to view name
        // e.g., HyperfindQueriesList -> hyperfind-queries-list
        $className = class_basename($this);
        $viewName = strtolower(
            preg_replace('/([a-z])([A-Z])/', '$1-$2', $className),
        );

        return "livewire.tools.api-explorer.endpoints.$viewName";
    }

    public function mount(): void
    {
        $this->setupAuthenticationFromSession();
        $this->loadCachedDataIfAvailable();
    }

    public function transformForView(array $data): array
    {
        return $data;
    }

    public function transformForCsv(array $data): array
    {
        return $data;
    }

    public function executeRequest(): void
    {
        $this->setupAuthenticationFromSession();
        $this->loadData();
    }

    public function loadData(): void
    {
        if (! $this->isAuthenticated) {
            $this->errorMessage
                = 'Please authenticate first using the credentials form above.';

            return;
        }

        $this->isLoading = true;
        $this->errorMessage = '';

        try {
            $cacheKey = $this->getCacheKey();

            // Get cached raw response data or fetch fresh if not cached
            $this->data = $this->rememberCachedData($cacheKey, function () {
                return $this->fetchDataFromApi();
            }, $this->getCacheTtl());

            // Initialize table data after fetching
            $this->initializeTableData();
            $this->setSuccessfulApiResponse();
        } catch (Exception $e) {
            $this->handleDataLoadingError($e);
        } finally {
            $this->isLoading = false;
        }
    }

    protected function fetchDataFromApi(): array
    {
        Log::info('BaseApiComponent: fetchDataFromApi started', [
            'component' => get_class($this),
            'isAuthenticated' => $this->isAuthenticated,
        ]);

        // Merge the parameters with those set in the endpoint component
        // We don't set any defaults here because parameters vary
        // from endpoint to endpoint
        $params = $this->getApiParams();

        Log::info('BaseApiComponent: API params', [
            'component' => get_class($this),
            'params' => $params,
        ]);

        $apiServiceCall = $this->getApiServiceCall();

        Log::info('BaseApiComponent: About to make API call', [
            'component' => get_class($this),
        ]);

        $response = $this->makeAuthenticatedApiCall(
            fn () => $apiServiceCall($params),
        );

        Log::info('BaseApiComponent: API call completed', [
            'component' => get_class($this),
            'has_response' => (bool) $response,
            'response_status' => $response ? $response->status()
                : 'no response',
            'response_successful' => $response && $response->successful(),
        ]);

        if (! $response) {
            $this->totalRecords = 0;
            Log::warning('BaseApiComponent: No response from API');

            return [];
        }

        // Store the raw response data in the cache for the raw JSON viewer
        $rawResponseData = $response->json();
        $rawJsonCacheKey = $this->getCacheKey();
        $this->storeCachedData(
            $rawJsonCacheKey,
            $rawResponseData,
            $this->getCacheTtl(),
        );
        //        cache()->put($rawJsonCacheKey, $rawResponseData, $this->getCacheTtl());

        Log::info('BaseApiComponent: Stored raw response in cache', [
            'component' => get_class($this),
            'rawJsonCacheKey' => $rawJsonCacheKey,
        ]);

        // Update total records
        $this->totalRecords = $this->extractTotalFromResponse($response);

        // Extract and return data
        $extractedData = $this->extractDataFromResponse($response);

        Log::info('BaseApiComponent: Data extraction completed', [
            'component' => get_class($this),
            'extracted_count' => count($extractedData),
            'totalRecords' => $this->totalRecords,
        ]);

        return $extractedData;
    }

    abstract protected function getApiParams(): array;

    // Abstract methods for response handling - must be implemented

    /**
     * Get API service call
     *
     * Return a callable that will be used to fetch data from the API.
     * The callable should accept an array of parameters and return an HTTP
     * Response. This method defines how to call an API endpoint.
     *
     * PATTERN: return fn (array $params) =>
     * $this->serviceName->methodName($params);
     *
     * The $params array will contain the result of getApiParams() and will be
     * passed to the service method. The service method should return an HTTP
     * Response object.
     *
     * @return callable A function that accepts array $params and returns
     *                  Response
     */
    abstract protected function getApiServiceCall(): callable;

    // Default implementation that can be overridden in a concrete class

    protected function handleDataLoadingError(Exception $e): void
    {
        $this->errorMessage = 'Failed to load data: '.$e->getMessage();
        $this->data = [];
        $this->paginatedData = null;
        $this->apiResponse = null;
        $this->rawJsonCacheKey = '';

        Log::error('API Component Error', [
            'component' => get_class($this),
            'error' => $e->getMessage(),
            'user_id' => auth()->id() ?? 'guest',
        ]);
    }

    // Default implementation that can be overridden in a concrete class

    /**
     * Retrieves the data key from the response.
     *
     * Only necessary to set if the WFM endpoint returns the response wrapped
     * in
     * a different key.
     * `hyperfindQueries` would be the key in the following example:
     * ```
     *      {
     *          "hyperfindQueries": [
     *              ...
     *          ]
     *      }
     * ```
     * Check the Developer Portal to see an example response for each WFM
     * endpoint.
     *
     * @return string|null The data key if available, otherwise null.
     */
    abstract protected function getDataKeyFromResponse(): ?string;

    /**
     * Extracts the total count from the given response data.
     *
     * This method should parse the provided array of response data to retrieve
     * the total count value. The implementation depends on the structure of the
     * response data returned by the WFM endpoint.
     *
     * Typically, this will only need to `return count($data)`
     *
     * @param  array  $data  The response data from which to extract the total count.
     * @return int|null The extracted total count if available, otherwise null.
     */
    abstract protected function getTotalFromResponseData(array $data): ?int;

    protected function getCsvFilename(): string
    {
        return 'export_'.class_basename($this).date('Y-m-d-H-i-s');
    }

    protected function getCsvHeaders(): array
    {
        return array_column($this->tableColumns, 'label');
    }
}
