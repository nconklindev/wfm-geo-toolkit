<div class="space-y-8">
    <!-- Failed Requests -->
    @if (! empty($analysisData['failed_requests']))
        <div class="rounded-xl border border-red-200 bg-red-50 p-6 shadow-sm dark:border-red-800 dark:bg-red-900/20">
            <div class="mb-4 flex items-center">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
                    <flux:icon.exclamation-triangle class="h-5 w-5 text-red-600 dark:text-red-400" />
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-semibold text-red-900 dark:text-red-100">Failed Requests</h3>
                    <p class="text-sm text-red-700 dark:text-red-300">
                        {{ count($analysisData['failed_requests']) }}
                        request{{ count($analysisData['failed_requests']) === 1 ? '' : 's' }} failed
                    </p>
                </div>
            </div>
            <div class="space-y-3">
                @foreach (array_slice($analysisData['failed_requests'], 0, 10) as $request)
                    <div
                        class="flex items-center justify-between rounded-lg border border-red-200 bg-white p-3 dark:border-red-700 dark:bg-red-900/10"
                    >
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center space-x-2">
                                <span
                                    @class([
                                        'inline-flex items-center rounded px-2 py-1 text-xs font-medium',
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' => ($request['status'] ?? 0) >= 500,
                                        'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300' =>
                                            ($request['status'] ?? 0) >= 400 && ($request['status'] ?? 0) < 500,
                                    ])
                                >
                                    {{ $request['method'] ?? 'GET' }} {{ $request['status'] ?? 'Unknown' }}
                                </span>

                                @if (! empty($request['status_text']))
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $request['status_text'] }}
                                    </span>
                                @endif
                            </div>
                            <p
                                class="mt-1 truncate text-sm font-medium text-zinc-900 dark:text-white"
                                title="{{ $request['url'] ?? 'Unknown URL' }}"
                            >
                                {{ parse_url($request['url'] ?? '', PHP_URL_PATH) ?: parse_url($request['url'] ?? '', PHP_URL_HOST) ?: 'Unknown URL' }}
                            </p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ parse_url($request['url'] ?? '', PHP_URL_HOST) ?: 'Unknown host' }}
                            </p>
                        </div>
                        <div class="ml-4 text-right">
                            <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ $this->formatTime($request['time'] ?? 0) }}
                            </div>
                        </div>
                    </div>
                @endforeach

                @if (count($analysisData['failed_requests']) > 10)
                    <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">
                        ... and {{ count($analysisData['failed_requests']) - 10 }} more failed
                        request{{ count($analysisData['failed_requests']) - 10 === 1 ? '' : 's' }}
                    </p>
                @endif
            </div>
        </div>
    @endif

    <!-- Request Performance -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
        <!-- Slowest and Fastest Requests -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Performance Extremes</h3>
            <div class="space-y-6">
                <!-- Slowest Request -->
                @if (! empty($analysisData['performance']['slowest_request']))
                    <div>
                        <div class="mb-2 flex items-center">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30"
                            >
                                <flux:icon.clock class="h-4 w-4 text-red-600 dark:text-red-400" />
                            </div>
                            <h4 class="ml-2 text-sm font-semibold text-zinc-900 dark:text-white">Slowest Request</h4>
                        </div>
                        <div class="ml-10">
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $this->formatTime($analysisData['performance']['slowest_request']['time'] ?? 0) }}
                            </p>
                            <p
                                class="text-xs text-zinc-500 dark:text-zinc-400"
                                title="{{ $analysisData['performance']['slowest_request']['url'] ?? '' }}"
                            >
                                {{ parse_url($analysisData['performance']['slowest_request']['url'] ?? '', PHP_URL_PATH) ?: parse_url($analysisData['performance']['slowest_request']['url'] ?? '', PHP_URL_HOST) ?: 'Unknown URL' }}
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Fastest Request -->
                @if (! empty($analysisData['performance']['fastest_request']))
                    <div>
                        <div class="mb-2 flex items-center">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30"
                            >
                                <flux:icon.bolt class="h-4 w-4 text-green-600 dark:text-green-400" />
                            </div>
                            <h4 class="ml-2 text-sm font-semibold text-zinc-900 dark:text-white">Fastest Request</h4>
                        </div>
                        <div class="ml-10">
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $this->formatTime($analysisData['performance']['fastest_request']['time'] ?? 0) }}
                            </p>
                            <p
                                class="text-xs text-zinc-500 dark:text-zinc-400"
                                title="{{ $analysisData['performance']['fastest_request']['url'] ?? '' }}"
                            >
                                {{ parse_url($analysisData['performance']['fastest_request']['url'] ?? '', PHP_URL_PATH) ?: parse_url($analysisData['performance']['fastest_request']['url'] ?? '', PHP_URL_HOST) ?: 'Unknown URL' }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Request Methods -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Request Methods</h3>
            <div class="space-y-3">
                @php
                    $methodCounts = collect($analysisData['largest_resources'] ?? [])
                        ->concat($analysisData['failed_requests'] ?? [])
                        ->groupBy('method')
                        ->map->count()
                        ->sortDesc();
                @endphp

                @forelse ($methodCounts as $method => $count)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div
                                @class([
                                    'h-3 w-3 rounded-full',
                                    'bg-blue-500' => ($method ?? '') === 'GET',
                                    'bg-green-500' => ($method ?? '') === 'POST',
                                    'bg-yellow-500' => ($method ?? '') === 'PUT',
                                    'bg-red-500' => ($method ?? '') === 'DELETE',
                                    'bg-purple-500' => ($method ?? '') === 'PATCH',
                                    'bg-gray-500' => ! in_array($method ?? '', ['GET', 'POST', 'PUT', 'DELETE', 'PATCH']),
                                ])
                            ></div>

                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                {{ $method }}
                            </span>
                        </div>
                        <span class="text-sm font-semibold text-zinc-900 dark:text-white">
                            {{ $count }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">No method data available.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Requests by Type -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Requests by Type</h3>
            <flux:tooltip content="Resources grouped by MIME type">
                <flux:icon.information-circle class="h-4 w-4 text-zinc-400" />
            </flux:tooltip>
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($analysisData['requests_by_type'] as $type)
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="mb-2 flex items-center justify-between">
                        <h4 class="font-medium text-zinc-900 dark:text-white">{{ $type['type'] }}</h4>
                        <span class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $type['count'] }}</span>
                    </div>
                    <div class="space-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                        <div>Size: {{ $this->formatBytes($type['size']) }}</div>
                        <div>Transferred: {{ $this->formatBytes($type['transferred']) }}</div>
                        @if ($type['size'] > 0 && $type['transferred'] != $type['size'])
                            <div class="text-green-600 dark:text-green-400">
                                Saved: {{ $this->formatBytes($type['size'] - $type['transferred']) }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- All Requests Table -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">All Requests</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead>
                    <tr class="bg-zinc-100 dark:bg-zinc-900/50">
                        <th
                            class="px-3 py-3 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase dark:text-zinc-400"
                        >
                            URL
                        </th>
                        <th
                            class="px-3 py-3 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase dark:text-zinc-400"
                        >
                            Method
                        </th>
                        <th
                            class="px-3 py-3 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase dark:text-zinc-400"
                        >
                            Status
                        </th>
                        <th
                            class="px-3 py-3 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase dark:text-zinc-400"
                        >
                            Size
                        </th>
                        <th
                            class="px-3 py-3 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase dark:text-zinc-400"
                        >
                            Time
                        </th>
                        <th
                            class="px-3 py-3 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase dark:text-zinc-400"
                        >
                            Type
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                    @foreach (array_slice($analysisData['largest_resources'], 0, 50) as $resource)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                            <td class="px-3 py-4">
                                <div class="max-w-xs">
                                    <p
                                        class="truncate text-sm font-medium text-zinc-900 dark:text-white"
                                        title="{{ $resource['url'] }}"
                                    >
                                        {{ basename(parse_url($resource['url'], PHP_URL_PATH)) ?: parse_url($resource['url'], PHP_URL_HOST) }}
                                    </p>
                                    <p
                                        class="truncate text-xs text-zinc-500 dark:text-zinc-400"
                                        title="{{ $resource['url'] }}"
                                    >
                                        {{ parse_url($resource['url'], PHP_URL_HOST) }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-3 py-4">
                                <span
                                    @class([
                                        'inline-flex items-center rounded px-2 py-1 text-xs font-medium',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' => ($resource['method'] ?? '') === 'GET',
                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => ($resource['method'] ?? '') === 'POST',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' => ($resource['method'] ?? '') === 'PUT',
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' => ($resource['method'] ?? '') === 'DELETE',
                                        'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' =>
                                            ($resource['method'] ?? '') === 'PATCH',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300' => ! in_array($resource['method'] ?? '', [
                                            'GET',
                                            'POST',
                                            'PUT',
                                            'DELETE',
                                            'PATCH',
                                        ]),
                                    ])
                                >
                                    {{ $resource['method'] ?? 'UNKNOWN' }}
                                </span>
                            </td>
                            <td class="px-3 py-4">
                                <span
                                    class="@if($resource['status'] >= 500) bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 @elseif($resource['status'] >= 400) bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300 @elseif($resource['status'] >= 300) bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 @else bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 @endif inline-flex items-center rounded px-2 py-1 text-xs font-medium"
                                >
                                    {{ $resource['status'] }}
                                </span>
                            </td>
                            <td class="px-3 py-4 text-sm text-zinc-900 dark:text-white">
                                {{ $this->formatBytes($resource['size']) }}
                                @if ($resource['transferred'] != $resource['size'])
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        ({{ $this->formatBytes($resource['transferred']) }} transferred)
                                    </div>
                                @endif
                            </td>
                            <td class="px-3 py-4 text-sm text-zinc-900 dark:text-white">
                                {{ $this->formatTime($resource['time']) }}
                            </td>
                            <td class="px-3 py-4">
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $resource['mime_type'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if (count($analysisData['largest_resources']) > 50)
                <div class="mt-4 text-center">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        Showing first 50 of {{ count($analysisData['largest_resources']) }} total requests
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
