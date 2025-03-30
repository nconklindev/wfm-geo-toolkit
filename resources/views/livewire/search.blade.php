<div class="relative">
    <flux:input
        type="text"
        wire:model.live.debounce="searchQuery"
        wire:keydown.enter.prevent
        icon="magnifying-glass"
        size="sm"
        class="text-sm"
        placeholder="Search by name or description"
        clearable
    />
    <!-- Search Results Container -->
    @if (! empty($searchQuery))
        <div
            class="absolute top-full right-0 left-0 z-[500] mt-2 max-h-96 w-full overflow-y-auto rounded-lg bg-zinc-900 shadow-lg"
        >
            @if (count($results) === 0)
                <div class="px-4 py-3 text-sm">No places found matching "{{ $searchQuery }}"</div>
            @else
                @foreach ($results as $place)
                    <div
                        class="cursor-pointer border-b px-4 py-2 text-xs last:border-0 hover:bg-zinc-100 dark:hover:bg-zinc-800/50"
                    >
                        <div class="font-medium">{{ $place['name'] }}</div>
                        @if (! empty($place['description']))
                            <div class="text-xs text-zinc-600">{{ $place['description'] }}</div>
                        @endif

                        <div class="text-xs text-zinc-500">
                            Lat: {{ $place['latitude'] }}, Long: {{ $place['longitude'] }}
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @endif
</div>
