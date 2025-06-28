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
            <flux:heading level="2" size="lg" class="mb-3 font-medium">Add New Item</flux:heading>
            <form wire:submit="addPoint" class="flex flex-grow flex-col gap-6">
                <!-- Type -->
                <flux:field>
                    <flux:label badge="Required">Type</flux:label>
                    <flux:select wire:model="type" id="type" tabindex="0">
                        <option value="known_place">Known Place</option>
                        <option value="punch">Punch</option>
                    </flux:select>
                </flux:field>

                <!-- Label -->
                <flux:field>
                    <flux:label badge="Optional">Label</flux:label>
                    <flux:input type="text" id="label" wire:model="label" placeholder="Office, John's Punch" />
                    <flux:error name="label" />
                </flux:field>

                <!-- Coordinates -->
                <div class="grid grid-cols-2 items-start gap-4">
                    <flux:field>
                        <flux:label badge="Required">Latitude</flux:label>
                        <flux:input
                            inputmode="numeric"
                            step="0.0000000001"
                            id="latitude"
                            wire:model="latitude"
                            placeholder="42.6146421515"
                            required
                            autofocus
                        />
                        <flux:error name="latitude" />
                    </flux:field>

                    <flux:field>
                        <flux:label badge="Required">Longitude</flux:label>
                        <flux:input
                            inputmode="numeric"
                            step="any"
                            id="longitude"
                            wire:model="longitude"
                            placeholder="-71.324701513"
                            required
                        />
                        <flux:error name="longitude" />
                    </flux:field>
                </div>

                <!-- Measurements -->
                <div class="grid grid-cols-2 items-start gap-4">
                    <flux:field>
                        <flux:label badge="Required">Radius</flux:label>
                        <flux:input.group
                            x-data="{
                                increment() {
                                    let current = parseInt($wire.radius) || 0
                                    $wire.radius = Math.min(current + 5, 1000)
                                    // Manually trigger the input event after Livewire updates
                                    $nextTick(() => {
                                        document
                                            .getElementById('radius')
                                            .dispatchEvent(new Event('input', { bubbles: true }))
                                    })
                                },
                                decrement() {
                                    let current = parseInt($wire.radius) || 0
                                    $wire.radius = Math.max(current - 5, 1)
                                    $nextTick(() => {
                                        document
                                            .getElementById('radius')
                                            .dispatchEvent(new Event('input', { bubbles: true }))
                                    })
                                },
                                captureKeyboardEvent(e) {
                                    // Only trigger shortcuts when the radius input has focus OR when no text input has focus
                                    const activeElement = document.activeElement
                                    const isTextInput =
                                        activeElement &&
                                        (activeElement.type === 'text' ||
                                            activeElement.type === 'search' ||
                                            activeElement.tagName === 'TEXTAREA')

                                    // If a text input (like label field) has focus, don't intercept
                                    if (isTextInput && activeElement.id !== 'radius') {
                                        return
                                    }

                                    // Prevent default behavior for our handled keys
                                    if (e.key === '+' || e.key === '=' || e.key === '-') {
                                        e.preventDefault()
                                    }

                                    switch (e.key) {
                                        case '+':
                                        case '=': // + key without shift
                                            this.increment()
                                            break
                                        case '-':
                                            this.decrement()
                                            break
                                    }
                                },
                            }"
                            @keydown.window="captureKeyboardEvent($event)"
                        >
                            <flux:input
                                type="number"
                                step="any"
                                id="radius"
                                wire:model="radius"
                                min="1"
                                max="1000"
                                placeholder="75"
                                required
                            />
                            <flux:button
                                icon="plus"
                                as="button"
                                class="cursor-pointer"
                                @click="increment()"
                                tabindex="-1"
                            />
                            <flux:button
                                icon="minus"
                                as="button"
                                class="cursor-pointer"
                                @click="decrement()"
                                tabindex="-1"
                            />
                        </flux:input.group>
                        <flux:error name="radius" />
                    </flux:field>

                    <flux:field>
                        <flux:label badge="Required">Accuracy</flux:label>

                        <flux:input
                            type="number"
                            step="any"
                            id="accuracy"
                            wire:model="accuracy"
                            placeholder="50"
                            required
                        />
                        <flux:error name="accuracy" />
                        <flux:description class="text-xs">
                            This is the "GPS Accuracy Threshold" when "Type" is a Known Place and the "Accuracy" when
                            the "Type" is a Punch.
                        </flux:description>
                    </flux:field>
                </div>

                <livewire:tools.plotter-location-input />

                <!-- Color -->
                <flux:field wire:ignore>
                    <flux:label badge="Optional">Color</flux:label>
                    <flux:input type="text" id="color" wire:model="color" data-color />
                    <flux:error name="color" />
                </flux:field>

                <div class="flex items-center justify-end pt-2">
                    <flux:button type="submit" variant="primary" class="cursor-pointer">Add to Plot</flux:button>
                </div>
            </form>
        </div>
        {{-- Map --}}
        <div class="relative min-h-[50vh] md:h-full">
            <!-- Map -->
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
            <div class="overflow-x-auto rounded-md border border-zinc-400 dark:border-zinc-700">
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
                                Type
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                            >
                                Coordinates
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
                                Locations
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                            >
                                Color
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                            >
                                Status
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
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-zinc-800 dark:text-white">
                                    {{ Str::title(str_replace('_', ' ', $point->type)) }}
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
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-zinc-600 dark:text-zinc-300">
                                    @if (! empty($point->locations))
                                        <div
                                            x-data="{ expanded: false }"
                                            class="flex flex-col items-start space-y-1"
                                        >
                                            @php
                                                $maxVisible = 3; // Show first 3 locations
                                                $visibleLocations = array_slice($point->locations, 0, $maxVisible);
                                                $hiddenLocations = array_slice($point->locations, $maxVisible);
                                                $hasMore = count($point->locations) > $maxVisible;
                                            @endphp

                                            {{-- Always visible locations --}}
                                            @foreach ($visibleLocations as $location)
                                                <div
                                                    class="max-w-xs truncate rounded bg-zinc-100 px-2 py-1 text-xs dark:bg-zinc-700"
                                                    title="{{ $location }}"
                                                >
                                                    {{ $location }}
                                                </div>
                                            @endforeach

                                            {{-- Hidden locations (shown when expanded) --}}
                                            @if ($hasMore)
                                                <div
                                                    x-show="expanded"
                                                    x-transition:enter="transition duration-200 ease-out"
                                                    x-transition:enter-start="scale-95 transform opacity-0"
                                                    x-transition:enter-end="scale-100 transform opacity-100"
                                                    x-transition:leave="transition duration-150 ease-in"
                                                    x-transition:leave-start="scale-100 transform opacity-100"
                                                    x-transition:leave-end="scale-95 transform opacity-0"
                                                    class="space-y-1"
                                                >
                                                    @foreach ($hiddenLocations as $location)
                                                        <div
                                                            class="max-w-xs truncate rounded bg-zinc-100 px-2 py-1 text-xs dark:bg-zinc-700"
                                                            title="{{ $location }}"
                                                        >
                                                            {{ $location }}
                                                        </div>
                                                    @endforeach
                                                </div>

                                                {{-- Toggle button --}}
                                                <button
                                                    @click="expanded = !expanded"
                                                    class="text-xs text-blue-600 underline hover:text-blue-800 focus:outline-none dark:text-blue-400 dark:hover:text-blue-300"
                                                    @click.stop
                                                >
                                                    <span x-show="!expanded">
                                                        Show {{ count($hiddenLocations) }} more
                                                    </span>
                                                    <span x-show="expanded">Show less</span>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-zinc-400">No locations</span>
                                    @endif
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
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($point->type === 'punch' && ! empty($this->getPunchIssues($index)))
                                        @php
                                            $issues = $this->getPunchIssues($index);
                                            $groupedIssues = collect($issues)->groupBy('severity');
                                        @endphp

                                        <div class="flex flex-col flex-wrap items-start">
                                            @foreach (['critical', 'warning', 'info'] as $severity)
                                                @if ($groupedIssues->has($severity))
                                                    @php
                                                        $severityIssues = $groupedIssues[$severity];
                                                        $issueCount = count($severityIssues);
                                                        $severityConfig = $this->getSeverityConfig($severity);
                                                    @endphp

                                                    <button
                                                        class="{{ $severityConfig['hover_bg'] }} {{ $severityConfig['hover_decoration'] }} flex cursor-pointer items-center rounded-md px-2 py-1 transition-all duration-200 hover:underline hover:decoration-from-font hover:underline-offset-2"
                                                        wire:click.stop="selectPointForIssues({{ $index }}, '{{ $severity }}')"
                                                        title="{{ $issueCount }} {{ $severity === 'info' ? 'informational' : $severity }} {{ Str::plural('item', $issueCount) }} found"
                                                    >
                                                        <x-issue-severity-icon
                                                            :severity="$severity"
                                                            :config="$severityConfig"
                                                        />
                                                        <span
                                                            class="{{ $severityConfig['text-color'] }} cursor-pointer text-xs"
                                                        >
                                                            {{ $issueCount }}
                                                        </span>
                                                    </button>
                                                @endif
                                            @endforeach
                                        </div>
                                    @elseif ($point->type === 'punch')
                                        <div class="flex items-center">
                                            <flux:icon.check-circle class="mr-1 h-4 w-4 text-green-500" />
                                            <span class="text-xs text-green-600 dark:text-green-400">Valid</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-zinc-400">—</span>
                                    @endif
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

        {{-- Issues Detail Section --}}
        @if ($selectedPointForIssues !== null && $selectedSeverity !== null && ! empty($this->getPunchIssuesBySeverity($selectedPointForIssues, $selectedSeverity)))
            @php
                $selectedPoint = $points[$selectedPointForIssues];
                $selectedIssues = $this->getPunchIssuesBySeverity($selectedPointForIssues, $selectedSeverity);
                $selectedIssueCount = count($selectedIssues);
                $severityConfig = $this->getSeverityConfig($selectedSeverity);
            @endphp

            <div class="mt-4 rounded-lg border p-4">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="flex items-center font-semibold">
                            @if ($severityConfig['icon'] === 'x-circle')
                                <flux:icon.x-circle class="{{ $severityConfig['text-color'] }} mr-2 h-5 w-5" />
                            @elseif ($severityConfig['icon'] === 'exclamation-triangle')
                                <flux:icon.exclamation-triangle
                                    class="{{ $severityConfig['text-color'] }} mr-2 h-5 w-5"
                                />
                            @elseif ($severityConfig['icon'] === 'exclamation-circle')
                                <flux:icon.exclamation-circle
                                    class="{{ $severityConfig['text-color'] }} mr-2 h-5 w-5"
                                />
                            @else
                                <flux:icon.information-circle
                                    class="{{ $severityConfig['text-color'] }} mr-2 h-5 w-5"
                                />
                            @endif
                            <span class="{{ $severityConfig['text-color'] }}">
                                {{ ucfirst($selectedSeverity) }}: {{ $selectedIssueCount }}
                                {{ Str::plural('issue', $selectedIssueCount) }} for
                                "{{ $selectedPoint->label ?: 'Unnamed Point' }}"
                            </span>
                        </h3>
                        <p class="mt-1 text-sm">
                            Location: {{ number_format($selectedPoint->latitude, 6) }},
                            {{ number_format($selectedPoint->longitude, 6) }}
                        </p>
                    </div>
                    <button
                        class="{{ $severityConfig['text-color'] }} ml-4 hover:opacity-80"
                        wire:click="clearSelectedPoint"
                        title="Dismiss"
                    >
                        <flux:icon.x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach ($selectedIssues as $issue)
                        <div
                            class="{{ $severityConfig['border-color'] }} {{ $severityConfig['bg-color'] }} rounded border p-3"
                        >
                            <h5 class="{{ $severityConfig['text-color'] }} font-medium">
                                {{ $issue['message'] }}
                            </h5>

                            <x-issue-recommendations :type="$issue['type']" :severity-config="$severityConfig" />
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex justify-end">
                    <flux:button
                        variant="primary"
                        size="sm"
                        class="cursor-pointer"
                        @class([
                            'bg-amber-50 text-zinc-600 hover:bg-amber-200 hover:text-zinc-800 dark:bg-amber-600 dark:text-zinc-200 dark:hover:bg-amber-700 dark:hover:text-zinc-100' =>
                                $issue['severity'] === 'warning',
                            'bg-red-50 text-zinc-600 hover:text-zinc-800 dark:bg-red-600 dark:text-zinc-200 dark:hover:bg-red-700 dark:hover:text-zinc-100' =>
                                $issue['severity'] === 'critical',
                            'bg-blue-50 text-blue-600 hover:text-zinc-800 dark:bg-blue-600 dark:text-zinc-200 dark:hover:bg-blue-700 dark:hover:text-zinc-100' =>
                                $issue['severity'] === 'info',
                            'cursor-pointer',
                        ])
                        wire:click="clearSelectedPoint"
                    >
                        Got it
                    </flux:button>
                </div>
            </div>
        @endif

        @if (count($points) > 0)
            <div class="mt-4 flex justify-center">
                <flux:button
                    variant="danger"
                    size="sm"
                    class="cursor-pointer"
                    wire:click="clearAllPoints"
                    wire:confirm="Are you sure you want to clear all points? This action cannot be undone."
                >
                    Clear All Points
                </flux:button>
            </div>
        @endif
    </div>
</div>

@push('scripts')
    @vite(['resources/js/plotter.js'])
@endpush
