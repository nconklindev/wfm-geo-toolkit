@php use Carbon\Carbon; @endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">IP Address Analysis Results</h1>
                <p class="mt-2 text-zinc-600 dark:text-zinc-400">
                    Analysis complete for {{ $uploadedFile['name'] ?? 'uploaded file' }}
                </p>
            </div>
            <div class="flex">
                <flux:button wire:click="uploadNewFile" variant="primary" icon="arrow-up-tray">
                    Upload New File
                </flux:button>
            </div>
        </div>
    </div>

    <!-- File Info -->
    <x-ui.card class="mb-6" variant="info">
        <x-slot:header>
            <div class="flex items-center gap-2">
                <flux:icon.document-text class="w-5 h-5" />
                <span class="font-semibold">File Information</span>
            </div>
        </x-slot:header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Filename</dt>
                <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $uploadedFile['name'] ?? 'Unknown' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">File Size</dt>
                <dd class="mt-1 text-sm text-zinc-900 dark:text-white">{{ $this->formatBytes($uploadedFile['size'] ?? 0) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Uploaded</dt>
                <dd class="mt-1 text-sm text-zinc-900 dark:text-white">
                    {{ isset($uploadedFile['uploaded_at']) ? Carbon::parse($uploadedFile['uploaded_at'])->diffForHumans() : 'Unknown' }}
                </dd>
            </div>
        </div>
    </x-ui.card>

    <!-- Custom Tabs -->
    <div class="mb-6">
        <div class="border-b border-zinc-200 dark:border-zinc-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button
                    wire:click="setTab('overview')"
                    type="button"
                    class="flex items-center gap-2 py-2 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors duration-200
                        {{ $currentTab === 'overview'
                            ? 'border-sky-500 text-sky-600 dark:text-sky-400'
                            : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300 dark:text-zinc-400 dark:hover:text-zinc-300 dark:hover:border-zinc-600' }}"
                >
                    <flux:icon.chart-bar class="w-4 h-4" />
                    Overview
                </button>
                <button
                    wire:click="setTab('issues')"
                    type="button"
                    class="flex items-center gap-2 py-2 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors duration-200
                        {{ $currentTab === 'issues'
                            ? 'border-sky-500 text-sky-600 dark:text-sky-400'
                            : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300 dark:text-zinc-400 dark:hover:text-zinc-300 dark:hover:border-zinc-600' }}"
                >
                    <flux:icon.exclamation-triangle class="w-4 h-4" />
                    Issues
                </button>
                <button
                    wire:click="setTab('ranges')"
                    type="button"
                    class="flex items-center gap-2 py-2 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors duration-200
                        {{ $currentTab === 'ranges'
                            ? 'border-sky-500 text-sky-600 dark:text-sky-400'
                            : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300 dark:text-zinc-400 dark:hover:text-zinc-300 dark:hover:border-zinc-600' }}"
                >
                    <flux:icon.list-bullet class="w-4 h-4" />
                    All Ranges
                </button>
                <button
                    wire:click="setTab('raw')"
                    type="button"
                    class="flex items-center gap-2 py-2 px-1 border-b-2 font-medium text-sm whitespace-nowrap transition-colors duration-200
                        {{ $currentTab === 'raw'
                            ? 'border-sky-500 text-sky-600 dark:text-sky-400'
                            : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300 dark:text-zinc-400 dark:hover:text-zinc-300 dark:hover:border-zinc-600' }}"
                >
                    <flux:icon.code-bracket class="w-4 h-4" />
                    Raw Data
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="space-y-6">
        @if($currentTab === 'overview')
            <!-- Summary Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <x-ui.card>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <flux:icon.queue-list class="w-8 h-8 text-sky-500 dark:text-sky-400" />
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Ranges</dt>
                            <dd class="text-2xl font-bold text-zinc-900 dark:text-white">
                                {{ $this->formatNumber($analysisData['summary']['total_ranges'] ?? 0) }}
                            </dd>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <flux:icon.check-circle class="w-8 h-8 text-green-500 dark:text-green-400" />
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Valid Ranges</dt>
                            <dd class="text-2xl font-bold text-zinc-900 dark:text-white">
                                {{ $this->formatNumber($analysisData['summary']['valid_ranges'] ?? 0) }}
                            </dd>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <flux:icon.exclamation-triangle class="w-8 h-8 text-amber-500 dark:text-amber-400" />
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Issues Found</dt>
                            <dd class="text-2xl font-bold text-zinc-900 dark:text-white">
                                {{ $this->formatNumber($analysisData['summary']['total_issues'] ?? 0) }}
                            </dd>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <flux:icon.globe-alt class="w-8 h-8 text-purple-500 dark:text-purple-400" />
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total IPs</dt>
                            <dd class="text-2xl font-bold text-zinc-900 dark:text-white">
                                {{ $this->formatNumber($analysisData['summary']['total_ip_addresses'] ?? 0) }}
                            </dd>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <!-- Issue Summary -->
            @if(!empty($analysisData['summary']['issue_breakdown']))
                <x-ui.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <flux:icon.chart-pie class="w-5 h-5 text-zinc-700 dark:text-zinc-300" />
                            <span class="font-semibold text-zinc-900 dark:text-white">Issue Breakdown</span>
                        </div>
                    </x-slot:header>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($analysisData['summary']['issue_breakdown'] as $issueType => $count)
                            <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-lg">
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ ucwords(str_replace('_', ' ', $issueType)) }}
                                </span>
                                <span class="text-sm font-bold text-zinc-900 dark:text-white">
                                    {{ $count }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>
            @endif

        @elseif($currentTab === 'issues')
            <!-- Issues Tab -->
            @if(isset($analysisData['validation_results']))
                @php
                    $rangesWithIssues = array_filter($analysisData['validation_results'], fn($result) => !empty($result['issues']));
                @endphp

                @if(empty($rangesWithIssues))
                    <x-ui.card variant="success">
                        <div class="text-center">
                            <flux:icon.check-circle class="w-12 h-12 text-green-500 dark:text-green-400 mx-auto mb-4" />
                            <h3 class="text-lg font-medium text-green-900 dark:text-green-100">No Issues Found</h3>
                            <p class="text-green-700 dark:text-green-300">All IP address ranges appear to be valid!</p>
                        </div>
                    </x-ui.card>
                @else
                    <div class="space-y-4">
                        @foreach($rangesWithIssues as $result)
                            <x-ui.card>
                                <div class="flex items-start gap-4">
                                    <flux:icon.exclamation-triangle class="w-6 h-6 text-amber-500 dark:text-amber-400 flex-shrink-0 mt-1" />
                                    <div class="flex-grow">
                                        <h4 class="font-semibold text-zinc-900 dark:text-white">
                                            {{ $result['ip_range']['name'] ?? 'Unnamed Range' }}
                                        </h4>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-3">
                                            {{ $result['ip_range']['start'] }} - {{ $result['ip_range']['end'] }}
                                        </p>
                                        <div class="space-y-3">
                                            @foreach($result['issues'] as $issue)
                                                @php
                                                    $severity = is_array($issue) ? ($issue['severity'] ?? 'info') : 'info';
                                                    $bgClasses = match($severity) {
                                                        'critical' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
                                                        'warning' => 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800',
                                                        default => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800'
                                                    };
                                                    $textClasses = match($severity) {
                                                        'critical' => 'text-red-700 dark:text-red-300',
                                                        'warning' => 'text-amber-700 dark:text-amber-300',
                                                        default => 'text-blue-700 dark:text-blue-300'
                                                    };
                                                @endphp

                                                @if(is_array($issue))
                                                    <div class="p-3 {{ $bgClasses }} rounded-lg border">
                                                        <div class="flex items-center gap-2 mb-2">
                                                            @if($severity === 'critical')
                                                                <flux:icon.x-circle class="w-4 h-4 text-red-500 dark:text-red-400" />
                                                                <flux:badge color="red" size="sm">Critical</flux:badge>
                                                            @elseif($severity === 'warning')
                                                                <flux:icon.exclamation-triangle class="w-4 h-4 text-amber-500 dark:text-amber-400" />
                                                                <flux:badge color="amber" size="sm">Warning</flux:badge>
                                                            @else
                                                                <flux:icon.information-circle class="w-4 h-4 text-blue-500 dark:text-blue-400" />
                                                                <flux:badge color="blue" size="sm">Info</flux:badge>
                                                            @endif
                                                            @if(isset($issue['type']))
                                                                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400 uppercase tracking-wider">
                                                                    {{ str_replace('_', ' ', $issue['type']) }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        @if(isset($issue['message']))
                                                            <p class="text-sm {{ $textClasses }} mb-2">
                                                                {{ $issue['message'] }}
                                                            </p>
                                                        @endif
                                                        @if(!empty($issue['overlapping_ranges']))
                                                            <div class="mt-2">
                                                                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400 uppercase tracking-wider">Overlapping with:</span>
                                                                <div class="mt-1 space-y-1">
                                                                    @foreach($issue['overlapping_ranges'] as $overlap)
                                                                        <div class="text-sm text-zinc-600 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 rounded px-2 py-1">
                                                                            <span class="font-medium">{{ $overlap['name'] ?? 'Range #' . ($overlap['index'] + 1) }}</span>
                                                                            <span class="font-mono text-xs ml-2">{{ $overlap['start'] }} - {{ $overlap['end'] }}</span>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="p-3 {{ $bgClasses }} rounded-lg border">
                                                        <p class="text-sm {{ $textClasses }}">
                                                            {{ is_string($issue) ? $issue : (string) $issue }}
                                                        </p>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </x-ui.card>
                        @endforeach
                    </div>
                @endif
            @endif

        @elseif($currentTab === 'ranges')
            <!-- All Ranges Tab -->
            @if(isset($analysisData['ip_ranges']))
                <x-ui.card>
                    <x-slot:header>
                        <div class="flex items-center gap-2">
                            <flux:icon.list-bullet class="w-5 h-5 text-zinc-700 dark:text-zinc-300" />
                            <span class="font-semibold text-zinc-900 dark:text-white">All IP Address Ranges ({{ count($analysisData['ip_ranges']) }})</span>
                        </div>
                    </x-slot:header>

                    <div class="overflow-x-auto rounded-md">
                        <table class="min-w-full divide-y  divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-950">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Range
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Protocol
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($analysisData['ip_ranges'] as $range)
                                @php
                                    $validation = collect($analysisData['validation_results'] ?? [])
                                        ->firstWhere('ip_range.index', $range['index']);
                                    $hasIssues = !empty($validation['issues'] ?? []);
                                @endphp
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                            {{ $range['name'] ?? 'Unnamed' }}
                                        </div>
                                        @if($range['description'])
                                            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $range['description'] }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-zinc-900 dark:text-white">
                                        {{ $range['start'] }} - {{ $range['end'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">
                                        {{ $range['protocol_version'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($hasIssues)
                                            <flux:badge color="amber" size="sm">
                                                <flux:icon.exclamation-triangle class="w-3 h-3 mr-1" />
                                                Issues ({{ count($validation['issues']) }})
                                            </flux:badge>
                                        @else
                                            <flux:badge color="green" size="sm">
                                                <flux:icon.check class="w-3 h-3" />
                                                Valid
                                            </flux:badge>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            @endif

        @elseif($currentTab === 'raw')
            <!-- Raw Data Tab -->
            <x-ui.card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <flux:icon.code-bracket class="w-5 h-5 text-zinc-700 dark:text-zinc-300" />
                        <span class="font-semibold text-zinc-900 dark:text-white">Raw Analysis Data</span>
                    </div>
                </x-slot:header>

                <div class="bg-zinc-900 dark:bg-zinc-950 rounded-lg p-4 overflow-x-auto">
                    <pre class="text-sm text-zinc-100 dark:text-zinc-200 whitespace-pre-wrap"><code>{{ json_encode($analysisData, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            </x-ui.card>
        @endif
    </div>
</div>
