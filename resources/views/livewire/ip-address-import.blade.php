<div class="min-h-screen py-8 ">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">IP Address Import</h1>
            <p class="mt-2 text-zinc-600 dark:text-zinc-400">Upload and analyze IP address ranges in JSON format</p>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <x-alert type="success" :message="session('success')" />
        @endif

        @if (session()->has('error'))
            <x-alert type="error" :message="session('error')" />
        @endif

        @if (session()->has('info'))
            <x-alert type="info" :message="session('info')" />
        @endif

        <!-- Upload Section -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-lg border border-zinc-200 dark:border-zinc-700 p-6 mb-8">
            @if (!$uploadedFile)
                <!-- File Upload Form -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-zinc-800 dark:text-zinc-200 mb-4">Upload JSON File</h2>

                    <div class="border-2 border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg p-8 text-center hover:border-sky-400 dark:hover:border-sky-500 transition-colors duration-200">
                        <div class="space-y-4">
                            <flux:icon.document-arrow-up class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-500" />

                            <div>
                                <label for="file" class="cursor-pointer">
                                    <span class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-sky-600 hover:bg-sky-700 dark:bg-sky-500 dark:hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 dark:focus:ring-offset-zinc-800 transition-colors duration-200">
                                        <flux:icon.arrow-up-tray class="-ml-1 mr-2 h-5 w-5" />
                                        Choose JSON File
                                    </span>
                                    <input id="file" wire:model="file" type="file" accept=".json" class="sr-only">
                                </label>
                            </div>

                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Select a JSON file containing IP address data (max 10MB)
                            </p>

                            @error('file')
                                <div class="mt-2 p-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-sm text-red-600 dark:text-red-400">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Expected Format Info -->
                    <div class="mt-6 p-4 bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-800 rounded-lg">
                        <h3 class="text-sm font-medium text-sky-800 dark:text-sky-200 mb-2">Expected JSON Format</h3>
                        <div class="bg-sky-300/20 dark:bg-zinc-950 rounded-md p-3 overflow-x-auto">
                            <pre class="text-xs text-zinc-800 dark:text-zinc-200"><code>[
  {
    "id": 2122235,
    "name": "UKG Corporate Office",
    "description": "UKG Corporate",
    "protocolVersion": { "id": 1, "name": "IPv4" },
    "startingIPRange": "198.42.56.25",
    "endingIPRange": "198.42.56.56"
  }
]</code></pre>
                        </div>
                    </div>

                    <!-- Upload Button -->
                    @if ($file)
                        <div class="mt-6 text-center">
                            <flux:button wire:click="uploadFile" variant="primary" class="text-lg px-4 py-2">
                                <span wire:loading.remove wire:target="uploadFile">Upload File</span>
                                <span wire:loading wire:target="uploadFile">Uploading...</span>
                            </flux:button>
                        </div>
                    @endif
                </div>
            @else
                <!-- Uploaded File Info -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-zinc-800 dark:text-zinc-200 mb-4">Uploaded File</h2>

                    <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <flux:icon.document-check class="h-8 w-8 text-green-500 dark:text-green-400 mr-3" />
                                <div>
                                    <div class="font-medium text-green-800 dark:text-green-200">{{ $uploadedFile['name'] }}</div>
                                    <div class="text-sm text-green-600 dark:text-green-300">
                                        {{ $this->formatBytes($uploadedFile['size']) }}
                                    </div>
                                </div>
                            </div>
                            <button
                                wire:click="removeFile"
                                class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 p-1 rounded-md hover:bg-green-100 dark:hover:bg-green-800/30 transition-colors duration-200"
                            >
                                <flux:icon.trash class="h-6 w-6" />
                            </button>
                        </div>
                    </div>

                    <!-- Analysis Button -->
                    <div class="mt-6 text-center">
                        <flux:button wire:click="analyzeFile" icon="chart-bar" variant="primary" :loading="false">
                            <span wire:loading.remove wire:target="analyzeFile">Analyze IP Addresses</span>
                            <span wire:loading wire:target="analyzeFile">Analyzing...</span>
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Additional Info Section -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-lg border border-zinc-200 dark:border-zinc-700 p-6">
            <h3 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-4">What happens during analysis?</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex items-start">
                    <flux:icon.shield-check class="h-6 w-6 text-sky-500 dark:text-sky-400 mr-3 mt-1 flex-shrink-0" />
                    <div>
                        <h4 class="font-medium text-zinc-800 dark:text-zinc-200">IP Validation</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                            Validates IP address formats and checks for invalid ranges
                        </p>
                    </div>
                </div>
                <div class="flex items-start">
                    <flux:icon.exclamation-triangle class="h-6 w-6 text-amber-500 dark:text-amber-400 mr-3 mt-1 flex-shrink-0" />
                    <div>
                        <h4 class="font-medium text-zinc-800 dark:text-zinc-200">Issue Detection</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                            Identifies overlapping ranges, large ranges, and other potential issues
                        </p>
                    </div>
                </div>
                <div class="flex items-start">
                    <flux:icon.document-magnifying-glass class="h-6 w-6 text-green-500 dark:text-green-400 mr-3 mt-1 flex-shrink-0" />
                    <div>
                        <h4 class="font-medium text-zinc-800 dark:text-zinc-200">Detailed Results</h4>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                            Provides detailed breakdown of all IP ranges and detected issues
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
