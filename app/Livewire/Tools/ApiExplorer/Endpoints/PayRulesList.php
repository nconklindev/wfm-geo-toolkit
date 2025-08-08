<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiComponent;
use Illuminate\Support\Collection;

class PayRulesList extends BaseApiComponent
{
    public string $errorMessage = '';

    public string $sortField = 'pay_rule_name';

    public function getCacheKey(): string
    {
        $id = md5(session()->id());

        return "pay_rules_list_$id";
    }

    public function getCacheTtl(): int
    {
        return 3600; // 1 hour - not changed/updated often
    }

    /**
     * Transform data for table view - flattens effectivePayRules
     */
    public function transformForView(array $data): array
    {
        $transformedData = [];

        foreach ($data as $item) {
            // If the item has effectivePayRules, flatten each rule
            if (isset($item['effectivePayRules']) && is_array($item['effectivePayRules'])) {
                foreach ($item['effectivePayRules'] as $rule) {
                    $transformedData[] = $this->flattenPayRule($item, $rule);
                }
            } else {
                // If no effectivePayRules, just add the base item
                $transformedData[] = [
                    'pay_rule_name' => $item['name'] ?? '',
                    'pay_rule_id' => $item['id'] ?? '',
                    'persistent_id' => $item['persistentId'] ?? '',
                    'rule_id' => '',
                    'effective_date' => '',
                    'fixed_rule_name' => '',
                    'work_rule_name' => '',
                    'punch_interpretation_rule_name' => '',
                    'holiday_credit_rule_name' => '',
                    'transfer_rule_name' => '',
                    'pay_from_schedule' => '',
                    'holidays_count' => 0,
                    'holidays_list' => '',
                ];
            }
        }

        return $transformedData;
    }

    /**
     * Flatten a single pay rule with its parent data for table view
     */
    private function flattenPayRule(array $parentItem, array $rule): array
    {
        // Extract holidays information
        $holidays = $rule['holidays'] ?? [];
        $selectedHolidays = array_filter($holidays, fn ($h) => $h['selected'] ?? false);
        $holidayNames = array_map(fn ($h) => $h['holiday']['name'] ?? '', $selectedHolidays);

        return [
            'pay_rule_name' => $parentItem['name'] ?? '',
            'pay_rule_id' => $parentItem['id'] ?? '',
            'persistent_id' => $parentItem['persistentId'] ?? '',
            'rule_id' => $rule['id'] ?? '',
            'effective_date' => $rule['effectiveDate'] ?? '',
            'fixed_rule_name' => $rule['fixedRule']['name'] ?? '',
            'work_rule_name' => $rule['workRule']['name'] ?? '',
            'punch_interpretation_rule_name' => $rule['terminalRule']['name'] ?? '',
            'holiday_credit_rule_name' => is_array($rule['holidayCreditRule']) && empty($rule['holidayCreditRule'])
                ? ''
                : ($rule['holidayCreditRule']['name'] ?? ''),
            'transfer_rule_name' => $rule['transferRule']['name'] ?? '',
            'pay_from_schedule' => $rule['payFromSchedule'] ? 'Yes' : 'No',
            'holidays_count' => count($selectedHolidays),
            'holidays_list' => implode(', ', $holidayNames),
        ];
    }

    /**
     * Transform data for CSV export - includes additional fields for comprehensive export
     */
    public function transformForCsv(array $data): array
    {
        $transformedData = [];

        foreach ($data as $item) {
            // If the item has effectivePayRules, flatten each rule with all fields
            if (isset($item['effectivePayRules']) && is_array($item['effectivePayRules'])) {
                foreach ($item['effectivePayRules'] as $rule) {
                    $transformedData[] = $this->flattenPayRuleForCsv($item, $rule);
                }
            } else {
                // If no effectivePayRules, just add the base item with empty rule fields
                $transformedData[] = $this->createEmptyRuleRow($item);
            }
        }

        return $transformedData;
    }

