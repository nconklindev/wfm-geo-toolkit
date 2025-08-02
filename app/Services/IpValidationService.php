<?php

namespace App\Services;

use App\IpAddressRange;

class IpValidationService
{
    public function __construct() {}

    public function validateMultipleRanges(array $ipRanges): array
    {
        $results = [];

        foreach ($ipRanges as $index => $ipRange) {
            $overlaps = $this->findOverlappingRanges($ipRange, $ipRanges, $index);
            $issues = $this->detectIssues($ipRange, $overlaps);

            $results[] = [
                'ip_range' => $ipRange,
                'issues' => $issues,
                'status' => $this->determineStatus($issues),
            ];
        }

        return $results;
    }

    public function findOverlappingRanges(IpAddressRange $targetRange, array $allRanges, int $excludeIndex): array
    {
        $overlaps = [];

        foreach ($allRanges as $index => $range) {
            if ($index === $excludeIndex) {
                continue;
            }

            if ($targetRange->overlapsWith($range)) {
                $overlaps[] = [
                    'index' => $index,
                    'name' => $range->name ?: 'Range #'.($index + 1),
                    'start' => $range->start,
                    'end' => $range->end,
                ];
            }
        }

        return $overlaps;
    }

    public function detectIssues(IpAddressRange $ipRange, array $overlappingRanges = []): array
    {
        $issues = [];

        // Check if IPs are valid
        if (! $ipRange->isValid()) {
            $issues[] = [
                'type' => 'invalid_ip',
                'severity' => 'critical',
                'message' => 'Invalid IP address format detected',
            ];

            return $issues; // Return early if IPs are invalid
        }

        // Check if start IP is greater than end IP
        if ($ipRange->isInvertedRange()) {
            $issues[] = [
                'type' => 'inverted_range',
                'severity' => 'critical',
                'message' => 'Start IP address is greater than end IP address',
            ];
        }

        $rangeSize = $ipRange->getRangeSize();
        if ($rangeSize > 16777216) { // /8 network threshold
            $issues[] = [
                'type' => 'extremely_large_range',
                'severity' => 'warning',
                'message' => "Extremely large IP range detected ($rangeSize addresses). This covers more than a /8 network.",
            ];
        } elseif ($rangeSize > 65536) { // /16 network threshold
            $issues[] = [
                'type' => 'large_range',
                'severity' => 'info',
                'message' => "Large IP range detected ($rangeSize addresses). Consider if this range is intentional.",
            ];
        }

        if ($ipRange->spansMixedNetworks()) {
            $issues[] = [
                'type' => 'mixed_private_public',
                'severity' => 'warning',
                'message' => 'IP range spans both private and public address spaces',
            ];
        }

        if ($ipRange->containsReservedIps()) {
            $issues[] = [
                'type' => 'contains_reserved',
                'severity' => 'warning',
                'message' => 'IP range contains reserved or special-use addresses',
            ];
        }

        // Check if the entire range is private
        if ($ipRange->isPrivateRange()) {
            $issues[] = [
                'type' => 'private_ip_range',
                'severity' => 'warning',
                'message' => 'IP range is entirely within private address space',
            ];
        }

        // External collection logic
        if (! empty($overlappingRanges)) {
            $count = count($overlappingRanges);
            $issues[] = [
                'type' => 'overlapping_ranges',
                'severity' => 'warning',
                'message' => "IP range overlaps with $count other IP address range(s)",
                'overlapping_ranges' => $overlappingRanges,
            ];
        }

        // Business preference check
        if ($ipRange->isSingleIp()) {
            $issues[] = [
                'type' => 'single_ip_as_range',
                'severity' => 'info',
                'message' => 'Single IP address represented as a range',
            ];
        }

        return $issues;
    }

    public function determineStatus(array $issues): string
    {
        if (empty($issues)) {
            return 'valid';
        }

        $severities = array_column($issues, 'severity');

        if (in_array('critical', $severities, true)) {
            return 'critical';
        }

        if (in_array('warning', $severities, true)) {
            return 'warning';
        }

        return 'info';
    }

    public function generateSummary(array $validationResults): array
    {
        $total = count($validationResults);
        $validEntries = 0;
        $entriesWithWarnings = 0;
        $entriesWithErrors = 0;
        $totalIpCount = 0;
        $totalIssues = 0;
        $issueBreakdown = [];

        foreach ($validationResults as $result) {
            $ipRange = $result['ip_range'];
            $totalIpCount += $ipRange->getRangeSize();

            // Count total issues
            $issueCount = count($result['issues'] ?? []);
            $totalIssues += $issueCount;

            // Build issue breakdown
            foreach ($result['issues'] ?? [] as $issue) {
                $issueType = is_array($issue) ? ($issue['type'] ?? 'unknown') : 'unknown';
                $issueBreakdown[$issueType] = ($issueBreakdown[$issueType] ?? 0) + 1;
            }

            switch ($result['status']) {
                case 'valid':
                    $validEntries++;
                    break;
                case 'critical':
                    $entriesWithErrors++;
                    break;
                case 'warning':
                case 'info':
                    $entriesWithWarnings++;
                    break;
            }
        }

        return [
            'total_ranges' => $total,
            'total_ip_addresses' => $totalIpCount,
            'valid_ranges' => $validEntries,
            'ranges_with_warnings' => $entriesWithWarnings,
            'ranges_with_errors' => $entriesWithErrors,
            'total_issues' => $totalIssues,
            'issue_breakdown' => $issueBreakdown,

            // Keep the old keys for backwards compatibility
            'total_entries' => $total,
            'valid_entries' => $validEntries,
            'entries_with_warnings' => $entriesWithWarnings,
            'entries_with_errors' => $entriesWithErrors,
        ];
    }
}
