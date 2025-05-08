@php
    use App\Models\BusinessStructureNode;
    use App\Models\KnownPlace;
@endphp

@php
    use App\Models\Location;
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
            {{-- TODO: This doesn't work --}}
            :placeholder="$placeholder"
            clearable
            wire:blur="resetSearch"
        />
    </div>

    <!-- Search Results Dropdown -->
    {{-- Only render the dropdown container if there's a search query --}}
    @if (! empty($searchQuery))
        <div
            x-data
            wire:transition
            {{-- Ensure absolute positioning relative to the parent div --}}
            {{-- Use left-0 and right-0 to span the width --}}
            class="absolute top-full right-0 left-0 z-[500] mt-2 max-h-96 w-full overflow-y-auto rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
            @click.away="$wire.resetSearch()"
            {{-- Close when clicking outside --}}
        >
            {{-- Check if results collection is loaded and empty --}}
            @if (isset($results) && $results->isEmpty())
                <div class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                    No results found matching "{{ $searchQuery }}"
                </div>
                {{-- Check if results collection is loaded and has items --}}
            @elseif (isset($results) && $results->isNotEmpty())
                @foreach ($results as $result)
                    {{-- Use instanceof to render different result types --}}
                    @if ($result instanceof KnownPlace)
                        <div
                            class="cursor-pointer border-b border-zinc-100 px-4 py-2.5 text-sm last:border-0 hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800/50"
                            wire:click="openResult('KnownPlace', {{ $result->id }})"
                            wire:key="known-place-{{ $result->id }}"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-medium">{{ $result->name }}</span>
                                <span class="text-xs text-zinc-400 dark:text-zinc-500">Place</span>
                            </div>
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
                                    Group: {{ $result->name }}
                                </div>
                            @endif
                        </div>
                    @elseif ($result instanceof BusinessStructureNode)
                        <div
                            class="cursor-pointer border-b border-zinc-100 px-4 py-2.5 text-sm last:border-0 hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800/50"
                            wire:click="openResult('BusinessStructureNode', {{ $result->id }})"
                            wire:key="node-{{ $result->id }}"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-medium">{{ $result->name }}</span>
                                <span class="text-xs text-zinc-400 dark:text-zinc-500">Location</span>
                            </div>
                            <div class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">
                                Path: {{ $result->path }}
                            </div>
                        </div>
                    @else
                        <div
                            class="border-b border-zinc-100 px-4 py-2.5 text-sm text-zinc-600 last:border-0 dark:border-zinc-800 dark:text-zinc-400"
                            wire:key="unknown-{{ $loop->index }}"
                        >
                            Unsupported result type: {{ class_basename($result) }}
                            @if (isset($result->name))
                                ({{ $result->name }})
                            @endif
                        </div>
                    @endif
                @endforeach

                {{-- Optional: Handle case where results haven't loaded yet (e.g., during debounce delay) --}}
            @else
                <div class="px-4 py-3 text-sm text-zinc-400">Loading...</div>
            @endif
        </div>
    @endif
</div>
