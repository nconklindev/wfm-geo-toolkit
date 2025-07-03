@props([
    'response' => null,
    'error' => null,
    'title' => 'Response',
    'showBorder' => true,
    'compact' => false,
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
        <flux:heading size="md" class="mb-4">{{ $title }}</flux:heading>

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
                        <flux:text size="sm" variant="subtle">Raw JSON Response</flux:text>
                        <div class="flex items-center space-x-2">
                            @if (isset($response['status']))
                                <flux:badge :color="$color" size="sm">
                                    {{ $response['status'] }}
                                </flux:badge>
                            @endif

                            @if (isset($response['headers']['content-type']))
                                <flux:text size="xs" variant="subtle">
                                    {{ $response['headers']['content-type'] }}
                                </flux:text>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Response Body -->
                <div class="relative p-4">
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
                </div>

                <!-- Additional Response Info -->
                @if (isset($response['execution_time']) || isset($response['size']))
                    <div class="border-t border-zinc-200 px-4 py-2 dark:border-zinc-700">
                        <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                            @if (isset($response['execution_time']))
                                <span>Execution time: {{ $response['execution_time'] }}ms</span>
                            @endif

                            @if (isset($response['size']))
                                <span>Size: {{ $response['size'] }} bytes</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{ $slot }}
    </div>
@endif
