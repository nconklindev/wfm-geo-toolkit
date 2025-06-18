<div>
    <div class="overflow-hidden rounded-lg bg-white dark:bg-zinc-800">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-8">
                <flux:heading size="xl" level="1">Export Known Places</flux:heading>
                <flux:text class="mt-1.5 text-sm">
                    Export your Known Places data in various formats. Select from the options below to customize your
                    export.
                </flux:text>
                <flux:callout color="blue" class="mt-6" icon="information-circle">
                    <flux:callout.text>
                        <strong>Note:</strong>
                        Please also see the API
                        <flux:callout.link
                            target="_blank"
                            href="https://developer.ukg.com/wfm/reference/retrieve-all-known-places-or-by-specification"
                            class="inline cursor-pointer"
                        >
                            documentation
                        </flux:callout.link>
                        for how to use the public API to export the same information.
                    </flux:callout.text>
                </flux:callout>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Instructions Panel -->
                <div class="lg:col-span-1">
                    <div class="rounded-lg bg-zinc-50 p-5 dark:bg-zinc-700">
                        <h3 class="mb-4 text-lg font-medium text-zinc-900 dark:text-zinc-200">How it works</h3>
                        <ul class="space-y-3 text-sm">
                            <li class="flex items-start">
                                <div
                                    class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-teal-600 text-xs font-medium text-zinc-900 dark:bg-teal-700 dark:text-white"
                                >
                                    1
                                </div>
                                <p class="ml-3 text-zinc-600 dark:text-zinc-300">
                                    Select your export format and options
                                </p>
                            </li>
                            <li class="flex items-start">
                                <div
                                    class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-teal-600 text-xs font-medium text-zinc-900 dark:bg-teal-700 dark:text-white"
                                >
                                    2
                                </div>
                                <p class="ml-3 text-zinc-600 dark:text-zinc-300">Click export and download your file</p>
                            </li>
                        </ul>
                    </div>
                    <flux:callout color="blue" class="mt-6" icon="information-circle">
                        <flux:callout.heading class="ml-1.5">CSV Format</flux:callout.heading>
                        <flux:callout.text class="ml-1.5">
                            Additional options available for CSV format
                        </flux:callout.text>
                    </flux:callout>
                </div>

                <!-- Export Form -->
                <div class="lg:col-span-2">
                    <form wire:submit="export">
                        <div class="flex w-full flex-col space-y-6 md:max-w-1/2">
                            <!-- Export Format -->
                            <flux:field>
                                <flux:select
                                    name="file_format"
                                    wire:model="fileFormat"
                                    id="file_format"
                                    label="Export Format"
                                >
                                    <flux:select.option value="json" selected>JSON</flux:select.option>
                                    <flux:select.option value="csv">CSV</flux:select.option>
                                </flux:select>
                            </flux:field>

                            <!-- Place Selection -->
                            <flux:field>
                                <flux:select
                                    name="places_filter"
                                    wire:model="placesFilter"
                                    id="places_filter"
                                    label="Places to Include"
                                >
                                    <flux:select.option value="all" selected>All Known Places</flux:select.option>
                                    <flux:select.option value="recent">
                                        Recently Created (Last 30 Days)
                                    </flux:select.option>
                                    <flux:select.option value="custom">Custom Selection</flux:select.option>
                                </flux:select>
                            </flux:field>

                            <!-- Custom Selection (conditionally shown based on places_filter) -->
                            <div
                                class="mt-4 w-full"
                                id="custom_selection"
                                wire:show="placesFilter === 'custom'"
                                wire:cloak
                            >
                                <flux:label>Select Specific Places</flux:label>
                                <div
                                    class="mt-2 max-h-60 space-y-2 overflow-y-auto rounded-md border border-zinc-300 p-2 dark:border-zinc-600"
                                >
                                    @foreach ($user->knownPlaces as $place)
                                        <div class="flex items-center gap-1">
                                            <flux:checkbox
                                                wire:model="selectedPlaces"
                                                :value="$place->id"
                                                :label="$place->name"
                                            />
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Include Timestamps -->
                            <flux:radio.group
                                wire:model="includeTimestamps"
                                id="include_timestamps"
                                label="Include Timestamps"
                            >
                                <flux:radio wire:model="includeTimestamps" value="1" label="Yes" />
                                <flux:radio wire:model="includeTimestamps" value="0" label="No" />
                            </flux:radio.group>

                            <!-- Transform Data -->
                            <flux:field wire:show="fileFormat === 'csv'" wire:cloak>
                                <flux:radio.group
                                    wire:model="transformData"
                                    id="transform_data"
                                    label="Transform Data"
                                    description="Transform the data to match what is expected by the Pro WFM Data Import Tool."
                                >
                                    <flux:radio wire:model="transformData" value="yes" label="Yes" />
                                    <flux:radio wire:model="transformData" value="no" label="No" />
                                </flux:radio.group>
                            </flux:field>
                        </div>

                        <!-- Export Button -->
                        <div class="mt-8">
                            <flux:button type="submit" variant="primary">Export and Download</flux:button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