    /**
     * Flatten a single pay rule with comprehensive data for CSV export
     */
    private function flattenPayRuleForCsv(array $parentItem, array $rule): array
    {
        // Extract holidays information
        $holidays = $rule['holidays'] ?? [];
        $selectedHolidays = array_filter($holidays, fn ($h) => $h['selected'] ?? false);
        $holidayNames = array_map(fn ($h) => $h['holiday']['name'] ?? '', $selectedHolidays);

        return [
            'pay_rule_name' => $parentItem['name'] ?? '',
            'pay_rule_id' => $parentItem['id'] ?? '',
            'persistent_id' => $parentItem['persistentId'] ?? '',
            'rule_id' => $rule['id'] ?? '',
            'effective_date' => $rule['effectiveDate'] ?? '',
            'fixed_rule_id' => $rule['fixedRule']['id'] ?? '',
            'fixed_rule_name' => $rule['fixedRule']['name'] ?? '',
            'work_rule_id' => $rule['workRule']['id'] ?? '',
            'work_rule_name' => $rule['workRule']['name'] ?? '',
            'punch_interpretation_rule_id' => $rule['terminalRule']['id'] ?? '',
            'punch_interpretation_rule_name' => $rule['terminalRule']['name'] ?? '',
            'holiday_credit_rule_id' => is_array($rule['holidayCreditRule']) && empty($rule['holidayCreditRule'])
                ? ''
                : ($rule['holidayCreditRule']['id'] ?? ''),
            'holiday_credit_rule_name' => is_array($rule['holidayCreditRule']) && empty($rule['holidayCreditRule'])
                ? ''
                : ($rule['holidayCreditRule']['name'] ?? ''),
            'transfer_rule_id' => $rule['transferRule']['id'] ?? '',
            'transfer_rule_name' => $rule['transferRule']['name'] ?? '',
            'pay_from_schedule' => $rule['payFromSchedule'] ? 'Yes' : 'No',
            'cost_center_transfers' => $rule['costCenterLaborCategoryAndJobTransfers'] ? 'Yes' : 'No',
            'schedule_total' => $rule['scheduleTotal'] ? 'Yes' : 'No',
            'transfer_regular_home' => $rule['transferRegularHome'] ? 'Yes' : 'No',
            'work_rule_transfers' => $rule['workRuleTransfers'] ? 'Yes' : 'No',
            'corrections_apply_date' => $rule['correctionsApplyDate'] ?? '',
            'enable_edits_after' => $rule['enableEditsAfter'] ?? '',
            'apply_only' => $rule['applyOnly'] ? 'Yes' : 'No',
            'holidays_count' => count($selectedHolidays),
            'holidays_list' => implode(', ', $holidayNames),
            'apply_schedule_margins' => $rule['applyScheduleMargins'] ? 'Yes' : 'No',
            'prepopulate_project' => $rule['prepopulateProject'] ? 'Yes' : 'No',
            'update_this_version' => $rule['updateThisVersion'] ? 'Yes' : 'No',
            'is_today_for_previous_pay_period' => $rule['isTodayForPreviousPayPeriod'] ? 'Yes' : 'No',
            'cancel_pfs_on_holidays' => $rule['cancelPFSOnHolidays'] ? 'Yes' : 'No',
            'cancel_pfs_edits_only_on_holidays' => $rule['cancelPFSEditsOnlyOnHolidays'] ? 'Yes' : 'No',
            'cancel_pfs_shifts_only_on_holidays' => $rule['cancelPFSShiftsOnlyOnHolidays'] ? 'Yes' : 'No',
        ];
    }

    /**
     * Create an empty rule row for items without effectivePayRules
     */
    private function createEmptyRuleRow(array $item): array
    {
        return [
            'pay_rule_name' => $item['name'] ?? '',
            'pay_rule_id' => $item['id'] ?? '',
            'persistent_id' => $item['persistentId'] ?? '',
            'rule_id' => '',
            'effective_date' => '',
            'fixed_rule_id' => '',
            'fixed_rule_name' => '',
            'work_rule_id' => '',
            'work_rule_name' => '',
            'punch_interpretation_rule_id' => '',
            'punch_interpretation_rule_name' => '',
            'holiday_credit_rule_id' => '',
            'holiday_credit_rule_name' => '',
            'transfer_rule_id' => '',
            'transfer_rule_name' => '',
            'pay_from_schedule' => '',
            'cost_center_transfers' => '',
            'schedule_total' => '',
            'transfer_regular_home' => '',
            'work_rule_transfers' => '',
            'corrections_apply_date' => '',
            'enable_edits_after' => '',
            'apply_only' => '',
            'holidays_count' => 0,
            'holidays_list' => '',
            'apply_schedule_margins' => '',
            'prepopulate_project' => '',
            'update_this_version' => '',
            'is_today_for_previous_pay_period' => '',
            'cancel_pfs_on_holidays' => '',
            'cancel_pfs_edits_only_on_holidays' => '',
            'cancel_pfs_shifts_only_on_holidays' => '',
        ];
    }

