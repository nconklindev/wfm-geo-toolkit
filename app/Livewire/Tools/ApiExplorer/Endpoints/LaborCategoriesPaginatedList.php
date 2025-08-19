<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseBatchableApiComponent;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use stdClass;

class LaborCategoriesPaginatedList extends BaseBatchableApiComponent
{
    public string $errorMessage = '';

    #[Validate('array')]
    public array $laborCategories = [];

    #[Validate('array')]
    public array $selectedLaborCategories = [];

    public bool $laborCategoriesLoaded = false;

    /**
     * Handle when labor category selection changes
     */
    public function updatedSelectedLaborCategories(): void
    {
        // Clear existing data when selection changes
        $this->data = [];
        $this->paginatedData = null;
        $this->apiResponse = null;

        // Reset pagination
        $this->resetPage();

        Log::info('Labor category selection updated', [
            'component' => get_class($this),
            'selected_count' => count($this->selectedLaborCategories),
        ]);
    }

    /**
     * Remove a category from selection
     */
    public function removeCategory(string $categoryId): void
    {
        $this->selectedLaborCategories = array_values(
            array_filter($this->selectedLaborCategories, function ($id) use ($categoryId) {
                return $id !== $categoryId;
            })
        );
    }

    // We override mount in this one endpoint because we need to load
    // the labor categories when the component is loaded
    public function mount(?string $accessToken = null, ?string $hostname = null): void
    {
        // the parent mount method will handle the authentication
        parent::mount();

        if ($this->isAuthenticated && ! $this->laborCategoriesLoaded) {
            $this->loadLaborCategories();
        }
    }

