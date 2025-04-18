<div>
    <flux:field>
        <flux:label badge="Optional">Locations</flux:label>

        <flux:description>
            Enter the locations that are assigned to this place. Enter one full path at a time. Click the button to add
            it to the table below.
        </flux:description>
        <flux:input.group>
            <flux:input
                wire:model="currentLocation"
                badge="Optional"
                type="text"
                placeholder="Acme Inc/Acme Manufacturing/Charlotte/Plant 01"
                tabindex="0"
            />
            <flux:button
                icon="plus"
                variant="primary"
                class="cursor-pointer"
                as="button"
                wire:click="addLocationToList"
            >
                Add
            </flux:button>
        </flux:input.group>
        <flux:error name="currentLocation" />
    </flux:field>

    <div class="mt-4 overflow-hidden rounded-lg border border-gray-700 shadow-sm">
        <div class="max-h-64 overflow-y-auto">
            @if ($types)
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="sticky top-0 bg-gray-50 dark:bg-gray-800">
                        <tr>
                            @foreach ($types as $type)
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-200"
                                    wire:key="tag-{{ $type->id }}"
                                >
                                    <div class="flex items-center space-x-2">
                                        <span
                                            class="inline-block h-3 w-3 rounded-full"
                                            wire:ignore.self
                                            style="background-color: {{ $type->color ?? '#cbd5e1' }}"
                                            wire:key="tag-color-{{ $type->id }}"
                                        ></span>
                                        <span>{{ $type->name }}</span>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        <!-- Example row - replace with actual data -->
                        @foreach ($savedLocations as $i => $locationSet)
                            <tr>
                                @foreach ($locationSet as $j => $location)
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-200">
                                        {{ $location }}
                                        {{--
                                            I don't like this but I didn't want to create an entire Livewire Form and
                                            Component just to handle one property (savedLocations)
                                        --}}
                                        <input
                                            type="hidden"
                                            name="savedLocations[{{ $i }}][{{ $j }}]"
                                            value="{{ $location }}"
                                        />
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