    protected function getApiParams(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    protected function getApiServiceCall(): callable
    {
        return fn ($params) => $this->wfmService->getPayRules($params);
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
            ['field' => 'pay_rule_name', 'label' => 'Pay Rule Name'],
            ['field' => 'pay_rule_id', 'label' => 'Pay Rule ID'],
            ['field' => 'rule_id', 'label' => 'Effective Rule ID'],
            ['field' => 'effective_date', 'label' => 'Effective Date'],
            ['field' => 'fixed_rule_name', 'label' => 'Fixed Rule'],
            ['field' => 'work_rule_name', 'label' => 'Work Rule'],
            ['field' => 'punch_interpretation_rule_name', 'label' => 'Punch Interpretation Rule'],
            ['field' => 'holiday_credit_rule_name', 'label' => 'Holiday Credit Rule'],
            ['field' => 'pay_from_schedule', 'label' => 'Pay From Schedule'],
            ['field' => 'holidays_count', 'label' => 'Holidays Count'],
            ['field' => 'holidays_list', 'label' => 'Selected Holidays'],
        ];
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
     * {@inheritDoc}
     */
    protected function getCsvColumns(): array
    {
        return [
            ['field' => 'pay_rule_name', 'label' => 'Pay Rule Name'],
            ['field' => 'pay_rule_id', 'label' => 'Pay Rule ID'],
            ['field' => 'persistent_id', 'label' => 'Persistent ID'],
            ['field' => 'rule_id', 'label' => 'Effective Rule ID'],
            ['field' => 'effective_date', 'label' => 'Effective Date'],
            ['field' => 'fixed_rule_id', 'label' => 'Fixed Rule ID'],
            ['field' => 'fixed_rule_name', 'label' => 'Fixed Rule Name'],
            ['field' => 'work_rule_id', 'label' => 'Work Rule ID'],
            ['field' => 'work_rule_name', 'label' => 'Work Rule Name'],
            ['field' => 'punch_interpretation_rule_id', 'label' => 'Terminal Rule ID'],
            ['field' => 'punch_interpretation_rule_name', 'label' => 'Terminal Rule Name'],
            ['field' => 'holiday_credit_rule_id', 'label' => 'Holiday Credit Rule ID'],
            ['field' => 'holiday_credit_rule_name', 'label' => 'Holiday Credit Rule Name'],
            ['field' => 'transfer_rule_id', 'label' => 'Transfer Rule ID'],
            ['field' => 'transfer_rule_name', 'label' => 'Transfer Rule Name'],
            ['field' => 'pay_from_schedule', 'label' => 'Pay From Schedule'],
            ['field' => 'cost_center_transfers', 'label' => 'Cost Center Transfers'],
            ['field' => 'schedule_total', 'label' => 'Schedule Total'],
            ['field' => 'transfer_regular_home', 'label' => 'Transfer Regular Home'],
            ['field' => 'work_rule_transfers', 'label' => 'Work Rule Transfers'],
            ['field' => 'corrections_apply_date', 'label' => 'Corrections Apply Date'],
            ['field' => 'enable_edits_after', 'label' => 'Enable Edits After'],
            ['field' => 'apply_only', 'label' => 'Apply Only'],
            ['field' => 'holidays_count', 'label' => 'Holidays Count'],
            ['field' => 'holidays_list', 'label' => 'Selected Holidays'],
            ['field' => 'apply_schedule_margins', 'label' => 'Apply Schedule Margins'],
            ['field' => 'prepopulate_project', 'label' => 'Prepopulate Project'],
            ['field' => 'update_this_version', 'label' => 'Update This Version'],
            ['field' => 'is_today_for_previous_pay_period', 'label' => 'Is Today For Previous Pay Period'],
            ['field' => 'cancel_pfs_on_holidays', 'label' => 'Cancel PFS On Holidays'],
            ['field' => 'cancel_pfs_edits_only_on_holidays', 'label' => 'Cancel PFS Edits Only On Holidays'],
            ['field' => 'cancel_pfs_shifts_only_on_holidays', 'label' => 'Cancel PFS Shifts Only On Holidays'],
        ];
    }
}
