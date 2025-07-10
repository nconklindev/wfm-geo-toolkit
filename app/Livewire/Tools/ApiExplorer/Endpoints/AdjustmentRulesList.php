<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\ExportsCsvData;
use App\Traits\PaginatesApiData;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdjustmentRulesList extends BaseApiEndpoint
{
    use ExportsCsvData;
    use PaginatesApiData;

    /**
     * @uses exportAllToCsv called from view via wire:click
     * @uses exportSelectionsToCsv called from view via wire:click
     */
    public function render(): View
    {
        $paginatedData = $this->getPaginatedData();

        return view('livewire.tools.api-explorer.endpoints.adjustment-rules-list', ['paginatedData' => $paginatedData]);
    }

    /**
     * Export all available data (respects current search/sort but not category filters)
     */
    public function exportAllToCsv(): StreamedResponse|RedirectResponse
    {
        $allData = $this->getAllData();

        if ($allData->isEmpty()) {
            session()->flash('error', 'No data available to export.');

            return back();
        }

        // Apply current search and sort to the full dataset
        $filteredData = $this->getFilteredAndSortedData($allData);

        // Flatten the data for CSV export
        $flattenedData = $this->flattenAdjustmentRulesForCsv($filteredData);

        // Generate filename based on the current state
        $filename = $this->generateExportFilename();

        // Define CSV columns for the flattened data
        $csvColumns = $this->defineCSVColumnsForTheFlattenedData();

        return $this->exportAsCsv($flattenedData, $csvColumns, $filename);
    }

    /**
     * Flatten the nested adjustment rules data structure for CSV export
     */
    private function flattenAdjustmentRulesForCsv($data): array
    {
        $flattened = [];

        foreach ($data as $rule) {
            $ruleId = $rule['id'] ?? '';
            $ruleName = $rule['name'] ?? 'Unnamed Rule';
            $paycodeNames = $rule['paycode_names'] ?? '-';

            $ruleVersions = $rule['ruleVersions']['adjustmentRuleVersion'] ?? [];

            // If no versions exist, create a single row with rule-level data
            if (empty($ruleVersions)) {
                $flattened[] = [
                    'rule_id' => $ruleId,
                    'rule_name' => $ruleName,
                    'paycode_names' => $paycodeNames,
                    'version_id' => '',
                    'version_description' => '',
                    'version_effective_date' => '',
                    'version_expiration_date' => '',
                    'trigger_index' => '',
                    'job_or_location_qualifier' => '',
                    'job_or_location_effective_date' => '',
                    'labor_category_entries' => '',
                    'trigger_pay_codes' => '',
                    'adjustment_type' => '',
                    'adjustment_amount' => '',
                    'bonus_rate_amount' => '',
                    'bonus_rate_hourly_rate' => '',
                    'cost_center' => '',
                ];

                continue;
            }

            foreach ($ruleVersions as $version) {
                $versionId = $version['versionId'] ?? '';
                $versionDescription = $version['description'] ?? '';
                $versionEffectiveDate = $version['effectiveDate'] ?? '';
                $versionExpirationDate = $version['expirationDate'] ?? '';

                $triggers = $version['triggers']['adjustmentTriggerForRule'] ?? [];

                // If no triggers exist, create a single row with version-level data
                if (empty($triggers)) {
                    $flattened[] = [
                        'rule_id' => $ruleId,
                        'rule_name' => $ruleName,
                        'paycode_names' => $paycodeNames,
                        'version_id' => $versionId,
                        'version_description' => $versionDescription,
                        'version_effective_date' => $versionEffectiveDate,
                        'version_expiration_date' => $versionExpirationDate,
                        'trigger_index' => '',
                        'job_or_location_qualifier' => '',
                        'job_or_location_effective_date' => '',
                        'labor_category_entries' => '',
                        'trigger_pay_codes' => '',
                        'adjustment_type' => '',
                        'adjustment_amount' => '',
                        'bonus_rate_amount' => '',
                        'bonus_rate_hourly_rate' => '',
                        'cost_center' => '',
                    ];

                    continue;
                }

                // Process each trigger
                foreach ($triggers as $index => $trigger) {
                    // Extract trigger data
                    $jobOrLocationQualifier = $trigger['jobOrLocation']['qualifier'] ?? '';
                    $jobOrLocationEffectiveDate = $trigger['jobOrLocationEffectiveDate'] ?? '';
                    $laborCategoryEntries = $trigger['laborCategoryEntries'] ?? '';

                    // Extract pay codes
                    $payCodes = $trigger['payCodes'] ?? [];
                    $triggerPayCodes = collect($payCodes)
                        ->pluck('qualifier')
                        ->filter()
                        ->implode(', ');

                    // Extract adjustment allocation
                    $allocation = $trigger['adjustmentAllocation']['adjustmentAllocation'] ?? null;
                    $adjustmentType = $allocation['adjustmentType'] ?? '';
                    $adjustmentAmount = $allocation['amount'] ?? '';
                    $bonusRateAmount = $allocation['bonusRateAmount'] ?? '';
                    $bonusRateHourlyRate = $allocation['bonusRateHourlyRate'] ?? '';

                    // Extract cost center
                    $costCenter = $trigger['costCenter'] ?? '';

                    $flattened[] = [
                        'rule_id' => $ruleId,
                        'rule_name' => $ruleName,
                        'paycode_names' => $paycodeNames,
                        'version_id' => $versionId,
                        'version_description' => $versionDescription,
                        'version_effective_date' => $versionEffectiveDate,
                        'version_expiration_date' => $versionExpirationDate,
                        'trigger_index' => $index + 1,
                        'job_or_location_qualifier' => $jobOrLocationQualifier,
                        'job_or_location_effective_date' => $jobOrLocationEffectiveDate,
                        'labor_category_entries' => $laborCategoryEntries,
                        'trigger_pay_codes' => $triggerPayCodes ?: '-',
                        'adjustment_type' => $adjustmentType,
                        'adjustment_amount' => $adjustmentAmount,
                        'bonus_rate_amount' => $bonusRateAmount,
                        'bonus_rate_hourly_rate' => $bonusRateHourlyRate,
                        'cost_center' => $costCenter,
                    ];
                }
            }
        }

        return $flattened;
    }

    protected function generateExportFilename(): string
    {
        $parts = ['adjustment-rules'];

        // Add search term if present
        if (! empty($this->search)) {
            $searchSlug = str_replace([' ', '.', '/', '\\'], '-', strtolower($this->search));
            $parts[] = "search-$searchSlug";
        }

        // Add timestamp
        $parts[] = now()->format('Y-m-d_H-i-s');

        return implode('-', $parts);
    }

    /**
     * @return array[]
     */
    private function defineCSVColumnsForTheFlattenedData(): array
    {
        return [
            ['field' => 'rule_id', 'label' => 'Rule ID'],
            ['field' => 'rule_name', 'label' => 'Rule Name'],
            ['field' => 'paycode_names', 'label' => 'Pay Code Names'],
            ['field' => 'version_id', 'label' => 'Version ID'],
            ['field' => 'version_description', 'label' => 'Version Description'],
            ['field' => 'version_effective_date', 'label' => 'Version Effective Date'],
            ['field' => 'version_expiration_date', 'label' => 'Version Expiration Date'],
            ['field' => 'trigger_index', 'label' => 'Trigger Index'],
            ['field' => 'job_or_location_qualifier', 'label' => 'Job/Location Qualifier'],
            ['field' => 'job_or_location_effective_date', 'label' => 'Job/Location Effective Date'],
            ['field' => 'labor_category_entries', 'label' => 'Labor Category Entries'],
            ['field' => 'trigger_pay_codes', 'label' => 'Trigger Pay Codes'],
            ['field' => 'adjustment_type', 'label' => 'Adjustment Type'],
            ['field' => 'adjustment_amount', 'label' => 'Adjustment Amount'],
            ['field' => 'bonus_rate_amount', 'label' => 'Bonus Rate Amount'],
            ['field' => 'bonus_rate_hourly_rate', 'label' => 'Bonus Rate Hourly Rate'],
            ['field' => 'cost_center', 'label' => 'Cost Center'],
        ];
    }

    /**
     * Export currently paginated/filtered data (selections visible on the current page)
     */
    public function exportSelectionsToCsv(): StreamedResponse|RedirectResponse
    {
        $currentPageData = $this->getPaginatedData();

        if ($currentPageData->isEmpty()) {
            session()->flash('error', 'No data available to export.');

            return back();
        }

        // Get the current page items
        $currentItems = collect($currentPageData->items());

        // Flatten the data for CSV export
        $flattenedData = $this->flattenAdjustmentRulesForCsv($currentItems);

        // Generate filename for selection export
        $filename = $this->generateSelectionsExportFilename();

        // Define CSV columns for the flattened data
        $csvColumns = $this->defineCSVColumnsForTheFlattenedData();

        return $this->exportAsCsv($flattenedData, $csvColumns, $filename);
    }

    /**
     * Generate filename for selection export
     */
    protected function generateSelectionsExportFilename(): string
    {
        $parts = ['adjustment-rules-selections'];

        // Add search term if present
        if (! empty($this->search)) {
            $searchSlug = Str::slug(($this->search));
            $parts[] = "search-$searchSlug";
        }

        // Add sort info if not default
        if ($this->sortField !== 'name' || $this->sortDirection !== 'asc') {
            $parts[] = "sorted-by-$this->sortField-$this->sortDirection";
        }

        // Add timestamp
        $parts[] = now()->format('Y-m-d_H-i-s');

        return implode('-', $parts);
    }

    protected function getAllDataForExport(): Collection
    {
        if (! $this->isAuthenticated) {
            return collect();
        }

        $response = $this->makeAuthenticatedApiCall(function () {
            return $this->wfmService->getAdjustmentRules();
        });

        if ($response && $response->successful()) {
            $data = $response->json();
            $records = $data['records'] ?? [];

            return collect($records);
        }

        return collect();
    }

    protected function initializeEndpoint(): void
    {
        // Set table columns specific to data elements
        $this->tableColumns = [
            [
                'field' => 'name',
                'label' => 'Name',
            ],
            [
                'field' => 'ruleVersions.adjustmentRuleVersion.0.description',
                'label' => 'Description',
            ],
            [
                'field' => 'ruleVersions.adjustmentRuleVersion.0.effectiveDate',
                'label' => 'Effective Date',
            ],
            [
                'field' => 'ruleVersions.adjustmentRuleVersion.0.expirationDate',
                'label' => 'Expiration Date',
            ],
            [
                'field' => 'paycode_names',
                'label' => 'Pay Codes',
            ],
        ];

        // Initialize pagination data
        $this->initializePaginationData();
    }

    protected function makeApiCall()
    {
        if (! $this->isAuthenticated) {
            return null;
        }

        try {
            $response = $this->makeAuthenticatedApiCall(function () {
                return $this->wfmService->getAdjustmentRules();
            });

            $this->processApiResponseData($response, 'DataElementsList');

            // Clear any previous error messages on a successful call
            $this->errorMessage = '';

            return $response;
        } catch (Exception $e) {
            // Handle other types of exceptions
            $this->errorMessage = 'An unexpected error occurred. Please try again later.';

            Log::error('Unexpected error in AdjustmentRulesList', [
                'error' => $e->getMessage(),
                'type' => get_class($e),
                'stack_trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
            ]);

            $this->totalRecords = 0;

            if (! empty($this->cacheKey)) {
                cache()->forget($this->cacheKey);
            }

            return null;
        }
    }

    protected function processApiResponseData($response, string $componentName = ''): void
    {
        if ($response && $response->successful()) {
            $data = $response->json();
            $records = $data['records'] ?? $data;

            // Process each record to add custom fields
            $processedRecords = array_map(function ($item) {
                $item['paycode_names'] = $this->extractPaycodeNames($item);

                return $item;
            }, $records);

            // Cache the processed dataset
            $this->cacheKey = $this->generateCacheKey();
            cache()->put($this->cacheKey, collect($processedRecords), now()->addMinutes(30));

            $this->totalRecords = is_array($data) && isset($data['totalRecords'])
                ? $data['totalRecords']
                : count($processedRecords);

            // Clear pagination cache when new data is loaded
            $this->clearPaginationCache();

            Log::info('Data Cached', [
                'component' => $componentName ?: get_class($this),
                'total_records_available' => $this->totalRecords,
                'records_fetched' => count($processedRecords),
                'cache_key' => $this->cacheKey,
                'memory_usage_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ]);
        } else {
            $this->totalRecords = 0;
        }
    }

    /**
     * Extract ALL paycode names (both trigger and allocation) for the main table display
     */
    private function extractPaycodeNames($item): string
    {
        $paycodeNames = [];

        // Navigate through the nested structure to get paycodes
        $ruleVersions = $item['ruleVersions']['adjustmentRuleVersion'] ?? [];

        foreach ($ruleVersions as $ruleVersion) {
            $triggers = $ruleVersion['triggers']['adjustmentTriggerForRule'] ?? [];

            foreach ($triggers as $trigger) {
                // Extract paycodes from trigger payCodes array (conditions)
                $payCodes = $trigger['payCodes'] ?? [];
                foreach ($payCodes as $payCode) {
                    if (isset($payCode['qualifier'])) {
                        $paycodeNames[] = $payCode['qualifier'];
                    }
                }

                // Extract paycode from adjustment allocation (what gets paid)
                $allocation = $trigger['adjustmentAllocation']['adjustmentAllocation'] ?? null;
                if ($allocation && isset($allocation['payCode']['qualifier'])) {
                    $paycodeNames[] = $allocation['payCode']['qualifier'];
                }
            }
        }

        // Remove duplicates and return as comma-separated string
        $uniqueNames = array_unique($paycodeNames);

        return implode(', ', $uniqueNames);
    }

    /**
     * Extract ONLY trigger paycode names for the triggers section
     */
    private function extractTriggerPaycodeNames($trigger): string
    {
        $payCodes = $trigger['payCodes'] ?? [];
        $triggerPayCodes = collect($payCodes)
            ->pluck('qualifier')
            ->filter()
            ->implode(', ');

        return $triggerPayCodes ?: '-';
    }

    /**
     * Extract ONLY allocation paycode name for the allocation section
     */
    private function extractAllocationPaycodeName($trigger): string
    {
        $allocation = $trigger['adjustmentAllocation']['adjustmentAllocation'] ?? null;

        if ($allocation && isset($allocation['payCode']['qualifier'])) {
            return $allocation['payCode']['qualifier'];
        }

        return '-';
    }
}
