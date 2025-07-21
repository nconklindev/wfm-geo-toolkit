<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiComponent;
use Illuminate\Support\Collection;

class AdjustmentRulesList extends BaseApiComponent
{
    public string $errorMessage = '';

    public function getCacheKey(): string
    {
        $id = md5(session()?->id());

        return 'adjustment_rules'.$id;
    }

    public function getCacheTtl(): int
    {
        return 3600; // 1 hour
    }

    /**
     * Transform data for CSV export (flattens nested structure)
     */
    public function transformForCsv(array $data): array
    {
        return $this->flattenAdjustmentRulesForCsv($data);
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
     * Transform data for view (adds extracted paycode names for display)
     */
    public function transformForView(array $data): array
    {
        return collect($data)->map(function ($item) {
            // Add extracted paycode names for table display
            $item['paycode_names'] = $this->extractPaycodeNames($item);

            return $item;
        })->toArray();
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

    protected function getApiParams(): array
    {
        return [
            'all_details' => 'true',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getApiServiceCall(): callable
    {
        return fn ($params) => $this->wfmService->getAdjustmentRules($params);
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
    protected function getTableColumns(): array
    {
        return [
            ['field' => 'id', 'label' => 'Rule ID'],
            ['field' => 'name', 'label' => 'Rule Name'],
            ['field' => 'paycode_names', 'label' => 'Pay Codes'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getDataForCsvExport(): Collection
    {
        // First, try to get from cached data (much faster)
        if (! empty($this->data)) {
            return collect($this->data);
        }

        // Fall back to fetching fresh data if no cached data
        return collect($this->fetchDataFromApi());
    }

    /**
     * {@inheritDoc}
     */
    protected function getCsvColumns(): array
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
}
