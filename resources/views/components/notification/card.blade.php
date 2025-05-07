{{-- resources/views/components/notification/card.blade.php --}}

@props([
    'notification',
])

@php
    $isUnread = $notification->unread();
    $status = $notification->data['status'] ?? 'Notification';
    $knownPlaceName = $notification->data['known_place_name'] ?? 'N/A';
    $message = $notification->data['message'] ?? 'No message content.';

    $badgeColor = match ($status) {
        'Possible Conflict' => 'yellow',
        'Needs Attention' => 'red',
        'Warning' => 'orange',
        'Critical' => 'red',
        'Success' => 'green',
        default => 'blue',
    };
@endphp

<div
    {{ $attributes->merge(['class' => 'block w-full cursor-pointer border-b border-gray-200 p-4 transition duration-150 ease-in-out hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700']) }}
>
    <div class="flex items-start space-x-4">
        <div class="flex-shrink-0 pt-1">
            @if ($isUnread)
                <span class="inline-block h-2 w-2 rounded-full bg-teal-500" title="Unread"></span>
            @else
                <span class="inline-block h-2 w-2 rounded-full bg-gray-300 dark:bg-gray-600" title="Read"></span>
            @endif
        </div>
        <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between">
                <flux:heading class="truncate">
                    {{ $knownPlaceName }}
                </flux:heading>
                <div class="flex items-center space-x-2">
                    <flux:badge size="sm" variant="pill" :color="$badgeColor">{{ $status }}</flux:badge>
                    <flux:text
                        class="text-sm text-gray-500 dark:text-gray-400"
                        title="{{ $notification->created_at->format('Y-m-d H:i:s') }}"
                    >
                        <time datetime="{{ $notification->created_at->toIso8601String() }}">
                            {{ $notification->created_at->diffForHumans(null, true) }} ago
                        </time>
                    </flux:text>
                </div>
            </div>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                {{ $message }}
            </p>
        </div>
    </div>
</div>
