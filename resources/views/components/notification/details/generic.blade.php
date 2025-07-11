{{-- resources/views/components/notification/details/generic.blade.php --}}

@php
    // Extract any additional details that might be available
    $additionalDetails = $details['details'] ?? [];

    // Remove common fields from additional details display
    $commonFields = ['status', 'message', 'known_place_id', 'known_place_name', 'known_ip_address_id', 'known_ip_address_name', 'start', 'end'];
    $displayDetails = collect($details)
        ->except($commonFields)
        ->filter(function ($value) {
            return ! is_null($value) && $value !== '' && $value !== [];
        });
@endphp

<div class="space-y-4">
    @if ($displayDetails->isNotEmpty())
        <div>
            <flux:heading level="3" size="md" class="mb-3 border-b pb-2 dark:border-zinc-600">
                Notification Details
            </flux:heading>

            <div class="space-y-2">
                @foreach ($displayDetails as $key => $value)
                    <div class="text-sm">
                        <span class="font-medium text-zinc-700 capitalize dark:text-zinc-300">
                            {{ str_replace('_', ' ', $key) }}:
                        </span>
                        <span class="text-zinc-600 dark:text-zinc-400">
                            @if (is_array($value))
                                @if (empty($value))
                                    <em>None</em>
                                @else
                                    <ul class="mt-1 list-inside list-disc pl-4">
                                        @foreach ($value as $item)
                                            <li>
                                                @if (is_array($item))
                                                    {{ json_encode($item) }}
                                                @else
                                                    {{ $item }}
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            @elseif (is_bool($value))
                                {{ $value ? 'Yes' : 'No' }}
                            @elseif (is_numeric($value))
                                {{ number_format($value) }}
                            @else
                                {{ $value }}
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <flux:text variant="subtle">
            This notification contains basic information only. No additional details are available.
        </flux:text>
    @endif

    {{-- Show raw data in development environment --}}
    @if (config('app.debug') && ! empty($additionalDetails))
        <div class="mt-6 border-t pt-4 dark:border-zinc-600">
            <details class="cursor-pointer">
                <summary class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                    Show Raw Notification Data (Debug)
                </summary>
                <pre class="mt-2 overflow-auto rounded bg-zinc-100 p-2 text-xs dark:bg-zinc-700">
{{ json_encode($details, JSON_PRETTY_PRINT) }}</pre
                >
            </details>
        </div>
    @endif
</div>
