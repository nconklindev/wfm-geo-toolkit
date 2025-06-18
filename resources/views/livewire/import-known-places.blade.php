<div>
    <div class="overflow-hidden rounded-lg bg-white dark:bg-zinc-800">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-8">
                <flux:heading size="xl" level="1">Import Known Places</flux:heading>
                <flux:text class="mt-1.5 text-sm">Import Known Places from a JSON file.</flux:text>
                <flux:callout color="blue" class="mt-6" icon="information-circle">
                    <flux:callout.text>
                        This tool will only work with exported JSON data from the Pro WFM API. See the API
                        <flux:callout.link
                            target="_blank"
                            href="https://developer.ukg.com/wfm/reference/retrieve-all-known-places-or-by-specification"
                            class="cursor-pointer"
                        >
                            documentation
                        </flux:callout.link>
                        for more details
                    </flux:callout.text>
                </flux:callout>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Instructions Panel -->
                <div class="lg:col-span-1">
                    <div class="rounded-lg bg-zinc-50 p-5 dark:bg-zinc-700">
                        <h3 class="mb-4 text-lg font-medium text-zinc-900 dark:text-white">How it works</h3>
                        <ul class="space-y-3 text-sm">
                            <li class="flex items-start">
                                <div
                                    class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-teal-600 text-xs font-medium text-zinc-900 dark:bg-teal-700 dark:text-white"
                                >
                                    1
                                </div>
                                <flux:text class="ml-3">Upload your JSON file with known places data</flux:text>
                            </li>
                            <li class="flex items-start">
                                <div
                                    class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-teal-600 text-xs font-medium text-zinc-900 dark:bg-teal-700 dark:text-white"
                                >
                                    2
                                </div>
                                <flux:text class="ml-3">Customize import options if needed</flux:text>
                            </li>
                            <li class="flex items-start">
                                <div
                                    class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-teal-600 text-xs font-medium text-zinc-900 dark:bg-teal-700 dark:text-white"
                                >
                                    3
                                </div>
                                <flux:text class="ml-3">Click the "Import" button when done</flux:text>
                            </li>
                        </ul>
                        <!-- Expected format -->
                        <div>
                            <flux:callout color="blue" class="mt-6" icon="information-circle">
                                <flux:callout.heading>Expected format</flux:callout.heading>
                                <flux:callout.text>
                                    Your JSON file should include name, coordinates, radius, and other place details.
                                </flux:callout.text>
                                <flux:modal.trigger name="expected-format-modal">
                                    <flux:callout.link variant="ghost" class="cursor-pointer text-xs">
                                        View sample format
                                    </flux:callout.link>
                                </flux:modal.trigger>
                            </flux:callout>

                            <!-- Modal for format sample -->
                            <x-expected-format-modal name="expected-format-modal" />
                        </div>
                    </div>
                </div>

                <!-- Upload Panel -->
                <div class="lg:col-span-2">
                    <form wire:submit="import" enctype="multipart/form-data" id="importForm">
                        @csrf
                        <div
                            class="space-y-6"
                            x-data="{ uploading: false, progress: 0 }"
                            x-on:livewire-upload-start="uploading = true"
                            x-on:livewire-upload-finish="uploading = false"
                            x-on:livewire-upload-cancel="uploading = false"
                            x-on:livewire-upload-error="uploading = false"
                            x-on:livewire-upload-progress="progress = $event.detail.progress"
                        >
                            <!-- File Upload Area -->
                            <div
                                class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-zinc-300 px-6 py-10 dark:border-zinc-600"
                                id="dropzone"
                            >
                                <flux:field class="space-y-3 text-center">
                                    <flux:icon.file-json class="mx-auto h-16 w-16 text-zinc-400" />

                                    <flux:input.file
                                        id="json_file"
                                        name="file"
                                        wire:model="file"
                                        label="Upload a JSON file"
                                        required
                                        accept="application/json"
                                        class="flex-1"
                                        size="sm"
                                    />
                                    <flux:text variant="subtle" size="sm">JSON up to 10MB</flux:text>
                                </flux:field>
                            </div>

                            <!-- Import Options -->
                            <div class="rounded-lg bg-zinc-50 p-5 dark:bg-zinc-700">
                                <h3 class="mb-4 text-lg font-medium text-zinc-900 dark:text-white">Import Options</h3>

                                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                    <flux:field>
                                        <flux:select
                                            id="duplicate_handling"
                                            name="duplicate_handling"
                                            wire:model="duplicateHandling"
                                            label="Duplicate Handling"
                                        >
                                            <flux:select.option value="skip">Skip duplicates</flux:select.option>
                                            <flux:select.option value="replace">Replace duplicates</flux:select.option>
                                            <flux:select.option value="merge">
                                                Merge with existing data
                                            </flux:select.option>
                                        </flux:select>
                                    </flux:field>
                                    <flux:field>
                                        <flux:select
                                            id="match_by"
                                            name="match_by"
                                            wire:model="matchBy"
                                            label="Match Duplicates By"
                                        >
                                            <flux:select.option value="name">Name</flux:select.option>
                                            <flux:select.option value="coordinates">Coordinates</flux:select.option>
                                            <flux:select.option value="both">
                                                Both name and coordinates
                                            </flux:select.option>
                                        </flux:select>
                                    </flux:field>
                                    <flux:field>
                                        <flux:radio.group name="include_inactive" label="Include Inactive">
                                            <flux:radio wire:model="includeInactive" value="yes" label="Yes" />
                                            <flux:radio wire:model="includeInactive" value="no" label="No" />
                                        </flux:radio.group>
                                    </flux:field>
                                </div>
                            </div>

                            <!-- Submission Controls -->
                            <div class="flex justify-end space-x-3">
                                <flux:button as="link" href="{{ route('known-places.create') }}">Cancel</flux:button>
                                <flux:button
                                    type="submit"
                                    id="importButton"
                                    variant="primary"
                                    icon="cloud-arrow-up"
                                    class="cursor-pointer"
                                >
                                    Import
                                </flux:button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
