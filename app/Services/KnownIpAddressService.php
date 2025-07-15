<?php

namespace App\Services;

use App\Models\KnownIpAddress;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

class KnownIpAddressService
{
    public function __construct() {}

    /**
     * Import IP addresses from an array of data
     *
     *
     * @throws Throwable
     */
    public function importFromArray(
        array $data,
        User $user,
        string $duplicateHandling = 'skip',
        string $matchBy = 'name',
        bool $validateIpRanges = true
    ): array {
        $results = [
            'imported' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        try {
            DB::transaction(function () use ($data, $user, $duplicateHandling, $matchBy, $validateIpRanges, &$results) {
                foreach ($data as $index => $entry) {
                    $this->processEntry($entry, $index, $user, $duplicateHandling, $matchBy, $validateIpRanges,
                        $results);
                }
            });
        } catch (Exception $e) {
            Log::error('IP Address import failed: '.$e->getMessage());
            throw new RuntimeException('Import failed: '.$e->getMessage());
        }

        return $results;
    }

    /**
     * Process a single entry for import
     */
    protected function processEntry(
        array $entry,
        int $index,
        User $user,
        string $duplicateHandling,
        string $matchBy,
        bool $validateIpRanges,
        array &$results
    ): void {
        // Validate required fields
        $validator = Validator::make($entry, [
            'name' => 'required|string|max:255',
            'start' => 'required|string',
            'end' => 'required|string',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            $results['errors'][] = "Entry $index: ".implode(', ', $validator->errors()->all());
            $results['skipped']++;

            return;
        }

        // Validate IP addresses if the option is enabled
        if ($validateIpRanges && ! $this->validateIpRange($entry['start'], $entry['end'], $index, $results)) {
            $results['skipped']++;

            return;
        }

        // Check for existing entries
        $existing = $this->findExistingEntry($entry, $user, $matchBy);

        if ($existing) {
            $this->handleDuplicate($existing, $entry, $duplicateHandling, $results);
        } else {
            $this->createKnownIpAddress($entry, $user);
            $results['imported']++;
        }
    }

    /**
     * Validate IP range
     */
    protected function validateIpRange(string $startIp, string $endIp, int $index, array &$results): bool
    {
        if (! filter_var($startIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $results['errors'][] = "Entry $index: Invalid start IP address '$startIp'";

            return false;
        }

        if (! filter_var($endIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $results['errors'][] = "Entry $index: Invalid end IP address '$endIp'";

            return false;
        }

        // Check if start IP is less than or equal to end IP
        if (ip2long($startIp) > ip2long($endIp)) {
            $results['errors'][] = "Entry $index: Start IP must be less than or equal to end IP";

            return false;
        }

        return true;
    }

    /**
     * Find existing entry based on match criteria
     */
    public function findExistingEntry(array $entry, User $user, string $matchBy): ?KnownIpAddress
    {
        $query = $user->knownIpAddresses();

        return match ($matchBy) {
            'name' => $query->where('name', $entry['name'])->first(),
            'ip_range' => $query->where('start', $entry['start'])
                ->where('end', $entry['end'])
                ->first(),
            'both' => $query->where('name', $entry['name'])
                ->where('start', $entry['start'])
                ->where('end', $entry['end'])
                ->first(),
            default => null,
        };
    }

    /**
     * Handle duplicate entries
     */
    protected function handleDuplicate(
        KnownIpAddress $existing,
        array $entry,
        string $duplicateHandling,
        array &$results
    ): void {
        switch ($duplicateHandling) {
            case 'skip':
                $results['skipped']++;
                break;

            case 'replace':
            case 'update':
                $this->updateKnownIpAddress($existing, $entry);
                $results['updated']++;
                break;
        }
    }

    /**
     * Create a new Known IP Address
     */
    public function createKnownIpAddress(array $data, User $user): KnownIpAddress
    {
        return $user->knownIpAddresses()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'start' => $data['start'],
            'end' => $data['end'],
        ]);
    }

    /**
     * Update an existing Known IP Address
     */
    public function updateKnownIpAddress(KnownIpAddress $knownIpAddress, array $data): KnownIpAddress
    {
        $knownIpAddress->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'start' => $data['start'],
            'end' => $data['end'],
        ]);

        return $knownIpAddress;
    }
}
