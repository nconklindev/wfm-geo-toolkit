<?php

namespace App\Services;

use App\Models\KnownIpAddress;
use App\Models\User;
use App\Notifications\KnownIpAddressNotification;
use Illuminate\Support\Facades\Log;

class IpAddressValidationService
{
    /**
     * Validate an IP address range and send notification if issues are detected.
     */
    public function validateAndNotify(KnownIpAddress $knownIpAddress): void
    {
        $issues = $this->detectIssues($knownIpAddress);
        
        if (!empty($issues)) {
            $this->sendNotification($knownIpAddress, $issues);
        }
    }

    /**
     * Detect various issues with an IP address range.
     */
    public function detectIssues(KnownIpAddress $knownIpAddress): array
    {
        $issues = [];
        
        // Convert IP addresses to long integers for comparison
        $startLong = ip2long($knownIpAddress->start);
        $endLong = ip2long($knownIpAddress->end);
        
        // Check if IPs are valid
        if ($startLong === false || $endLong === false) {
            $issues[] = [
                'type' => 'invalid_ip',
                'severity' => 'critical',
                'message' => 'Invalid IP address format detected'
            ];
            return $issues; // Return early if IPs are invalid
        }
        
        // Check if start IP is greater than end IP
        if ($startLong > $endLong) {
            $issues[] = [
                'type' => 'inverted_range',
                'severity' => 'critical',
                'message' => 'Start IP address is greater than end IP address'
            ];
        }
        
        // Calculate range size
        $rangeSize = $endLong - $startLong + 1;
        
        // Check for suspiciously large ranges
        if ($rangeSize > 16777216) { // Larger than a /8 network (16M+ addresses)
            $issues[] = [
                'type' => 'extremely_large_range',
                'severity' => 'warning',
                'message' => "Extremely large IP range detected ({$rangeSize} addresses). This covers more than a /8 network."
            ];
        } elseif ($rangeSize > 65536) { // Larger than a /16 network (64K+ addresses)
            $issues[] = [
                'type' => 'large_range',
                'severity' => 'info',
                'message' => "Large IP range detected ({$rangeSize} addresses). Consider if this range is intentional."
            ];
        }
        
        // Check for private IP ranges mixed with public
        $startIsPrivate = $this->isPrivateIp($knownIpAddress->start);
        $endIsPrivate = $this->isPrivateIp($knownIpAddress->end);
        
        if ($startIsPrivate !== $endIsPrivate) {
            $issues[] = [
                'type' => 'mixed_private_public',
                'severity' => 'warning',
                'message' => 'IP range spans both private and public address spaces'
            ];
        }
        
        // Check for reserved/special IP ranges
        if ($this->containsReservedIps($knownIpAddress->start, $knownIpAddress->end)) {
            $issues[] = [
                'type' => 'contains_reserved',
                'severity' => 'warning',
                'message' => 'IP range contains reserved or special-use addresses'
            ];
        }
        
        // Check for overlapping ranges with other known IP addresses
        $overlaps = $this->findOverlappingRanges($knownIpAddress);
        if (!empty($overlaps)) {
            $issues[] = [
                'type' => 'overlapping_ranges',
                'severity' => 'warning',
                'message' => "IP range overlaps with {$overlaps->count()} other known IP address(es)",
                'overlapping_ranges' => $overlaps->pluck('name', 'id')->toArray()
            ];
        }
        
        // Check for single IP represented as range
        if ($rangeSize === 1 && $knownIpAddress->start === $knownIpAddress->end) {
            $issues[] = [
                'type' => 'single_ip_as_range',
                'severity' => 'info',
                'message' => 'Single IP address represented as a range'
            ];
        }
        
        return $issues;
    }

