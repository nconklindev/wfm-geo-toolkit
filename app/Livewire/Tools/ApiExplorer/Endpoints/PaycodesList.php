<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiComponent;
use Illuminate\Support\Collection;

class PaycodesList extends BaseApiComponent
{
    public string $errorMessage = '';

    public string $sortField = 'name';

    public function getCacheKey(): string
    {
        $id = md5(session()->id());

        return 'paycodes_list_'.$id;
    }

    public function getCacheTtl(): int
    {
        return 3600;
    }

    public function transformForView(array $data): array
    {
        return array_map(static function ($item) {
            return [
                'name' => $item['name'] ?? 'Unknown',
                'description' => $item['description'] ?? '',
                'type' => $item['type'] ?? 'Unknown',
                'money' => $item['money'] ?? 'Unknown',
                'excusedAbsence' => $item['excusedAbsence'] ?? 'Unknown',
                'totals' => $item['totals'] ?? 'Unknown',
                'unit' => $item['unit'] ?? 'Unknown',
                'associatedDurationPayCode' => $item['associatedDurationPayCode'] ?? 'Unknown',
                'combined' => $item['combined'] ?? 'Unknown',
                'visibleToTimecardSchedule' => $item['visibleToTimecardSchedule'] ?? 'Unknown',
                'visibleToReports' => $item['visibleToReports'] ?? 'Unknown',
            ];
        }, $data);
    }

    protected function getApiParams(): array
    {
        return [];
    }

    protected function getDataKeyFromResponse(): ?string
    {
        return null;
    }

    protected function getTotalFromResponseData(array $data): ?int
    {
        return count($data);
    }

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

    protected function getCsvColumns(): array
    {
        return $this->getTableColumns();
    }

    protected function getTableColumns(): array
    {
        return [
            ['field' => 'name', 'label' => 'Name'],
            ['field' => 'description', 'label' => 'Description'],
            ['field' => 'type', 'label' => 'Type'],
            ['field' => 'money', 'label' => 'Money'],
            ['field' => 'excusedAbsence', 'label' => 'Excuses Absence'],
            ['field' => 'totals', 'label' => 'Include in Totals'],
            ['field' => 'unit', 'label' => 'Unit'],
            ['field' => 'associatedDurationPayCode.qualifier', 'label' => 'Associated Duration Pay Code'],
            ['field' => 'combined', 'label' => 'Combined'],
            ['field' => 'visibleToTimecardSchedule', 'label' => 'Visible in Timecard and Schedule'],
            ['field' => 'visibleToReports', 'label' => 'Visible in Reports'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getApiServiceCall(): callable
    {
        return fn (array $params) => $this->wfmService->getPaycodes($params);
    }
}
