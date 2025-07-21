<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiComponent;
use Illuminate\Support\Collection;

class PercentAllocationRulesList extends BaseApiComponent
{
    public string $errorMessage = '';

    public function getCacheKey(): string
    {
        $id = md5(session()?->id());

        return 'percent_allocation_rules_'.$id;
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
        return $this->flattenPercentAllocationRulesForCsv($data);
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

    protected function getApiParams(): array
    {
        return []; // No parameters needed for this endpoint
    }

    protected function getApiServiceCall(): callable
    {
        return fn ($params) => $this->wfmService->getPercentAllocationRules($params);
    }

    protected function getDataKeyFromResponse(): ?string
    {
        return null; // Data is at root level
    }

    protected function getTotalFromResponseData(array $data): ?int
    {
        return count($data);
    }

    protected function getTableColumns(): array
    {
        return [
            ['field' => 'id', 'label' => 'Rule ID'],
            ['field' => 'name', 'label' => 'Rule Name'],
            ['field' => 'persistentId', 'label' => 'Persistent ID'],
            ['field' => 'job_names', 'label' => 'Jobs'],
            ['field' => 'paycode_names', 'label' => 'Pay Codes'],
        ];
    }

    protected function getDataForCsvExport(): Collection
    {
        // First, try to get from cached data (much faster)
        if (! empty($this->data)) {
            return collect($this->data);
        }

        // Fall back to fetching fresh data if no cached data
        return collect($this->fetchDataFromApi());
    }

    protected function getCsvColumns(): array
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
     * Transform API data to add extracted job and paycode names for display
     */
    protected function transformApiData(array $data): Collection
    {
        return collect($data)->map(function ($item) {
            // Add extracted job and paycode names for table display
            $item['job_names'] = $this->extractJobNames($item);
            $item['paycode_names'] = $this->extractPaycodeNames($item);

            return $item;
        });
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
}
