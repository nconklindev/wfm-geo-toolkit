@php
    // Extract IP address specific data
    use Carbon\Carbon;
    $ipAddressId = $details['known_ip_address_id'] ?? null;
    $ipAddressName = $details['known_ip_address_name'] ?? 'Unknown IP Range';
    $startIp = $details['start'] ?? null;
    $endIp = $details['end'] ?? null;
    $issues = $details['issues'] ?? [];
    $rangeSize = $details['range_size'] ?? null;
    $detectedAt = $details['detected_at'] ?? null;

    // If issues are in details sub-array
    if (empty($issues) && isset($details['details']['issues'])) {
        $issues = $details['details']['issues'];
        $rangeSize = $rangeSize ?? ($details['details']['range_size'] ?? null);
        $detectedAt = $detectedAt ?? ($details['details']['detected_at'] ?? null);
    }
@endphp

{{-- IP Address Information --}}
<div class="mb-4">
    <flux:heading level="3">IP Address Range</flux:heading>
    <div class="mt-2 space-y-1">
        <flux:text>
            <span class="font-medium">Name:</span>
            {{ $ipAddressName }}
            @if ($ipAddressId)
                <flux:text variant="subtle" class="inline">(ID: {{ $ipAddressId }})</flux:text>
            @endif
        </flux:text>

        @if ($startIp && $endIp)
            <flux:text>
                <span class="font-medium">Range:</span>
                <code class="rounded bg-gray-100 px-1 py-0.5 text-xs dark:bg-gray-700">{{ $startIp }}</code>
                to
                <code class="rounded bg-gray-100 px-1 py-0.5 text-xs dark:bg-gray-700">{{ $endIp }}</code>
            </flux:text>
        @endif

        @if ($rangeSize)
            <flux:text>
                <span class="font-medium">Range Size:</span>
                {{ number_format($rangeSize) }} IP addresses
            </flux:text>
        @endif

        @if ($detectedAt)
            <flux:text variant="subtle">
                <span class="font-medium">Detected:</span>
                {{ Carbon::parse($detectedAt)->format('M j, Y g:i A') }}
            </flux:text>
        @endif
    </div>
</div>

<flux:separator class="my-4" />

{{-- Issues Details --}}
@if (! empty($issues))
    <div class="mb-4">
        <flux:heading level="3" size="md" class="mb-3">Detected Issues</flux:heading>
        <div class="space-y-3">
            @foreach ($issues as $issue)
                @php
                    $issueColor = match ($issue['severity'] ?? 'info') {
                        'critical' => 'red',
                        'warning' => 'orange',
                        'info' => 'blue',
                        default => 'gray',
                    };
                @endphp

                <div class="rounded border border-gray-200 p-3 dark:border-gray-700">
                    <div class="mb-2 flex items-center justify-between">
                        <flux:text weight="medium" class="capitalize">
                            {{ str_replace('_', ' ', $issue['type'] ?? 'Unknown Issue') }}
                        </flux:text>
                        <flux:badge size="sm" variant="pill" :color="$issueColor">
                            {{ ucfirst($issue['severity'] ?? 'info') }}
                        </flux:badge>
                    </div>

                    <flux:text class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $issue['message'] ?? 'No details available.' }}
                    </flux:text>

                    {{-- Show overlapping ranges if available --}}
                    @if (isset($issue['overlapping_ranges']) && ! empty($issue['overlapping_ranges']))
                        <div class="mt-2">
                            <flux:text class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                Overlapping with:
                            </flux:text>
                            <ul class="mt-1 list-inside list-disc pl-4 text-xs text-gray-600 dark:text-gray-400">
                                @foreach ($issue['overlapping_ranges'] as $id => $name)
                                    <li>{{ $name }} (ID: {{ $id }})</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@else
    <flux:text variant="subtle">No specific issues detected.</flux:text>
@endif

{{-- Additional Information --}}
@if ($startIp && $endIp)
    <div class="mt-6 border-t pt-4 dark:border-gray-600">
        <flux:heading level="4" size="sm" class="mb-2">Additional Information</flux:heading>
        <div class="space-y-1 text-xs text-gray-600 dark:text-gray-400">
            @php
                $startLong = ip2long($startIp);
                $endLong = ip2long($endIp);
                $isPrivateStart =
                    ($startLong >= ip2long('10.0.0.0') && $startLong <= ip2long('10.255.255.255')) ||
                    ($startLong >= ip2long('172.16.0.0') && $startLong <= ip2long('172.31.255.255')) ||
                    ($startLong >= ip2long('192.168.0.0') && $startLong <= ip2long('192.168.255.255'));
                $isPrivateEnd =
                    ($endLong >= ip2long('10.0.0.0') && $endLong <= ip2long('10.255.255.255')) ||
                    ($endLong >= ip2long('172.16.0.0') && $endLong <= ip2long('172.31.255.255')) ||
                    ($endLong >= ip2long('192.168.0.0') && $endLong <= ip2long('192.168.255.255'));
            @endphp

            <flux:text>
                <span class="font-medium">Address Type:</span>
                @if ($isPrivateStart && $isPrivateEnd)
                    Private Network Range
                @elseif (! $isPrivateStart && ! $isPrivateEnd)
                    Public Network Range
                @else
                    Mixed Private/Public Range
                @endif
            </flux:text>

            @if ($rangeSize)
                <flux:text>
                    <span class="font-medium">Network Size:</span>
                    @if ($rangeSize <= 1)
                        Single Host
                    @elseif ($rangeSize <= 256)
                        Small Network (≤ /24)
                    @elseif ($rangeSize <= 65536)
                        Medium Network (≤ /16)
                    @elseif ($rangeSize <= 16777216)
                        Large Network (≤ /8)
                    @else
                        Extremely Large Network (> /8)
                    @endif
                </flux:text>
            @endif
        </div>
    </div>
@endif

{{-- Detail Actions --}}
@if ($ipAddressId)
    <div class="mt-6 border-t pt-4 dark:border-gray-600">
        <flux:button
            href="{{ route('known-ip-addresses.edit', $ipAddressId) }}"
            target="_blank"
            size="xs"
            icon="pencil-square"
        >
            Edit IP Address Range
        </flux:button>
    </div>
@endif