    /**
     * Load labor categories for the dropdown selection
     */
    public function loadLaborCategories(): void
    {
        if (! $this->isAuthenticated) {
            $this->errorMessage = 'Please authenticate first using the credentials form above.';

            return;
        }

        try {
            Log::info('Loading labor categories', [
                'component' => get_class($this),
            ]);

            $response = $this->makeAuthenticatedApiCall(
                fn () => $this->wfmService->getLaborCategories(),
            );

            if ($response && $response->successful()) {
                $data = $response->json();

                // Transform the data for the dropdown display
                $this->laborCategories = collect($data)
                    ->map(function ($category) {
                        return [
                            'id' => $category['id'] ?? '',
                            'name' => $category['name'] ?? 'Unknown',
                            'qualifier' => $category['qualifier'] ?? '',
                        ];
                    })
                    ->toArray();

                $this->laborCategoriesLoaded = true;

                Log::info('Labor categories loaded successfully', [
                    'component' => get_class($this),
                    'count' => count($this->laborCategories),
                ]);
            } else {
                $this->errorMessage = 'Failed to load labor categories. Please try again.';
                Log::error('Failed to load labor categories', [
                    'component' => get_class($this),
                    'status' => $response ? $response->status() : 'no_response',
                ]);
            }
        } catch (Exception $e) {
            $this->errorMessage = 'Error loading labor categories: '.$e->getMessage();
            Log::error('Error loading labor categories', [
                'component' => get_class($this),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Override shouldBatch to disable batching for this component
     * since we need to make multiple separate API calls
     */
    public function shouldBatch(): bool
    {
        return false; // Disable smart batching for multiple labor category calls
    }

    protected function getApiParams(): array
    {
        // We only handle the case where no Labor Categories are selected here
        // and return all by default
        return ['where' => new stdClass];
    }

    /**
     * {@inheritDoc}
     */
    protected function getApiServiceCall(): callable
    {
        return fn ($params) => $this->wfmService->getLaborCategoryEntriesPaginated($params);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDataKeyFromResponse(): ?string
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    protected function getTotalFromResponseData(array $data): ?int
    {
        return count($data);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDataForCsvExport(): Collection
    {
        // Try to get cached data first
        if (! empty($this->data)) {
            return collect($this->data);
        }

        // If no cached data, and we're authenticated, fetch fresh data
        if ($this->isAuthenticated) {
            $this->loadData();

            return collect($this->data);
        }

        // Return an empty collection if not authenticated or no data
        return collect();
    }

    /**
     * Override the loadData method to handle multiple labor categories
     */
    public function loadData(): void
    {
        // First, ensure labor categories are loaded
        if (! $this->laborCategoriesLoaded) {
            $this->loadLaborCategories();
            Log::info('labor categories loaded');
        }

        // Handle multiple labor category calls ourselves
        if (! empty($this->selectedLaborCategories)) {
            $this->loadMultipleLaborCategoryData();
        } else {
            // If no categories selected, call parent (which will return empty due to getApiParams)
            parent::loadData();
        }
    }

    /**
     * Handle loading data for multiple labor categories
     */
    protected function loadMultipleLaborCategoryData(): void
    {
        if (! $this->isAuthenticated) {
            $this->errorMessage = 'Please authenticate first using the credentials form above.';

            return;
        }

        $this->isLoading = true;
        $this->errorMessage = '';

        try {
            $cacheKey = $this->getCacheKey();

            // Get cached data or fetch fresh
            $this->data = $this->rememberCachedData($cacheKey, function () {
                return $this->fetchMultipleLaborCategoryData();
            }, $this->getCacheTtl());

            // Initialize table data after fetching
            $this->initializeTableData();
            $this->setSuccessfulMultipleCategoryResponse();
        } catch (Exception $e) {
            $this->handleDataLoadingError($e);
        } finally {
            $this->isLoading = false;
        }
    }

    public function getCacheKey(): string
    {
        $id = md5(session()->id());
        $selectedLaborCategories = md5(implode(',', $this->selectedLaborCategories));

        return 'labor_category_entries_'.$selectedLaborCategories.'_'.$id;
    }

    /**
     * Fetch data for multiple labor categories
     */
    protected function fetchMultipleLaborCategoryData(): array
    {
        Log::info('LaborCategoriesPaginatedList: fetchMultipleLaborCategoryData started', [
            'component' => get_class($this),
            'selectedCount' => count($this->selectedLaborCategories),
            'selectedLaborCategories' => $this->selectedLaborCategories,
        ]);

        // Convert selected IDs to integers for consistent comparison
        $selectedIds = array_map('intval', $this->selectedLaborCategories);

        // Get the selected categories
        $selectedCategories = collect($this->laborCategories)
            ->whereIn('id', $selectedIds)
            ->toArray();

        Log::info('Debug matched categories', [
            'selectedCategories' => $selectedCategories,
            'matchedCount' => count($selectedCategories),
        ]);

        if (empty($selectedCategories)) {
            Log::warning('No categories found for selected IDs', [
                'selectedIds' => $selectedIds,
                'laborCategories_ids' => collect($this->laborCategories)->pluck('id')->toArray(),
            ]);
            $this->totalRecords = 0;

            return [];
        }

        $allData = [];
        $allRawResponses = [];
        $successfulCalls = 0;
        $failedCalls = 0;

        // Make one API call per selected category
        foreach ($selectedCategories as $category) {
            try {
                Log::info('Making API call for category', [
                    'categoryId' => $category['id'],
                    'categoryName' => $category['name'],
                    'originalQualifier' => $category['qualifier'],
                ]);

                $params = [
                    'where' => [
                        'laborCategory' => [
                            'id' => $category['id'],
                            'qualifier' => $category['name'], // Use name as the qualifier string
                        ],
                    ],
                ];

                $response = $this->makeAuthenticatedApiCall(
                    fn () => $this->wfmService->getLaborCategoryEntriesPaginated($params)
                );

                if ($response && $response->successful()) {
                    $responseData = $response->json();

                    $allRawResponses[] = [
                        'laborCategory' => $category['name'],
                        'categoryName' => $category['name'],
                        'categoryId' => $category['id'],
                        'data' => $responseData,
                    ];

                    $extractedData = $this->extractDataFromResponse($response);

                    // Add source tracking
                    $extractedData = array_map(function ($item) use ($category) {
                        if (is_array($item)) {
                            $item['_source_labor_category'] = $category['name'];
                            $item['_source_qualifier'] = $category['name'];
                            $item['_source_category_id'] = $category['id'];
                        }

                        return $item;
                    }, $extractedData);

                    $allData = array_merge($allData, $extractedData);
                    $successfulCalls++;

                    Log::info('API call successful', [
                        'categoryName' => $category['name'],
                        'categoryId' => $category['id'],
                        'recordCount' => count($extractedData),
                    ]);
                } else {
                    $failedCalls++;
                    Log::warning('API call failed', [
                        'categoryName' => $category['name'],
                        'categoryId' => $category['id'],
                        'status' => $response ? $response->status() : 'no_response',
                    ]);
                }
            } catch (Exception $e) {
                $failedCalls++;
                Log::error('Exception during API call', [
                    'categoryName' => $category['name'] ?? 'unknown',
                    'categoryId' => $category['id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Store combined raw responses
        $combinedRawResponse = [
            'multiple_labor_category_calls' => true,
            'successful_calls' => $successfulCalls,
            'failed_calls' => $failedCalls,
            'total_records' => count($allData),
            'responses' => $allRawResponses,
        ];

        $this->storeCachedData($this->getCacheKey(), $combinedRawResponse, $this->getCacheTtl());
        $this->totalRecords = count($allData);

        Log::info('fetchMultipleLaborCategoryData completed', [
            'totalRecords' => $this->totalRecords,
            'successfulCalls' => $successfulCalls,
            'failedCalls' => $failedCalls,
        ]);

        return $allData;
    }

    public function getCacheTtl(): int
    {
        return 3600; // 1 hour
    }

    /**
     * Set successful response for multiple category calls
     */
    protected function setSuccessfulMultipleCategoryResponse(): void
    {
        $recordCount = count($this->data);
        $categoryCount = count($this->selectedLaborCategories);

        $this->apiResponse = [
            'status' => 200,
            'data' => [
                'message' => $categoryCount > 1
                    ? "Multiple API calls completed - $recordCount total records from $categoryCount labor categories"
                    : "API call completed - $recordCount records",
                'record_count' => $recordCount,
                'labor_categories_queried' => $categoryCount,
                'click_to_view' => 'Click "Show Raw JSON" below to view combined response data',
            ],
        ];

        $this->rawJsonCacheKey = $this->getCacheKey();
    }

    /**
     * {@inheritDoc}
     */
    protected function getCsvColumns(): array
    {
        return $this->getTableColumns();
    }

    /**
     * {@inheritDoc}
     */
    protected function getTableColumns(): array
    {
        return [
            ['field' => 'name', 'label' => 'Name'],
            ['field' => 'description', 'label' => 'Description'],
            ['field' => 'inactive', 'label' => 'Inactive'],
            ['field' => 'laborCategory.name', 'label' => 'Labor Category'],
        ];
    }
}