    /**
     * Check if an IP address is in private ranges.
     */
    private function isPrivateIp(string $ip): bool
    {
        $long = ip2long($ip);
        
        // 10.0.0.0/8
        if ($long >= ip2long('10.0.0.0') && $long <= ip2long('10.255.255.255')) {
            return true;
        }
        
        // 172.16.0.0/12
        if ($long >= ip2long('172.16.0.0') && $long <= ip2long('172.31.255.255')) {
            return true;
        }
        
        // 192.168.0.0/16
        if ($long >= ip2long('192.168.0.0') && $long <= ip2long('192.168.255.255')) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if the range contains reserved IP addresses.
     */
    private function containsReservedIps(string $start, string $end): bool
    {
        $startLong = ip2long($start);
        $endLong = ip2long($end);
        
        $reservedRanges = [
            ['start' => ip2long('0.0.0.0'), 'end' => ip2long('0.255.255.255')],     // "This" network
            ['start' => ip2long('127.0.0.0'), 'end' => ip2long('127.255.255.255')], // Loopback
            ['start' => ip2long('169.254.0.0'), 'end' => ip2long('169.254.255.255')], // Link-local
            ['start' => ip2long('224.0.0.0'), 'end' => ip2long('255.255.255.255')], // Multicast + Reserved
        ];
        
        foreach ($reservedRanges as $reserved) {
            // Check if ranges overlap
            if ($startLong <= $reserved['end'] && $endLong >= $reserved['start']) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Find overlapping IP ranges for the same user.
     */
    private function findOverlappingRanges(KnownIpAddress $knownIpAddress)
    {
        $startLong = ip2long($knownIpAddress->start);
        $endLong = ip2long($knownIpAddress->end);
        
        return KnownIpAddress::where('user_id', $knownIpAddress->user_id)
            ->where('id', '!=', $knownIpAddress->id)
            ->get()
            ->filter(function ($other) use ($startLong, $endLong) {
                $otherStartLong = ip2long($other->start);
                $otherEndLong = ip2long($other->end);
                
                // Check if ranges overlap
                return $startLong <= $otherEndLong && $endLong >= $otherStartLong;
            });
    }

    /**
     * Send notification to the user about IP address issues.
     */
    private function sendNotification(KnownIpAddress $knownIpAddress, array $issues): void
    {
        $highestSeverity = $this->getHighestSeverity($issues);
        
        $message = $this->generateNotificationMessage($issues);
        
        $issueDetails = [
            'message' => $message,
            'status' => $this->mapSeverityToStatus($highestSeverity),
            'details' => [
                'issues' => $issues,
                'range_size' => ip2long($knownIpAddress->end) - ip2long($knownIpAddress->start) + 1,
                'detected_at' => now()->toISOString(),
            ]
        ];
        
        $knownIpAddress->user->notify(new KnownIpAddressNotification($knownIpAddress, $issueDetails));
        
        Log::info("IP address validation notification sent", [
            'known_ip_address_id' => $knownIpAddress->id,
            'user_id' => $knownIpAddress->user_id,
            'issues_count' => count($issues),
            'highest_severity' => $highestSeverity
        ]);
    }

    /**
     * Get the highest severity level from issues.
     */
    private function getHighestSeverity(array $issues): string
    {
        $severities = array_column($issues, 'severity');
        
        if (in_array('critical', $severities)) {
            return 'critical';
        } elseif (in_array('warning', $severities)) {
            return 'warning';
        } else {
            return 'info';
        }
    }

    /**
     * Generate a user-friendly notification message.
     */
    private function generateNotificationMessage(array $issues): string
    {
        $criticalIssues = array_filter($issues, fn($issue) => $issue['severity'] === 'critical');
        $warningIssues = array_filter($issues, fn($issue) => $issue['severity'] === 'warning');
        
        if (!empty($criticalIssues)) {
            return 'Critical issues detected with your IP address range that require immediate attention.';
        } elseif (!empty($warningIssues)) {
            return 'Potential issues detected with your IP address range that may need review.';
        } else {
            return 'Some observations about your IP address range that you might want to review.';
        }
    }

    /**
     * Map severity to notification status.
     */
    private function mapSeverityToStatus(string $severity): string
    {
        return match ($severity) {
            'critical' => 'Critical',
            'warning' => 'Warning',
            'info' => 'Info',
            default => 'Notification',
        };
    }
}