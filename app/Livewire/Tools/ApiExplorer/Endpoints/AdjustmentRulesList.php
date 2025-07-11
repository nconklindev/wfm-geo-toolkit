<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiEndpoint;
use App\Traits\ExportsCsvData;
use App\Traits\PaginatesApiData;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Export all adjustment rules data as CSV
     */
    public function exportAllToCsv(): StreamedResponse|RedirectResponse
    {
        try {
            $response = $this->fetchData();

            if (! $response || ! $response->successful()) {
                session()->flash('error', 'Failed to fetch data for export.');

                return back();
            }

            $allData = $this->extractDataFromResponse($response);

            if (empty($allData)) {
                session()->flash('error', 'No data available to export.');

                return back();
            }

            // Process the data to add custom fields
            $processedData = $this->processDataForDisplay($allData);

            // Apply current search and sort filters
            $filteredData = $this->applyFiltersAndSort(collect($processedData));

            // Flatten the data for CSV export
            $flattenedData = $this->flattenAdjustmentRulesForCsv($filteredData);

            $filename = $this->generateExportFilename('adjustment-rules-all');

            return $this->exportAsCsv($flattenedData, $this->defineCSVColumnsForTheFlattenedData(), $filename);
        } catch (Exception $e) {
            Log::error('Error exporting all adjustment rules data', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            session()->flash('error', 'Failed to export data. Please try again.');

            return back();
        }
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
     * Export current page/filtered data as CSV
     */
    public function exportSelectionsToCsv(): StreamedResponse|RedirectResponse
    {
        $filename = $this->generateExportFilename('adjustment-rules-selections');

        // Get current page data
        $currentPageData = $this->getPaginatedData();

        if ($currentPageData->isEmpty()) {
            session()->flash('error', 'No data available to export.');

            return back();
        }

        // Flatten the data for CSV export
        $flattenedData = $this->flattenAdjustmentRulesForCsv(collect($currentPageData->items()));

        return $this->exportAsCsv($flattenedData, $this->defineCSVColumnsForTheFlattenedData(), $filename);
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
