@props([
    'response' => null,
    'error' => null,
    'title' => 'Response',
    'showBorder' => true,
    'compact' => false,
    'rawJsonCacheKey' => null,
])

@php
    $statusCode = $response['status'] ?? null;

    $color = match (true) {
        $statusCode >= 200 && $statusCode < 300 => 'green',
        $statusCode >= 400 && $statusCode < 500 => 'amber',
        $statusCode >= 500 => 'red',
        default => 'zinc',
    };

    // Generate unique ID for this response component
    $responseId = 'response-' . uniqid();
@endphp

@if ($response || $error)
    <div
        @class([
            'border-t border-zinc-200 pt-6 dark:border-zinc-700' => $showBorder,
            'space-y-4' => ! $compact,
            'space-y-2' => $compact,
        ])
    >
        <!-- Header section -->
        <flux:heading size="md" class="inline-flex">Response</flux:heading>

        @if ($error)
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                <div class="flex items-start space-x-2">
                    <flux:icon.exclamation-triangle class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-500" />
                    <div class="min-w-0 flex-1">
                        <flux:heading size="sm" class="text-red-800 dark:text-red-200">Error</flux:heading>
                        <flux:text size="sm" class="break-words text-red-700 dark:text-red-300">
                            {{ $error }}
                        </flux:text>
                    </div>
                </div>
            </div>
        @endif

        @if ($response)
            <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                <!-- Response Headers -->
                <div class="border-b border-zinc-200 px-4 py-2 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <flux:text size="sm" variant="subtle">Response Summary</flux:text>
                        <div class="flex items-center space-x-2">
                            @if (isset($response['status']))
                                <flux:badge :color="$color" size="sm">
                                    {{ $response['status'] }}
                                </flux:badge>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Response Body -->
                <div class="relative p-4">
                    @if (isset($response['data']['click_to_view']))
                        <!-- Summary view -->
                        <div class="space-y-2">
                            <div class="flex items-center space-x-2">
                                <flux:icon.check-circle class="h-5 w-5 text-green-500" />
                                <flux:text size="sm" class="font-medium">
                                    {{ $response['data']['message'] ?? 'Request completed successfully' }}
                                </flux:text>
                            </div>

                            @if (isset($response['data']['record_count']))
                                <flux:text size="sm" variant="subtle">
                                    Records: {{ number_format($response['data']['record_count']) }}
                                </flux:text>
                            @endif

                            <flux:text size="sm" variant="subtle" class="italic">
                                {{ $response['data']['click_to_view'] ?? 'Click "Show Raw JSON" below to view full response' }}
                            </flux:text>
                        </div>
                    @else
                        <!-- Error responses show immediately -->
                        <pre
                            id="{{ $responseId }}"
                            class="overflow-x-auto text-xs break-words whitespace-pre-wrap"
                        ><code>{{ json_encode($response['data'] ?? ($response['body'] ?? $response), JSON_PRETTY_PRINT) }}</code></pre>

                        <flux:tooltip content="Copy to clipboard">
                            <flux:button
                                icon="copy"
                                variant="ghost"
                                size="sm"
                                class="absolute! top-2 right-2 opacity-70 transition-opacity hover:opacity-100"
                                onclick="copyToClipboard('{{ $responseId }}', this)"
                            />
                        </flux:tooltip>
                    @endif
                </div>
            </div>
        @endif

        <!-- Raw JSON Viewer positioned below the response -->
        @if ($response && $rawJsonCacheKey)
            <livewire:tools.api-explorer.raw-json-viewer
                :cache-key="$rawJsonCacheKey"
                wire:key="raw-json-{{ $rawJsonCacheKey }}"
            />
        @endif

        {{ $slot }}
    </div>
@endif

<script>
    function copyToClipboard(elementId, button) {
        const element = document.getElementById(elementId);
        const text = element.textContent;

        navigator.clipboard
            .writeText(text)
            .then(() => {
                // Show feedback
                const originalIcon = button.innerHTML;
                button.innerHTML =
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';

                setTimeout(() => {
                    button.innerHTML = originalIcon;
                }, 2000);
            })
            .catch((err) => {
                console.error('Failed to copy: ', err);
            });
    }
</script>
