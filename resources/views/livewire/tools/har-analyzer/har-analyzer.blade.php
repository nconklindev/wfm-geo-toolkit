<div class="mx-auto max-w-7xl p-6">
    <!-- Header -->
    <div class="mb-8 text-center">
        <div
            class="mb-1.5 inline-flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-r from-blue-500 to-purple-600"
        >
            <flux:icon.cloud-arrow-up class="size-10 text-white" />
        </div>

        <h1 class="mb-2 text-3xl font-bold text-zinc-900 dark:text-white">HAR File Analyzer</h1>
        <p class="text-zinc-600 dark:text-zinc-400">
            Upload and analyze HTTP Archive (.har) files to inspect network traffic
        </p>
    </div>

    <!-- Flash Messages -->
    <x-alert status="success" :message="session('success')" />
    <x-alert status="error" :message="session('error')" />
    <x-alert status="warning" :message="session('warning')" />
    <x-alert status="info" :message="session('info')" />

    <!-- Upload Area -->
    @if (! $uploadedFile)
        <div
            class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-800"
        >
            <div class="p-8">
                <form wire:submit.prevent="uploadFile">
                    <!-- Drag & Drop Area -->
                    <div class="relative">
                        <input
                            type="file"
                            wire:model="harFile"
                            accept=".har"
                            class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                            id="har-file-input"
                        />

                        <div
                            class="rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50 p-12 text-center transition-colors duration-200 hover:border-blue-400 dark:border-zinc-600 dark:bg-zinc-900/50 dark:hover:border-blue-500"
                        >
                            <div class="space-y-4">
                                <!-- Upload Icon -->
                                <div class="flex justify-center">
                                    <div
                                        class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-r from-blue-500 to-purple-600"
                                    >
                                        <flux:icon.cloud-arrow-up class="size-12 text-white" />
                                    </div>
                                </div>

                                <!-- Upload Text -->
                                <div>
                                    <flux:heading
                                        level="3"
                                        size="lg"
                                        class="mb-2 font-semibold text-zinc-900 dark:text-white"
                                    >
                                        Drop your HAR file here
                                    </flux:heading>
                                    <flux:text class="mb-4">or click to browse from your computer</flux:text>
                                    <flux:text size="sm" variant="subtle">Supports .har files up to 10MB</flux:text>
                                </div>

                                <!-- Selected File Info -->
                                @if ($harFile)
                                    <div
                                        class="mt-6 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20"
                                    >
                                        <div class="flex items-center justify-center space-x-3">
                                            <flux:icon.document class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                                            <div class="text-left">
                                                <p class="font-medium text-blue-900 dark:text-blue-100">
                                                    {{ $harFile->getClientOriginalName() }}
                                                </p>
                                                <p class="text-sm text-blue-600 dark:text-blue-300">
                                                    {{ number_format($harFile->getSize() / 1024, 2) }} KB
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Upload Button -->
                    @if ($harFile)
                        <div class="mt-6 flex justify-center">
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="inline-flex transform items-center rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-3 font-semibold text-white shadow-lg transition-all duration-200 hover:-translate-y-0.5 hover:from-blue-700 hover:to-purple-700 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <flux:icon.loading class="mr-2 size-4" wire:loading wire:target="uploadFile" />
                                <flux:icon.cloud-arrow-up
                                    class="mr-2 -ml-1 h-5 w-5 text-white"
                                    wire:loading.remove
                                    wire:target="uploadFile"
                                />

                                <span wire:loading.remove wire:target="uploadFile">Upload File</span>
                                <span wire:loading wire:target="uploadFile">Uploading...</span>
                            </button>
                        </div>
                    @endif

                    <!-- Validation Errors -->
                    @error('harFile')
                        <div
                            class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-900/20"
                        >
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        </div>
                    @enderror
                </form>
            </div>
        </div>
    @elseif ($uploadedFile)
        <!-- Uploaded File Display -->
        <div
            class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-800"
        >
            <div class="p-8">
                <div class="mb-6 text-center">
                    <div
                        class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30"
                    >
                        <flux:icon.check-circle class="size-10 text-green-600 dark:text-green-400" />
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-zinc-900 dark:text-white">File Uploaded Successfully</h3>
                </div>

                <!-- File Info Card -->
                <div class="mb-6 rounded-xl bg-zinc-50 p-6 dark:bg-zinc-900/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30"
                            >
                                <flux:icon.document class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h4 class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $uploadedFile['name'] }}
                                </h4>
                                <div class="flex items-center space-x-4 text-sm text-zinc-500 dark:text-zinc-400">
                                    <span>{{ number_format($uploadedFile['size'] / 1024, 2) }} KB</span>
                                    <span>•</span>
                                    <span>{{ $uploadedFile['uploaded_at']->format('M j, Y \a\t g:i A') }}</span>
                                </div>
                            </div>
                        </div>

                        <button
                            wire:click="removeFile"
                            class="inline-flex items-center rounded-lg bg-red-100 px-4 py-2 text-red-700 transition-colors duration-200 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                        >
                            <flux:icon.trash class="mr-2 h-4 w-4" />
                            Remove
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col justify-center gap-4 sm:flex-row">
                    <button
                        wire:click="analyzeFile"
                        wire:loading.attr="disabled"
                        class="inline-flex transform items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-3 font-semibold text-white shadow-lg transition-all duration-200 hover:-translate-y-0.5 hover:from-blue-700 hover:to-purple-700 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <flux:icon.loading class="mr-2 size-4" wire:loading wire:target="analyzeFile" />
                        <flux:icon.chart-bar class="mr-2 h-5 w-5" wire:loading.remove wire:target="analyzeFile" />

                        <span wire:loading.remove wire:target="analyzeFile">Analyze HAR File</span>
                        <span wire:loading wire:target="analyzeFile">Analyzing...</span>
                    </button>

                    <button
                        wire:click="removeFile"
                        class="inline-flex items-center justify-center rounded-xl bg-zinc-100 px-6 py-3 font-semibold text-zinc-700 transition-colors duration-200 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-600"
                    >
                        <flux:icon.arrow-up class="mr-2 h-5 w-5" />
                        Upload New File
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Info Callout -->
    <div class="mt-8 rounded-xl border border-blue-200 bg-blue-50 p-6 dark:border-blue-800 dark:bg-blue-900/20">
        <div class="flex items-start space-x-3">
            <flux:icon.information-circle variant="outline" class="size-6 text-blue-600 dark:text-blue-400" />
            <div>
                <h4 class="mb-2 font-semibold text-blue-900 dark:text-blue-100">About HAR Files</h4>
                <p class="text-sm leading-relaxed text-blue-800 dark:text-blue-200">
                    HAR (HTTP Archive) files contain detailed information about network requests made by web pages. They
                    include request/response headers, timing data, and content, making them perfect for analyzing web
                    performance and debugging network issues.
                </p>
            </div>
        </div>
    </div>
</div>
