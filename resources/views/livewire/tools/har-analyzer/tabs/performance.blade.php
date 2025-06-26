<!-- Performance Tab -->
<div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
    <!-- Performance Metrics -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Average Timings</h3>
            <flux:tooltip content="Network timing breakdown for all requests">
                <flux:icon.information-circle class="h-4 w-4 text-zinc-400" />
            </flux:tooltip>
        </div>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">DNS Resolution</span>
                <span class="font-semibold text-zinc-900 dark:text-white">
                    {{ $this->formatTime($analysisData['performance']['avg_dns_time']) }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Connection</span>
                <span class="font-semibold text-zinc-900 dark:text-white">
                    {{ $this->formatTime($analysisData['performance']['avg_connect_time']) }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">SSL Handshake</span>
                <span class="font-semibold text-zinc-900 dark:text-white">
                    {{ $this->formatTime($analysisData['performance']['avg_ssl_time']) }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Wait Time</span>
                <span class="font-semibold text-zinc-900 dark:text-white">
                    {{ $this->formatTime($analysisData['performance']['avg_wait_time']) }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Send Time</span>
                <span class="font-semibold text-zinc-900 dark:text-white">
                    {{ $this->formatTime($analysisData['performance']['avg_send_time']) }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Receive Time</span>
                <span class="font-semibold text-zinc-900 dark:text-white">
                    {{ $this->formatTime($analysisData['performance']['avg_receive_time']) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Largest Resources -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Largest Resources</h3>
            <flux:tooltip content="0ms = cached; Unknown size = no Content-Length header">
                <flux:icon.information-circle class="h-4 w-4 text-zinc-400" />
            </flux:tooltip>
        </div>
        <div class="space-y-3">
            @foreach (array_slice($analysisData['largest_resources'], 0, 5) as $resource)
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p
                            class="truncate text-sm font-medium text-zinc-900 dark:text-white"
                            title="{{ $resource['url'] }}"
                        >
                            {{ basename(parse_url($resource['url'], PHP_URL_PATH)) ?: parse_url($resource['url'], PHP_URL_HOST) }}
                        </p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $resource['mime_type'] }}
                        </p>
                    </div>
                    <div class="ml-4 text-right">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                            @if ($resource['size'] === -1)
                                <span class="text-zinc-400">Unknown</span>
                            @else
                                {{ $this->formatBytes($resource['size']) }}
                            @endif
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            @if ($resource['time'] == 0)
                                <span class="text-green-600 dark:text-green-400">0ms (cached)</span>
                            @else
                                {{ $this->formatTime($resource['time']) }}
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- API Performance Section -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">API Response Times</h3>
            <flux:tooltip content="Average response times by API type">
                <flux:icon.information-circle class="h-4 w-4 text-zinc-400" />
            </flux:tooltip>
        </div>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Authentication APIs</span>
                <span class="font-semibold text-zinc-900 dark:text-white">
                    @if (($analysisData['performance']['auth_api_avg_time'] ?? 0) == 0)
                        <span class="text-zinc-400">No requests</span>
                    @else
                        {{ $this->formatTime($analysisData['performance']['auth_api_avg_time']) }}
                    @endif
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Data APIs</span>
                <span class="font-semibold text-zinc-900 dark:text-white">
                    @if (($analysisData['performance']['data_api_avg_time'] ?? 0) == 0)
                        <span class="text-zinc-400">No requests</span>
                    @else
                        {{ $this->formatTime($analysisData['performance']['data_api_avg_time']) }}
                    @endif
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Upload APIs</span>
                <span class="font-semibold text-zinc-900 dark:text-white">
                    @if (($analysisData['performance']['upload_api_avg_time'] ?? 0) == 0)
                        <span class="text-zinc-400">No requests</span>
                    @else
                        {{ $this->formatTime($analysisData['performance']['upload_api_avg_time']) }}
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Resource Loading Performance -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Resource Performance</h3>
            <flux:tooltip content="Average load times by resource type">
                <flux:icon.information-circle class="h-4 w-4 text-zinc-400" />
            </flux:tooltip>
        </div>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">CSS Load Time</span>
                <span class="font-semibold text-zinc-900 dark:text-white">
                    @if (($analysisData['performance']['css_load_time'] ?? 0) == 0)
                        <span class="text-zinc-400">No CSS files</span>
                    @else
                        {{ $this->formatTime($analysisData['performance']['css_load_time']) }}
                    @endif
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">JavaScript Load Time</span>
                <span class="font-semibold text-zinc-900 dark:text-white">
                    @if (($analysisData['performance']['js_load_time'] ?? 0) == 0)
                        <span class="text-zinc-400">No JS files</span>
                    @else
                        {{ $this->formatTime($analysisData['performance']['js_load_time']) }}
                    @endif
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Image Load Time</span>
                <span class="font-semibold text-zinc-900 dark:text-white">
                    @if (($analysisData['performance']['image_load_time'] ?? 0) == 0)
                        <span class="text-zinc-400">No images</span>
                    @else
                        {{ $this->formatTime($analysisData['performance']['image_load_time']) }}
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Performance Insights -->
    <div
        class="col-span-full rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
    >
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Performance Insights</h3>
            <flux:tooltip content="Insights based on your HAR data">
                <flux:icon.information-circle class="h-4 w-4 text-zinc-400" />
            </flux:tooltip>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @if (($analysisData['performance']['avg_wait_time'] ?? 0) > 1000)
                <div class="flex items-start space-x-3 rounded-lg bg-amber-50 p-4 dark:bg-amber-900/20">
                    <div class="mt-0.5 h-5 w-5 flex-shrink-0 rounded-full bg-amber-500"></div>
                    <div>
                        <h4 class="font-medium text-amber-800 dark:text-amber-200">High Server Response Time</h4>
                        <p class="text-sm text-amber-700 dark:text-amber-300">
                            Average wait time is
                            {{ $this->formatTime($analysisData['performance']['avg_wait_time']) }}. This suggests
                            potential backend performance issues or database bottlenecks in WFM.
                        </p>
                    </div>
                </div>
            @endif

            @if (($analysisData['performance']['compression_ratio'] ?? 100) < 70)
                <div class="flex items-start space-x-3 rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                    <div class="mt-0.5 h-5 w-5 flex-shrink-0 rounded-full bg-blue-500"></div>
                    <div>
                        <h4 class="font-medium text-blue-800 dark:text-blue-200">Low Compression Detected</h4>
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            Only {{ $analysisData['performance']['compression_ratio'] }}% compression ratio detected.
                            This indicates most responses aren't compressed.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Cache Performance -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Cache Performance</h3>
            <flux:tooltip content="Percentage of resources with cache headers">
                <flux:icon.information-circle class="h-4 w-4 text-zinc-400" />
            </flux:tooltip>
        </div>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Cache Hit Rate</span>
                <span class="font-semibold text-zinc-900 dark:text-white">
                    {{ number_format($analysisData['performance']['cache_hit_rate'] ?? 0, 1) }}%
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Cached Resources</span>
                <span class="font-semibold text-zinc-900 dark:text-white">
                    {{ $analysisData['performance']['cached_resources'] ?? 0 }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-600 dark:text-zinc-400">Non-Cached Resources</span>
                <span class="font-semibold text-zinc-900 dark:text-white">
                    {{ $analysisData['performance']['non_cached_resources'] ?? 0 }}
                </span>
            </div>
        </div>
    </div>
</div>
