<div class="space-y-4">
    <!-- Toggle Button -->
    <div class="flex items-center justify-between">
        <flux:heading size="sm" variant="subtle">Raw JSON Response</flux:heading>
        <flux:button
            as="button"
            wire:click="toggleVisibility"
            variant="ghost"
            size="sm"
            :icon="$isVisible ? 'eye-slash' : 'eye'"
        >
            {{ $isVisible ? 'Hide' : 'Show' }} Raw JSON
        </flux:button>
    </div>

    @if ($isVisible)
        <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
            <!-- Response Headers -->
            <div class="border-b border-zinc-200 px-4 py-2 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <flux:text size="sm" variant="subtle">Raw JSON Response</flux:text>
                    <div class="flex items-center space-x-2">
                        @if ($jsonData)
                            <flux:badge color="green" size="sm">200</flux:badge>
                            <flux:text size="xs" variant="subtle">application/json</flux:text>
                        @elseif ($errorMessage)
                            <flux:badge color="red" size="sm">Error</flux:badge>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Response Body -->
            <div class="relative p-4">
                @if ($isLoading)
                    <div class="flex items-center justify-center py-8">
                        <flux:icon.loading class="h-6 w-6 animate-spin text-zinc-400" />
                        <span class="ml-2 text-sm text-zinc-500">Loading JSON data...</span>
                    </div>
                @elseif ($errorMessage)
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <div class="flex items-start space-x-2">
                            <flux:icon.exclamation-triangle class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-500" />
                            <div class="min-w-0 flex-1">
                                <flux:text size="sm" class="text-red-700 dark:text-red-300">
                                    {{ $errorMessage }}
                                </flux:text>
                            </div>
                        </div>
                    </div>
                @elseif ($jsonData)
                    <pre
                        id="json-viewer-{{ $this->getId() }}"
                        class="overflow-x-auto text-xs break-words whitespace-pre-wrap"
                    ><code>{{ json_encode($jsonData['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>

                    <div class="absolute top-2 right-2">
                        <flux:tooltip content="Copy to clipboard">
                            <flux:button
                                icon="copy"
                                variant="ghost"
                                size="sm"
                                class="opacity-70 transition-opacity hover:opacity-100"
                                onclick="copyToClipboard('json-viewer-{{ $this->getId() }}', this)"
                            />
                        </flux:tooltip>
                    </div>
                @endif
            </div>

            <!-- Additional Info -->
            @if ($jsonData && ! $errorMessage)
                <div class="border-t border-zinc-200 px-4 py-2 dark:border-zinc-700">
                    <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                        <div class="flex items-center space-x-4">
                            <span>Total records: {{ number_format($jsonData['total_records'] ?? 0) }}</span>
                            <span>Source: {{ $jsonData['loaded_from'] ?? 'cache' }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
