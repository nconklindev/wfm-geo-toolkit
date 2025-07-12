<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\ExportsCsvData;
use App\Traits\PaginatesApiData;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AdjustmentRulesList extends BaseApiEndpoint
{
    use ExportsCsvData;
    use PaginatesApiData;

    public function render(): View
    {
        $paginatedData = $this->getPaginatedData();

        return view('livewire.tools.api-explorer.endpoints.adjustment-rules-list', [
            'paginatedData' => $paginatedData,
        ]);
    }

    /**
     * Override exportAllToCsv to use flattened data structure
     */
    public function exportAllToCsv()
    {
        try {
            $allData = $this->getAllDataForExport();

            if ($allData->isEmpty()) {
                session()->flash('error', 'No data available to export.');

                return back();
            }

            // Apply current search and sort filters
            $filteredData = $this->applyFiltersAndSort($allData);

            // Flatten the data for CSV export
            $flattenedData = $this->flattenAdjustmentRulesForCsv($filteredData);

            $filename = $this->generateExportFilename('all');

            return $this->exportAsCsv($flattenedData, $this->defineCSVColumnsForTheFlattenedData(), $filename);
        } catch (Exception $e) {
            Log::error('CSV Export Error - All Data', [
                'error' => $e->getMessage(),
                'component' => get_class($this),
            ]);

            session()->flash('error', 'Failed to export data. Please try again.');

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
     * Fetch data from WFM API
     */
    protected function fetchData(): ?Response
    {
        return $this->makeAuthenticatedApiCall(function () {
            return $this->wfmService->getAdjustmentRules();
        });
    }

    /**
     * Process raw API data to add custom fields
     */
    protected function processDataForDisplay(array $data): array
    {
        return array_map(function ($item) {
            $item['paycode_names'] = $this->extractPaycodeNames($item);

            return $item;
        }, $data);
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

    /**
     * Custom filename generation for exports
     */
    protected function generateExportFilename(string $type): string
    {
        $parts = ['adjustment-rules', $type];

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
     * Initialize endpoint configuration
     */
    protected function initializeEndpoint(): void
    {
        $this->tableColumns = [
            ['field' => 'name', 'label' => 'Name'],
            ['field' => 'ruleVersions.adjustmentRuleVersion.0.description', 'label' => 'Description'],
            ['field' => 'ruleVersions.adjustmentRuleVersion.0.effectiveDate', 'label' => 'Effective Date'],
            ['field' => 'ruleVersions.adjustmentRuleVersion.0.expirationDate', 'label' => 'Expiration Date'],
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

    /**
     * Override exportSelectionsToCsv to use flattened data structure
     */
    public function exportSelectionsToCsv()
    {
        try {
            $exportData = $this->getAllData();
            $filteredData = $this->applyFiltersAndSort($exportData);

            if ($filteredData->isEmpty()) {
                session()->flash('error', 'No data available to export.');

                return back();
            }

            // Flatten the data for CSV export
            $flattenedData = $this->flattenAdjustmentRulesForCsv($filteredData);

            $filename = $this->generateExportFilename('selections');

            return $this->exportAsCsv($flattenedData, $this->defineCSVColumnsForTheFlattenedData(), $filename);
        } catch (Exception $e) {
            Log::error('CSV Export Error - Selections', [
                'error' => $e->getMessage(),
                'component' => get_class($this),
            ]);

            session()->flash('error', 'Failed to export CSV. Please try again.');

            return back();
        }
    }
}
