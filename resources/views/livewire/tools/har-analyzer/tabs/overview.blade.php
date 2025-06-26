<!-- Overview Tab -->
<div class="flex flex-col gap-8 md:grid md:grid-cols-2">
    <!-- Requests by Type -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Requests by Type</h3>
        <div class="space-y-1.5">
            @forelse ($analysisData['requests_by_type'] as $type)
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="h-3 w-3 rounded-full bg-blue-500"></div>
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ Str::ucfirst($type['type']) }}
                        </span>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                            {{ $type['count'] }}
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $this->formatBytes($type['size']) }}
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-zinc-500 dark:text-zinc-400">No requests found.</p>
            @endforelse
        </div>
    </div>

    <!-- Status Codes -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Status Codes</h3>
        <div class="space-y-3">
            @forelse ($analysisData['status_codes'] ?? [] as $code => $count)
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div
                            @class([
                                'h-3 w-3 rounded-full',
                                'bg-red-500' => $code >= 500,
                                'bg-orange-500' => $code >= 400 && $code < 500,
                                'bg-yellow-500' => $code >= 300 && $code < 400,
                                'bg-green-500' => $code < 300,
                            ])
                        ></div>
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ $code }}
                        </span>
                    </div>
                    <span class="text-sm font-semibold text-zinc-900 dark:text-white">
                        {{ $count }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-zinc-500 dark:text-zinc-400">No status codes found.</p>
            @endforelse
        </div>
    </div>
</div>
