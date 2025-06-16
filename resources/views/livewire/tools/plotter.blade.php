<div>
    <flux:heading level="1" size="xl" class="mb-1 font-semibold">Plotting Tool</flux:heading>
    <flux:text class="mb-4">
        Use the plotting tool to plot Known Places from WFM alongside employee punches. Helpful for troubleshooting
        geofencing issues within Pro WFM.
    </flux:text>

    {{-- Input Form and Map Container --}}
    <div class="grid min-h-[70vh] grid-cols-1 gap-4 rounded-lg bg-white p-4 shadow-sm md:grid-cols-2 dark:bg-zinc-800">
        {{-- Input Form --}}
        <div>
            {{-- Your form fields remain unchanged --}}
            <flux:heading level="2" size="lg" class="mb-3 font-medium">Add New Item</flux:heading>
            <form wire:submit="addPoint" class="flex flex-grow flex-col gap-6">
                <!-- Latitude -->
                <div class="mt-auto grid grid-cols-2 items-start gap-4">
                    <flux:field>
                        <flux:label badge="Required">Latitude</flux:label>
                        <flux:input
                            inputmode="numeric"
                            step="0.0000000001"
                            id="latitude"
                            wire:model="latitude"
                            placeholder="40.7128"
                            required
                            autofocus
                        />
                        <flux:error name="latitude" />
                    </flux:field>

                    <!-- Longitude -->
                    <flux:field>
                        <flux:label badge="Required" class="block text-sm font-medium">Longitude</flux:label>
                        <flux:input
                            inputmode="numeric"
                            step="any"
                            id="longitude"
                            wire:model="longitude"
                            placeholder="-74.0060"
                            required
                        />
                        <flux:error
                            name=" longitude
                        "
                        />
                    </flux:field>
                </div>

                <div class="mt-auto grid grid-cols-2 items-start gap-4">
                    <!-- Radius -->
                    <flux:field>
                        <flux:label badge="Required">Radius</flux:label>
                        <flux:input
                            type="number"
                            step="any"
                            id="radius"
                            wire:model="radius"
                            placeholder="50"
                            required
                        />
                        <flux:error name="radius" />
                    </flux:field>

                    <!-- Accuracy -->
                    <flux:field>
                        <flux:label badge="Required" class="block text-sm font-medium">Accuracy</flux:label>
                        <flux:input
                            type="number"
                            step="any"
                            id="accuracy"
                            wire:model="accuracy"
                            placeholder="100"
                            required
                        />
                        <flux:error name="accuracy" />
                    </flux:field>
                </div>

                <!-- Label -->
                <flux:field>
                    <flux:label badge="Optional">Label</flux:label>
                    <flux:input type="text" id="label" wire:model="label" placeholder="Office, John's Punch" />
                    <flux:error name="label" />
                </flux:field>

                <!-- Color -->
                <flux:field wire:ignore>
                    <flux:label badge="Optional">Color</flux:label>
                    <flux:input type="text" id="color" wire:model="color" data-color />
                    <flux:error name="color" />
                </flux:field>

                <div class="flex items-center justify-end pt-2 md:col-span-3">
                    <flux:button type="submit" variant="primary" class="cursor-pointer">Add to Plot</flux:button>
                </div>
            </form>
        </div>
        {{-- Map Container --}}
        <div class="relative min-h-[50vh] md:h-full">
            <!-- Map container -->
            <div
                id="map"
                {{-- Use a unique ID for this page so that we aren't fighting with the global map.js script --}}
                data-map-type="plotter"
                data-map-points="{{ json_encode($mapPoints) }}"
                class="h-full w-full rounded-md"
                wire:ignore
            ></div>

            <!-- Address search overlay -->
            <livewire:address-search />
        </div>
    </div>

    <!-- Points Table -->
    <div class="mt-6">
        <flux:heading level="2" size="lg" class="mb-3 font-medium">Plotted Points</flux:heading>
        @if (count($points) > 0)
            <div class="overflow-hidden rounded-md border border-zinc-400 dark:border-zinc-700">
                <table class="min-w-full divide-y divide-zinc-400 dark:divide-zinc-700">
                    <thead class="bg-white dark:bg-zinc-800">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                            >
                                Label
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                            >
                                Location
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                            >
                                Radius
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                            >
                                Accuracy
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                            >
                                Color
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-400 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                        @foreach ($points as $index => $point)
                            <tr
                                class="cursor-pointer transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                wire:click="flyTo({{ $index }})"
                            >
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-zinc-800 dark:text-white">
                                    {{ $point->label ?: 'Unnamed Point' }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-zinc-600 dark:text-zinc-300">
                                    {{ number_format($point->latitude, 10) }},
                                    {{ number_format($point->longitude, 10) }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-zinc-600 dark:text-zinc-300">
                                    {{ $point->radius }}m
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-zinc-600 dark:text-zinc-300">
                                    {{ $point->accuracy }}m
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="mr-2 h-5 w-5 rounded-full"
                                            style="background-color: {{ $point->color }}"
                                        ></div>
                                        <span class="text-zinc-600 dark:text-zinc-300">{{ $point->color }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                    <flux:button
                                        type="button"
                                        icon="trash"
                                        size="sm"
                                        variant="danger"
                                        wire:click.stop="removePoint({{ $index }})"
                                        class="cursor-pointer focus:outline-none"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div
                class="rounded-md border border-zinc-400 bg-white p-6 text-center dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-zinc-400">No points have been added yet. Use the form above to add points to the map.</p>
            </div>
        @endif
    </div>

    <style>
        /* Making sure that the address search field is the proper color regardless of the appearance settings */
        .dark #address_search {
            background-color: color-mix(in oklab, var(--color-zinc-800) 90%, var(--color-white)) !important;
        }
    </style>
    @push('scripts')
        @vite(['resources/js/plotter.js'])
    @endpush
</div>
