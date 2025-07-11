{{-- resources/views/components/notification/details.blade.php --}}

@props([
    'details',
])

@php
    // Ensure we're working with an array
    $details = is_array($details) ? $details : [];

    // Get status and message from the top level
    $status = $details['status'] ?? 'Notification';
    $message = $details['message'] ?? 'No additional information available.';

    // Determine notification type based on available data
    $notificationType = 'unknown';
    if (isset($details['known_place_id']) || isset($details['triggered_known_place'])) {
        $notificationType = 'known_place';
    } elseif (isset($details['known_ip_address_id']) || isset($details['start']) || isset($details['end'])) {
        $notificationType = 'known_ip_address';
    }

    // Badge color based on status
    $badgeColor = match ($status) {
        'Possible Conflict' => 'yellow',
        'Needs Attention', 'Critical' => 'red',
        'Warning' => 'orange',
        'Success' => 'green',
        'Info' => 'blue',
        default => 'zinc',
    };
@endphp

<div
    class="max-h-[calc(100vh-8rem)] overflow-y-auto rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800"
>
    {{-- Status and Message --}}
    <div class="mb-4">
        <div class="mb-2 flex items-center">
            <flux:heading level="3" size="sm" class="mr-2">Status:</flux:heading>
            <flux:badge size="sm" variant="pill" :color="$badgeColor">{{ $status }}</flux:badge>
        </div>
        <flux:text class="text-zinc-600 dark:text-zinc-300">{{ $message }}</flux:text>
    </div>

    {{-- Divider --}}
    <div class="my-4 border-t dark:border-zinc-600"></div>

    {{-- Known Place Notification Details --}}
    @if ($notificationType === 'known_place')
        @include('components.notification.details.known-place', ['details' => $details])

        {{-- Known IP Address Notification Details --}}
    @elseif ($notificationType === 'known_ip_address')
        @include('components.notification.details.known-ip-address', ['details' => $details])

        {{-- Generic/Unknown Notification Details --}}
    @else
        @include('components.notification.details.generic', ['details' => $details])
    @endif
</div>
