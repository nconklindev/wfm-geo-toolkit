<div>
    <flux:field>
        <flux:label badge="Optional">Locations</flux:label>
        <flux:description>
            For a Known Place, enter the location(s) assigned to that Known Place. For a Punch, enter the employee's
            primary location.
        </flux:description>

        <flux:input.group>
            <flux:input
                wire:model="currentLocation"
                type="text"
                placeholder="Acme Inc/North Carolina/Plant 01"
                wire:keydown.enter="addLocation"
            />
            <flux:button
                icon="plus"
                variant="primary"
                class="cursor-pointer"
                as="button"
                type="button"
                wire:click="addLocation"
            >
                Add
            </flux:button>
        </flux:input.group>
        <flux:error name="currentLocation" />
    </flux:field>

    @if (count($locations) > 0)
        <div class="mt-4 space-y-2">
            @foreach ($locations as $index => $location)
                <div
                    class="flex items-center justify-between rounded-md border border-zinc-300 bg-zinc-50 px-3 py-2 transition-opacity duration-200 dark:border-zinc-600 dark:bg-zinc-800"
                    wire:key="location-{{ $index }}"
                >
                    <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $location }}</span>
                    <div class="relative">
                        <!-- Loading spinner overlay -->
                        <div
                            wire:loading.flex
                            wire:target="removeLocation({{ $index }})"
                            class="absolute inset-0 items-center justify-center rounded bg-zinc-50/80 dark:bg-zinc-800/80"
                        >
                            <svg class="h-4 w-4 animate-spin text-zinc-500" fill="none" viewBox="0 0 24 24">
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                        </div>

                        <!-- Button (hidden during loading) -->
                        <flux:button
                            icon="trash"
                            size="sm"
                            variant="danger"
                            wire:click="removeLocation({{ $index }})"
                            wire:loading.class="opacity-0"
                            wire:target="removeLocation({{ $index }})"
                            class="cursor-pointer transition-opacity duration-200"
                        />
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
