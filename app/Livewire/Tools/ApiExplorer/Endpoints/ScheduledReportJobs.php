<?php

namespace App\Livewire\Tools\ApiExplorer\Endpoints;

use App\Livewire\Tools\ApiExplorer\BaseApiComponent;
use Illuminate\Support\Collection;

class ScheduledReportJobs extends BaseApiComponent
{
    public string $sortField = 'reportName';

    public string $errorMessage = '';

    public function getCacheKey(): string
    {
        $id = md5(session()->id());

        return "scheduled_report_jobs_$id";
    }

    public function getCacheTtl(): int
    {
        return 900; // 15 minutes
    }

    /**
     * Transform data for CSV export
     */
    public function transformForCsv(array $data): array
    {
        return $this->transformForView($data);
    }

    /**
     * Transform data for the table view - extract most important fields
     */
    public function transformForView(array $data): array
    {
        return collect($data)->map(function ($report) {
            if (! is_array($report)) {
                return null;
            }

            // Extract report parameters
            $parameters = $this->extractReportParameters($report['reportParameter'] ?? []);

            return [
                'id' => $report['id'] ?? 'N/A',
                'reportName' => $report['reportName'] ?? 'Unknown',
                'title' => $report['title'] ?? '',
                'description' => $report['description'] ?? '',
                'reportRequestName' => $report['reportRequestName'] ?? '',
                'sendAttachment' => isset($report['sendAttachment']) ? ($report['sendAttachment'] ? 'Yes' : 'No') : 'N/A',
                'recipientCount' => isset($report['recipients']) && is_array($report['recipients']) ? count($report['recipients']) : 0,
                'frequency' => $report['schedulingInfo']['frequency'] ?? 'N/A',
                'startTime' => $report['schedulingInfo']['startTime'],
                'startDateTime' => $report['schedulingInfo']['startDateTime'] ?? 'N/A',
                'endDateTime' => $report['schedulingInfo']['endDateTime'] ?? 'N/A',
                'forever' => isset($report['schedulingInfo']['forever']) ? ($report['schedulingInfo']['forever'] ? 'Yes' : 'No') : 'N/A',
                'scheduledBy' => $report['scheduledBy']['qualifier'] ?? 'N/A',
                'runAs' => $report['runAs']['qualifier'] ?? 'N/A',
                // Add parameter fields
                'dateRange' => $parameters['dateRange'],
                'dataSource' => $parameters['dataSource'],
                'outputFormat' => $parameters['outputFormat'],
            ];
        })->filter()->toArray(); // Remove null entries
    }

    /**
     * Extract and format report parameters
     */
    private function extractReportParameters(array $reportParameter): array
    {
        $parameters = [
            'dateRange' => 'N/A',
            'dataSource' => 'N/A',
            'outputFormat' => 'N/A',
            'priority' => 'N/A',
        ];

        if (! isset($reportParameter['parameters']) || ! is_array($reportParameter['parameters'])) {
            return $parameters;
        }

        foreach ($reportParameter['parameters'] as $param) {
            if (! isset($param['name']) || ! isset($param['value'])) {
                continue;
            }

            switch ($param['name']) {
                case 'DateRange':
                    $value = $param['value'];
                    if (isset($value['startDate']) && isset($value['endDate'])) {
                        $startDate = date('Y-m-d', strtotime($value['startDate']));
                        $endDate = date('Y-m-d', strtotime($value['endDate']));
                        $parameters['dateRange'] = "$startDate to $endDate";

                        // Add symbolic period info if available
                        if (isset($value['symbolicPeriod']['id'])) {
                            $parameters['dateRange'] .= " (Period: {$value['symbolicPeriod']['id']})";
                        }
                    }
                    break;

                case 'DataSource':
                    $value = $param['value'];
                    if (isset($value['savedLocations']['id'])) {
                        $parameters['dataSource'] = "Location ID: {$value['savedLocations']['id']}";
                    } elseif (isset($value['hyperfind']['id'])) {
                        $parameters['dataSource'] = "Hyperfind ID: {$value['hyperfind']['id']}";
                    }

                    break;

                case 'Output Format':
                    $value = $param['value'];
                    if (isset($value['title'])) {
                        $parameters['outputFormat'] = $value['title'];
                    } elseif (isset($value['key'])) {
                        $parameters['outputFormat'] = strtoupper($value['key']);
                    }
                    break;
            }
        }

        return $parameters;
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
        return fn ($params) => $this->wfmService->getScheduledReportJobs($params);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDataKeyFromResponse(): ?string
    {
        return 'reportRequests';
    }

    /**
     * {@inheritDoc}
     */
    protected function getTotalFromResponseData(array $data): ?int
    {
        if (isset($data['reportRequests']) && is_array($data['reportRequests'])) {
            return count($data['reportRequests']);
        }

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
            ['field' => 'id', 'label' => 'ID'],
            ['field' => 'reportName', 'label' => 'Report Name'],
            ['field' => 'title', 'label' => 'Title'],
            ['field' => 'description', 'label' => 'Description'],
            ['field' => 'sendAttachment', 'label' => 'Send Attachment'],
            ['field' => 'recipientCount', 'label' => 'Recipients'],
            ['field' => 'frequency', 'label' => 'Frequency'],
            ['field' => 'startTime', 'label' => 'Start Time'],
            ['field' => 'startDateTime', 'label' => 'Start Date'],
            ['field' => 'endDateTime', 'label' => 'End Date'],
            ['field' => 'forever', 'label' => 'Forever'],
            ['field' => 'scheduledBy', 'label' => 'Scheduled By'],
            ['field' => 'runAs', 'label' => 'Run As'],
            // Add parameter columns
            ['field' => 'dateRange', 'label' => 'Date Range'],
            ['field' => 'dataSource', 'label' => 'Data Source'],
            ['field' => 'outputFormat', 'label' => 'Output Format'],
        ];
    }
}
