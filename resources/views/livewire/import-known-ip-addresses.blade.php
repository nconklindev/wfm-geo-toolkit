<div>
    <div class="overflow-hidden rounded-lg bg-white dark:bg-zinc-800">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-8">
                <flux:heading size="xl" level="1">Import Known IP Addresses</flux:heading>
                <flux:text class="mt-1.5 text-sm">Import Known IP Addresses from a JSON file.</flux:text>
                <flux:callout color="blue" class="mt-6" icon="information-circle">
                    <flux:callout.text>
                        Upload a JSON file containing IP address ranges with their names and descriptions. Each entry
                        should include start IP, end IP, name, and optional description. You can use the Pro WFM API to
                        obtain the Known IP Address data.
                        <flux:callout.link class="cursor-pointer">Learn more</flux:callout.link>
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
                                    class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-teal-300 text-xs font-medium text-zinc-900 dark:bg-teal-700 dark:text-white"
                                >
                                    1
                                </div>
                                <flux:text class="ml-3">Upload your JSON file with IP address data</flux:text>
                            </li>
                            <li class="flex items-start">
                                <div
                                    class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-teal-300 text-xs font-medium text-zinc-900 dark:bg-teal-700 dark:text-white"
                                >
                                    2
                                </div>
                                <flux:text class="ml-3">Customize import options if needed</flux:text>
                            </li>
                            <li class="flex items-start">
                                <div
                                    class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-teal-300 text-xs font-medium text-zinc-900 dark:bg-teal-700 dark:text-white"
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
                                    Your JSON file should include start IP, end IP, name, and optional description for
                                    each IP range.
                                </flux:callout.text>
                                <flux:modal.trigger name="ip-format-modal">
                                    <flux:callout.link variant="ghost" class="cursor-pointer text-xs">
                                        View sample format
                                    </flux:callout.link>
                                </flux:modal.trigger>
                            </flux:callout>

                            <!-- Modal for format sample -->
                            <x-ip-format-modal />
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
                                    <flux:icon.server class="mx-auto h-16 w-16 text-zinc-400" />

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

                                <!-- Upload Progress -->
                                <div x-show="uploading" class="mt-4 w-full">
                                    <div class="h-2 rounded-full bg-zinc-200 dark:bg-zinc-700">
                                        <div
                                            class="h-2 rounded-full bg-blue-600 transition-all duration-300"
                                            :style="`width: ${progress}%`"
                                        ></div>
                                    </div>
                                    <flux:text
                                        size="sm"
                                        variant="subtle"
                                        class="mt-1"
                                        x-text="`Uploading... ${progress}%`"
                                    ></flux:text>
                                </div>
                            </div>

                            <!-- Import Options -->
                            <div class="rounded-lg bg-zinc-50 p-5 dark:bg-zinc-700">
                                <h3 class="mb-4 text-lg font-medium text-zinc-900 dark:text-white">Import Options</h3>

                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <flux:field>
                                        <flux:select
                                            id="duplicate_handling"
                                            name="duplicate_handling"
                                            wire:model="duplicateHandling"
                                            label="Duplicate Handling"
                                        >
                                            <flux:select.option value="skip">Skip duplicates</flux:select.option>
                                            <flux:select.option value="replace">Replace duplicates</flux:select.option>
                                            <flux:select.option value="update">Update existing</flux:select.option>
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
                                            <flux:select.option value="ip_range">
                                                IP Range (Start & End)
                                            </flux:select.option>
                                            <flux:select.option value="both">Both name and IP range</flux:select.option>
                                        </flux:select>
                                    </flux:field>
                                </div>

                                <div class="mt-4">
                                    <flux:checkbox wire:model="validateIpRanges" label="Validate IP address formats" />
                                    <flux:text variant="subtle" size="sm" class="mt-1">
                                        Check this to validate that all IP addresses are properly formatted before
                                        importing
                                    </flux:text>
                                </div>
                            </div>

                            <!-- Preview Section (if file is uploaded) -->
                            @if ($file)
                                <div class="rounded-lg bg-zinc-50 p-5 dark:bg-zinc-700">
                                    <h3 class="mb-4 text-lg font-medium text-zinc-900 dark:text-white">
                                        Import Preview
                                    </h3>
                                    <flux:text variant="subtle" size="sm">
                                        File: {{ $file->getClientOriginalName() }}
                                        ({{ number_format($file->getSize() / 1024, 2) }} KB)
                                    </flux:text>

                                    @if ($previewData)
                                        <div class="mt-4">
                                            <flux:text size="sm" class="font-medium">
                                                Found {{ count($previewData) }} IP address entries
                                            </flux:text>
                                            <div
                                                class="mt-2 max-h-32 overflow-y-auto rounded border bg-white p-2 dark:bg-zinc-800"
                                            >
                                                @foreach (array_slice($previewData, 0, 3) as $entry)
                                                    <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                                        {{ $entry['name'] ?? 'Unnamed' }}
                                                        ({{ $entry['startingIPRange'] ?? 'N/A' }} -
                                                        {{ $entry['endingIPRange'] ?? 'N/A' }})
                                                    </div>
                                                @endforeach

                                                @if (count($previewData) > 3)
                                                    <div class="text-xs text-zinc-500">
                                                        ... and {{ count($previewData) - 3 }} more
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Submission Controls -->
                            <div class="flex justify-end space-x-3">
                                <flux:button as="link" href="{{ route('known-ip-addresses.index') }}">
                                    Cancel
                                </flux:button>
                                <flux:button
                                    type="submit"
                                    id="importButton"
                                    variant="primary"
                                    icon="cloud-arrow-up"
                                    class="cursor-pointer"
                                    @disabled="uploading || ! file"
                                >
                                    <span x-show="!uploading">Import</span>
                                    <span x-show="uploading">Processing...</span>
                                </flux:button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
