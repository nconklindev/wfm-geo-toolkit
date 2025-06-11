@php
    use App\Models\KnownPlace;
@endphp

@php
    use App\Models\BusinessStructureNode;
@endphp

@php
    use App\Models\KnownIpAddress;
@endphp

@props(['placeholder' => 'Search'])
<div class="relative">
    <div class="max-w-2xs">
        <flux:input
            type="text"
            wire:model.live.debounce.300ms="searchQuery"
            wire:keydown.enter.prevent
            wire:keydown.escape="resetSearch"
            icon="magnifying-glass"
            size="sm"
            class="text-sm"
            kbd="Ctrl K"
            :placeholder="$placeholder"
            clearable
            wire:blur="resetSearch"
        />
    </div>

    <!-- Search Results Dropdown -->
    @if (! empty($searchQuery) && strlen(trim($searchQuery)) >= 2)
        <div
            x-data
            wire:transition
            class="absolute top-full right-0 left-0 z-[500] mt-2 max-h-96 w-full overflow-y-auto rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
            @click.away="$wire.resetSearch()"
        >
            @if (isset($results) && $results->isEmpty())
                <div class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                    No results found matching "{{ $searchQuery }}"
                    @if ($searchTime)
                        <span class="text-xs text-zinc-400">({{ number_format($searchTime * 1000, 0) }}ms)</span>
                    @endif
                </div>
            @elseif (isset($results) && $results->isNotEmpty())
                {{-- Search results header with count and timing --}}
                @if ($searchTime)
                    <div
                        class="border-b border-zinc-100 px-4 py-2 text-xs text-zinc-400 dark:border-zinc-800 dark:text-zinc-500"
                    >
                        {{ $results->count() }} results ({{ number_format($searchTime * 1000, 0) }}ms)
                    </div>
                @endif

                @foreach ($results as $result)
                    @php
                        $modelType = class_basename($result);
                        $displayName = $this->getModelDisplayName($modelType);
                    @endphp

                    <div
                        class="cursor-pointer border-b border-zinc-100 px-4 py-2.5 text-sm last:border-0 hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800/50"
                        wire:click="openResult('{{ $modelType }}', {{ $result->id }})"
                        wire:key="{{ strtolower($modelType) }}-{{ $result->id }}"
                    >
                        <div class="flex items-center justify-between">
                            <span class="font-medium">{{ $result->name }}</span>
                            <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $displayName }}</span>
                        </div>

                        {{-- Model-specific additional information --}}
                        @if ($result instanceof KnownPlace)
                            @if (! empty($result->description))
                                <div class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $result->description }}
                                </div>
                            @endif

                            <div class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">
                                Lat: {{ $result->latitude }}, Long: {{ $result->longitude }}
                            </div>
                            @if (isset($result->group))
                                <div class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">
                                    Group: {{ $result->group->name }}
                                </div>
                            @endif
                        @elseif ($result instanceof BusinessStructureNode)
                            <div class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">
                                Path: {{ $result->path }}
                            </div>
                        @elseif ($result instanceof KnownIpAddress)
                            @if (! empty($result->description))
                                <div class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $result->description }}
                                </div>
                            @endif

                            <div class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">
                                {{ $result->start }} - {{ $result->end }}
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="px-4 py-3 text-sm text-zinc-400">
                    <div class="flex items-center space-x-2">
                        <div class="h-4 w-4 animate-spin rounded-full border-2 border-zinc-300 border-t-zinc-600"></div>
                        <span>Searching...</span>
                    </div>
                </div>
            @endif
        </div>
    @elseif (! empty($searchQuery) && strlen(trim($searchQuery)) < 2)
        <div
            class="absolute top-full right-0 left-0 z-[500] mt-2 rounded-lg border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-600 shadow-lg dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400"
        >
            Type at least 2 characters to search
        </div>
    @endif
</div>
