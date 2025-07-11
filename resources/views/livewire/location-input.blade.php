<div>
    {{-- Input field for adding a new location path --}}
    <flux:field>
        <flux:label badge="Optional">Locations</flux:label>

        <flux:description>
            {{ $description }}
        </flux:description>
        <flux:input.group>
            <flux:input
                wire:model.defer="currentLocation"
                type="text"
                placeholder="Acme Inc/North Carolina/Plant 01"
                tabindex="0"
                wire:keydown.enter="addLocationToList"
            />
            <flux:button
                icon="plus"
                variant="primary"
                class="cursor-pointer"
                as="button"
                type="button"
                wire:click="addLocationToList"
                :loading="false"
            >
                Add
            </flux:button>
        </flux:input.group>
        <flux:error name="currentLocation" />
        <flux:error name="savedLocations" />
    </flux:field>

    {{-- Table displaying the added location paths --}}
    <div class="mt-4 overflow-hidden rounded-lg border border-zinc-700 shadow-sm">
        <div class="max-h-64 overflow-y-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="sticky top-0 bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-200"
                        >
                            Location Path
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Remove</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                    {{-- $locations is an array of arrays --}}
                    @forelse ($locations as $index => $locationPathArray)
                        <tr wire:key="location-row-{{ $index }}">
                            {{-- Display the location path string by joining the array elements --}}
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-zinc-900 dark:text-zinc-100">
                                {{ implode('/', $locationPathArray) }}
                                @foreach ($locationPathArray as $segmentIndex => $segment)
                                    <input type="hidden" name="locations[{{ $index }}][]" value="{{ $segment }}" />
                                @endforeach
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                <button
                                    type="button"
                                    wire:click="removeLocation({{ $index }})"
                                    class="text-red-600 hover:text-red-800 dark:text-red-500 dark:hover:text-red-400"
                                    title="Remove location"
                                    wire:loading.attr="disabled"
                                    wire:target="removeLocation({{ $index }})"
                                >
                                    Remove
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No locations added yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-1">
        <flux:error name="savedLocations.*" />
    </div>
</div>
