@php
    // Prepare data for Requests by Type chart
    $requestsByType = collect($analysisData['requests_by_type']);
    $requestsByTypeLabels = $requestsByType->pluck('type')->map(fn ($type) => Str::ucfirst($type));
    $requestsByTypeCounts = $requestsByType->pluck('count');

    // Prepare data for Status Codes chart
    $statusCodes = collect($analysisData['status_codes']);
    $statusCodesLabels = $statusCodes->keys();
    $statusCodesCounts = $statusCodes->values();
    $statusCodesColors = $statusCodesLabels->map(function ($code) {
        if ($code >= 500) {
            return '#ef4444';
        } // red-500
        if ($code >= 400) {
            return '#f97316';
        } // orange-500
        if ($code >= 300) {
            return '#eab308';
        } // yellow-500
        return '#22c55e'; // green-500
    });
@endphp

<div class="space-y-8">
    <!-- File Info Header -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center space-x-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                    <flux:icon.check-circle class="h-6 w-6 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">
                        {{ $uploadedFile['name'] }}
                    </h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        Analysis completed • {{ $this->formatBytes($uploadedFile['size']) }}
                    </p>
                </div>
            </div>
            <div class="flex space-x-2">
                <button
                    wire:click="startNewAnalysis"
                    class="inline-flex items-center rounded-lg bg-blue-100 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50"
                >
                    <flux:icon.arrow-path class="mr-1 h-4 w-4" />
                    New Analysis
                </button>
                <button
                    wire:click="startNewAnalysis"
                    class="inline-flex items-center rounded-lg bg-zinc-100 px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-600"
                >
                    <flux:icon.trash class="mr-1 h-4 w-4" />
                    Remove
                </button>
            </div>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                    <flux:icon.globe-alt class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Requests</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        {{ number_format($analysisData['overview']['total_requests']) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                    <flux:icon.arrow-down class="h-6 w-6 text-green-600 dark:text-green-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Size</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        {{ $this->formatBytes($analysisData['overview']['total_size']) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                    <flux:icon.clock class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Load Time</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        {{ $this->formatTime($analysisData['overview']['load_time']) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
                    <flux:icon.exclamation-triangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Failed Requests</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        {{ number_format($analysisData['overview']['failed_requests']) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-b border-zinc-200 dark:border-zinc-700">
        <nav class="-mb-px flex space-x-8 overflow-x-scroll">
            <button
                wire:click="setTab('overview')"
                class="{{ $currentTab === 'overview' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' }} border-b-2 px-1 py-2 text-sm font-medium"
            >
                Overview
            </button>
            <button
                wire:click="setTab('performance')"
                class="{{ $currentTab === 'performance' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' }} border-b-2 px-1 py-2 text-sm font-medium"
            >
                Performance
            </button>
            <button
                wire:click="setTab('requests')"
                class="{{ $currentTab === 'requests' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' }} border-b-2 px-1 py-2 text-sm font-medium"
            >
                Requests
            </button>
            <button
                wire:click="setTab('security')"
                class="{{ $currentTab === 'security' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' }} border-b-2 px-1 py-2 text-sm font-medium"
            >
                Security
            </button>
            <button
                wire:click="setTab('domains')"
                class="{{ $currentTab === 'domains' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' }} border-b-2 px-1 py-2 text-sm font-medium"
            >
                Domains
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="mt-8">
        @if ($currentTab === 'overview')
            <livewire:tools.har-analyzer.tabs.overview :analysis-data="$analysisData" />
        @elseif ($currentTab === 'performance')
            <livewire:tools.har-analyzer.tabs.performance :analysis-data="$analysisData" />
        @elseif ($currentTab === 'requests')
            <livewire:tools.har-analyzer.tabs.requests :analysis-data="$analysisData" />
        @elseif ($currentTab === 'security')
            <livewire:tools.har-analyzer.tabs.security :analysis-data="$analysisData" />
        @elseif ($currentTab === 'domains')
            <livewire:tools.har-analyzer.tabs.domains :analysis-data="$analysisData" />
        @endif
    </div>
</div>
