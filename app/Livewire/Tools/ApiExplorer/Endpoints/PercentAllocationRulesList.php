<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\ExportsCsvData;
use App\Traits\PaginatesApiData;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PercentAllocationRulesList extends BaseApiEndpoint
{
    use ExportsCsvData;
    use PaginatesApiData;

    public function render(): View
    {
        $paginatedData = $this->getPaginatedData();

        return view('livewire.tools.api-explorer.endpoints.percent-allocation-rules-list', [
            'paginatedData' => $paginatedData,
        ]);
    }

    /**
     * Override exportAllToCsv to use flattened data structure
     */
    public function exportAllToCsv(): StreamedResponse|RedirectResponse
    {
        try {
            $allData = $this->getAllDataForExport();

            if ($allData->isEmpty()) {
                session()?->flash('error', 'No data available to export.');

                return back();
            }

            // Apply current search and sort filters
            $filteredData = $this->applyFiltersAndSort($allData);

            // Flatten the data for CSV export
            $flattenedData = $this->flattenPercentAllocationRulesForCsv($filteredData);

            $filename = $this->generateExportFilename('all');

            return $this->generateCsv($flattenedData, $this->defineCSVColumnsForTheFlattenedData(), $filename);
        } catch (Exception $e) {
            Log::error('CSV Export Error - All Data', [
                'error' => $e->getMessage(),
                'component' => get_class($this),
            ]);

            session()?->flash('error', 'Failed to export data. Please try again.');

            return back();
        }
    }

    /**
     * Custom method for getting all data for export - required by ExportsCsvData trait
     */
    protected function getAllDataForExport(): Collection
    {
        $response = $this->fetchData();

        if (! $response || ! $response->successful()) {
            return collect();
        }

        $allData = $this->extractDataFromResponse($response);

        // Process the data to add custom fields
        $processedData = $this->processDataForDisplay($allData);

        return collect($processedData);
    }

    /**
     * Fetches data by making an authenticated API call to retrieve percent allocation rules.
     */
    protected function fetchData(): ?Response
    {
        return $this->makeAuthenticatedApiCall(function () {
            return $this->wfmService->getPercentAllocationRules();
        });
    }

    /**
     * Process raw API data to add custom fields
     */
    protected function processDataForDisplay(array $data): array
    {
        return array_map(function ($item) {
            $item['job_names'] = $this->extractJobNames($item);
            $item['paycode_names'] = $this->extractPaycodeNames($item);

            return $item;
        }, $data);
    }

    /**
     * Extract job names from allocations for the main table display
     */
    private function extractJobNames($item): string
    {
        $jobNames = [];

        // Navigate through the nested structure to get job names
        $fpaRuleVersions = $item['fpaRuleVersions'] ?? [];

        foreach ($fpaRuleVersions as $ruleVersion) {
            $triggers = $ruleVersion['triggers'] ?? [];

            foreach ($triggers as $trigger) {
                $allocations = $trigger['allocations'] ?? [];

                foreach ($allocations as $allocation) {
                    $job = $allocation['job'] ?? [];
                    if (isset($job['name'])) {
                        $jobNames[] = $job['name'];
                    } elseif (isset($job['qualifier'])) {
                        $jobNames[] = $job['qualifier'];
                    }
                }
            }
        }

        // Remove duplicates and return as comma-separated string
        $uniqueNames = array_unique($jobNames);

        return implode(', ', $uniqueNames);
    }

    /**
     * Extract paycode names from triggers for the main table display
     */
    private function extractPaycodeNames($item): string
    {
        $paycodeNames = [];

        // Navigate through the nested structure to get paycodes
        $fpaRuleVersions = $item['fpaRuleVersions'] ?? [];

        foreach ($fpaRuleVersions as $ruleVersion) {
            $triggers = $ruleVersion['triggers'] ?? [];

            foreach ($triggers as $trigger) {
                $payCodes = $trigger['payCodes'] ?? [];
                foreach ($payCodes as $payCode) {
                    if (isset($payCode['qualifier'])) {
                        $paycodeNames[] = $payCode['qualifier'];
                    }
                }
            }
        }

        // Remove duplicates and return as comma-separated string
        $uniqueNames = array_unique($paycodeNames);

        return implode(', ', $uniqueNames);
    }

    /**
     * Flatten the nested percent allocation rules data structure for CSV export
     */
    private function flattenPercentAllocationRulesForCsv($data): array
    {
        $flattened = [];

        foreach ($data as $rule) {
            $ruleId = $rule['id'] ?? '';
            $ruleName = $rule['name'] ?? 'Unnamed Rule';
            $persistentId = $rule['persistentId'] ?? '';
            $jobNames = $rule['job_names'] ?? '-';
            $paycodeNames = $rule['paycode_names'] ?? '-';

            $fpaRuleVersions = $rule['fpaRuleVersions'] ?? [];

            // If no versions exist, create a single row with rule-level data
            if (empty($fpaRuleVersions)) {
                $flattened[] = [
                    'rule_id' => $ruleId,
                    'rule_name' => $ruleName,
                    'persistent_id' => $persistentId,
                    'job_names' => $jobNames,
                    'paycode_names' => $paycodeNames,
                    'version_description' => '',
                    'start_effective_date' => '',
                    'end_effective_date' => '',
                    'trigger_index' => '',
                    'job_or_location_qualifier' => '',
                    'job_or_location_effective_date' => '',
                    'labor_category_entries' => '',
                    'trigger_pay_codes' => '',
                    'allocation_index' => '',
                    'allocation_percentage' => '',
                    'wage_adjustment_amount' => '',
                    'wage_adjustment_type' => '',
                    'allocation_job_name' => '',
                    'allocation_job_qualifier' => '',
                ];

                continue;
            }

            foreach ($fpaRuleVersions as $version) {
                $versionDescription = $version['description'] ?? '';
                $startEffectiveDate = $version['startEffectiveDate'] ?? '';
                $endEffectiveDate = $version['endEffectiveDate'] ?? '';

                $triggers = $version['triggers'] ?? [];

                // If no triggers exist, create a single row with version-level data
                if (empty($triggers)) {
                    $flattened[] = [
                        'rule_id' => $ruleId,
                        'rule_name' => $ruleName,
                        'persistent_id' => $persistentId,
                        'job_names' => $jobNames,
                        'paycode_names' => $paycodeNames,
                        'version_description' => $versionDescription,
                        'start_effective_date' => $startEffectiveDate,
                        'end_effective_date' => $endEffectiveDate,
                        'trigger_index' => '',
                        'job_or_location_qualifier' => '',
                        'job_or_location_effective_date' => '',
                        'labor_category_entries' => '',
                        'trigger_pay_codes' => '',
                        'allocation_index' => '',
                        'allocation_percentage' => '',
                        'wage_adjustment_amount' => '',
                        'wage_adjustment_type' => '',
                        'allocation_job_name' => '',
                        'allocation_job_qualifier' => '',
                    ];

                    continue;
                }

                // Process each trigger
                foreach ($triggers as $triggerIndex => $trigger) {
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

                    $allocations = $trigger['allocations'] ?? [];

                    // If no allocations exist, create a single row with trigger-level data
                    if (empty($allocations)) {
                        $flattened[] = [
                            'rule_id' => $ruleId,
                            'rule_name' => $ruleName,
                            'persistent_id' => $persistentId,
                            'job_names' => $jobNames,
                            'paycode_names' => $paycodeNames,
                            'version_description' => $versionDescription,
                            'start_effective_date' => $startEffectiveDate,
                            'end_effective_date' => $endEffectiveDate,
                            'trigger_index' => $triggerIndex + 1,
                            'job_or_location_qualifier' => $jobOrLocationQualifier,
                            'job_or_location_effective_date' => $jobOrLocationEffectiveDate,
                            'labor_category_entries' => $laborCategoryEntries,
                            'trigger_pay_codes' => $triggerPayCodes ?: '-',
                            'allocation_index' => '',
                            'allocation_percentage' => '',
                            'wage_adjustment_amount' => '',
                            'wage_adjustment_type' => '',
                            'allocation_job_name' => '',
                            'allocation_job_qualifier' => '',
                        ];

                        continue;
                    }

                    // Process each allocation
                    foreach ($allocations as $allocationIndex => $allocation) {
                        $flattened[] = [
                            'rule_id' => $ruleId,
                            'rule_name' => $ruleName,
                            'persistent_id' => $persistentId,
                            'job_names' => $jobNames,
                            'paycode_names' => $paycodeNames,
                            'version_description' => $versionDescription,
                            'start_effective_date' => $startEffectiveDate,
                            'end_effective_date' => $endEffectiveDate,
                            'trigger_index' => $triggerIndex + 1,
                            'job_or_location_qualifier' => $jobOrLocationQualifier,
                            'job_or_location_effective_date' => $jobOrLocationEffectiveDate,
                            'labor_category_entries' => $laborCategoryEntries,
                            'trigger_pay_codes' => $triggerPayCodes ?: '-',
                            'allocation_index' => $allocationIndex + 1,
                            'allocation_percentage' => $allocation['percentage'] ?? '',
                            'wage_adjustment_amount' => $allocation['wageAdjustmentAmount'] ?? '',
                            'wage_adjustment_type' => $allocation['wageAdjustmentType'] ?? '',
                            'allocation_job_name' => $allocation['job']['name'] ?? '',
                            'allocation_job_qualifier' => $allocation['job']['qualifier'] ?? '',
                        ];
                    }
                }
            }
        }

        return $flattened;
    }

    /**
     * Custom filename generation for exports
     */
    protected function generateExportFilename(string $type): string
    {
        $parts = ['percent-allocation-rules', $type];

        // Add search term if present
        if (! empty($this->search)) {
            $searchSlug = str_replace([' ', '.', '/', '\\'], '-', strtolower($this->search));
            $parts[] = "search-{$searchSlug}";
        }

        // Add timestamp
        $parts[] = now()->format('Y-m-d_H-i-s');

        return implode('-', $parts);
    }

    /**
     * Define CSV columns for the flattened data
     */
    private function defineCSVColumnsForTheFlattenedData(): array
    {
        return [
            ['field' => 'rule_id', 'label' => 'Rule ID'],
            ['field' => 'rule_name', 'label' => 'Rule Name'],
            ['field' => 'persistent_id', 'label' => 'Persistent ID'],
            ['field' => 'job_names', 'label' => 'Job Names'],
            ['field' => 'paycode_names', 'label' => 'Pay Code Names'],
            ['field' => 'version_description', 'label' => 'Version Description'],
            ['field' => 'start_effective_date', 'label' => 'Start Effective Date'],
            ['field' => 'end_effective_date', 'label' => 'End Effective Date'],
            ['field' => 'trigger_index', 'label' => 'Trigger Index'],
            ['field' => 'job_or_location_qualifier', 'label' => 'Job/Location Qualifier'],
            ['field' => 'job_or_location_effective_date', 'label' => 'Job/Location Effective Date'],
            ['field' => 'labor_category_entries', 'label' => 'Labor Category Entries'],
            ['field' => 'trigger_pay_codes', 'label' => 'Trigger Pay Codes'],
            ['field' => 'allocation_index', 'label' => 'Allocation Index'],
            ['field' => 'allocation_percentage', 'label' => 'Allocation Percentage'],
            ['field' => 'wage_adjustment_amount', 'label' => 'Wage Adjustment Amount'],
            ['field' => 'wage_adjustment_type', 'label' => 'Wage Adjustment Type'],
            ['field' => 'allocation_job_name', 'label' => 'Allocation Job Name'],
            ['field' => 'allocation_job_qualifier', 'label' => 'Allocation Job Qualifier'],
        ];
    }

    /**
     * Override exportSelectionsToCsv to use flattened data structure
     */
    public function exportSelectionsToCsv(): StreamedResponse|RedirectResponse
    {
        try {
            $exportData = $this->getAllData();
            $filteredData = $this->applyFiltersAndSort($exportData);

            if ($filteredData->isEmpty()) {
                session()?->flash('error', 'No data available to export.');

                return back();
            }

            // Flatten the data for CSV export
            $flattenedData = $this->flattenPercentAllocationRulesForCsv($filteredData);

            $filename = $this->generateExportFilename('selections');

            return $this->generateCsv($flattenedData, $this->defineCSVColumnsForTheFlattenedData(), $filename);
        } catch (Exception $e) {
            Log::error('CSV Export Error - Selections', [
                'error' => $e->getMessage(),
                'component' => get_class($this),
            ]);

            session()?->flash('error', 'Failed to export CSV. Please try again.');

            return back();
        }
    }

    /**
     * Initialize endpoint configuration
     */
    protected function initializeEndpoint(): void
    {
        $this->tableColumns = [
            ['field' => 'name', 'label' => 'Name'],
            ['field' => 'fpaRuleVersions.0.description', 'label' => 'Description'],
            ['field' => 'fpaRuleVersions.0.startEffectiveDate', 'label' => 'Start Date'],
            ['field' => 'fpaRuleVersions.0.endEffectiveDate', 'label' => 'End Date'],
            ['field' => 'job_names', 'label' => 'Jobs'],
            ['field' => 'paycode_names', 'label' => 'Pay Codes'],
        ];

        $this->initializePaginationData();
    }

    /**
     * Override the storeData method to include custom processing
     */
    protected function storeData(array $data): void
    {
        // Process data to add custom fields
        $processedData = $this->processDataForDisplay($data);

        $this->tableData = $processedData;
        $this->totalRecords = count($processedData);

        // Cache the processed data
        if (! empty($this->cacheKey)) {
            cache()->put($this->cacheKey, collect($processedData), now()->addMinutes(30));
        }
    }
}
