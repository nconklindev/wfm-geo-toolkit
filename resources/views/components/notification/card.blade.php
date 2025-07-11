{{-- resources/views/components/notification/card.blade.php --}}

@props([
    'notification',
])

@php
    $isUnread = $notification->unread();
    $status = $notification->data['status'] ?? 'Notification';

    // Support both Known Place and Known IP Address notifications
    $title =
        $notification->data['known_place_name'] ??
        ($notification->data['known_ip_address_name'] ?? 'Notification');

    $message = $notification->data['message'] ?? 'No message content.';

    $badgeColor = match ($status) {
        'Possible Conflict' => 'yellow',
        'Needs Attention' => 'red',
        'Warning' => 'orange',
        'Critical' => 'red',
        'Success' => 'green',
        'Info' => 'blue',
        default => 'zinc',
    };
@endphp

<div
    {{ $attributes->merge(['class' => 'group block w-full cursor-pointer border-b border-zinc-200 p-4 transition duration-150 ease-in-out hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-700']) }}
>
    <div class="flex items-start space-x-4">
        <div class="flex-shrink-0 pt-1">
            @if ($isUnread)
                <span class="inline-block h-2 w-2 rounded-full bg-sky-500" title="Unread"></span>
            @else
                <span class="inline-block h-2 w-2 rounded-full bg-zinc-300 dark:bg-zinc-600" title="Read"></span>
            @endif
        </div>
        <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between">
                <flux:heading class="truncate">
                    {{ $title }}
                </flux:heading>
                <div class="flex items-center space-x-2">
                    <flux:badge size="sm" variant="pill" :color="$badgeColor">{{ $status }}</flux:badge>
                    <div class="group relative">
                        <div class="transition-opacity duration-150 ease-in-out group-hover:opacity-0">
                            <flux:text variant="subtle" title="{{ $notification->created_at->format('Y-m-d H:i:s') }}">
                                <time datetime="{{ $notification->created_at->toIso8601String() }}">
                                    {{ $notification->created_at->diffForHumans(null, true) }} ago
                                </time>
                            </flux:text>
                        </div>
                        <div
                            class="pointer-events-none absolute top-0 right-2 bottom-0 flex items-center opacity-0 transition-opacity duration-150 ease-in-out group-hover:pointer-events-auto group-hover:opacity-100"
                        >
                            <flux:button
                                variant="ghost"
                                size="xs"
                                icon="x-mark"
                                {{-- Or "trash" if that's your desired icon --}}
                                title="Delete notification"
                                tooltip="Delete notification"
                                class="cursor-pointer p-1"
                                wire:click.stop="deleteNotification('{{ $notification->id }}')"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <flux:text class="mt-1">
                {{ $message }}
            </flux:text>
        </div>
    </div>
</div>
