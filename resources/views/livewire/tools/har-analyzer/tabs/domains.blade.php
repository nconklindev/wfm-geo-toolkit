<!-- Domains Tab -->
<div class="space-y-8">
    @php
        $domains = collect($analysisData['domains'] ?? []);
        $totalDomains = $domains->count();
        $totalRequests = $domains->sum('requests');
        $totalSize = $domains->sum('size');
        $thirdPartyDomains = $this->thirdPartyDomains;
        $mainDomain = $domains->first()['domain'] ?? 'unknown';
    @endphp

    <!-- Domain Overview Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Domains -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                    <flux:icon.globe-alt class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Domains</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        {{ $totalDomains }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Third-Party Domains -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center">
                <div
                    @class([
                        'flex h-12 w-12 items-center justify-center rounded-lg',
                        'bg-red-100 dark:bg-red-900/30' => count($thirdPartyDomains) > 10,
                        'bg-yellow-100 dark:bg-yellow-900/30' => count($thirdPartyDomains) > 5 && count($thirdPartyDomains) <= 10,
                        'bg-blue-100 dark:bg-blue-900/30' => count($thirdPartyDomains) > 0 && count($thirdPartyDomains) <= 5,
                        'bg-green-100 dark:bg-green-900/30' => count($thirdPartyDomains) === 0,
                    ])
                >
                    <flux:icon.building-office
                        @class([
                            'h-6 w-6',
                            'text-red-600 dark:text-red-400' => count($thirdPartyDomains) > 10,
                            'text-yellow-600 dark:text-yellow-400' => count($thirdPartyDomains) > 5 && count($thirdPartyDomains) <= 10,
                            'text-blue-600 dark:text-blue-400' => count($thirdPartyDomains) > 0 && count($thirdPartyDomains) <= 5,
                            'text-green-600 dark:text-green-400' => count($thirdPartyDomains) === 0,
                        ])
                    />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Third-Party</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        {{ count($thirdPartyDomains) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Environment Type -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center">
                @php
                    $envType = $this->domainType;
                    $envIcon = match ($envType) {
                        'local' => 'computer-desktop',
                        'development' => 'wrench-screwdriver',
                        'staging' => 'beaker',
                        default => 'server',
                    };
                @endphp

                <div
                    @class([
                        'flex h-12 w-12 items-center justify-center rounded-lg',
                        'bg-zinc-100 dark:bg-zinc-900/30' => $envType === 'local',
                        'bg-purple-100 dark:bg-purple-900/30' => $envType === 'development',
                        'bg-orange-100 dark:bg-orange-900/30' => $envType === 'staging',
                        'bg-green-100 dark:bg-green-900/30' => $envType === 'production',
                    ])
                >
                    <flux:icon
                        name="{{ $envIcon }}"
                        @class([
                            'h-6 w-6',
                            'text-zinc-600 dark:text-zinc-400' => $envType === 'local',
                            'text-purple-600 dark:text-purple-400' => $envType === 'development',
                            'text-orange-600 dark:text-orange-400' => $envType === 'staging',
                            'text-green-600 dark:text-green-400' => $envType === 'production',
                        ])
                    />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Environment</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        {{ ucfirst($envType) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Average Response Time -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                    <flux:icon.clock class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Avg. Response</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        {{ $this->formatTime($domains->avg('avg_time') ?? 0) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Domain Performance Analysis -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
        <!-- Performance Breakdown -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Performance by Domain</h3>
                <flux:tooltip content="Response times and request counts by domain">
                    <flux:icon.information-circle class="h-4 w-4 text-zinc-400" />
                </flux:tooltip>
            </div>
            <div class="space-y-4">
                @foreach ($domains->take(5) as $domain)
                    @php
                        $percentage = $totalRequests > 0 ? round(($domain['requests'] / $totalRequests) * 100, 1) : 0;
                        $isMain = $loop->first;
                        $isSlow = ($domain['avg_time'] ?? 0) > 1000;
                    @endphp

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                @if ($isMain)
                                    <flux:icon.home class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                @elseif (in_array($domain, $thirdPartyDomains))
                                    <flux:icon.building-office class="h-4 w-4 text-orange-600 dark:text-orange-400" />
                                @else
                                    <flux:icon.server class="h-4 w-4 text-green-600 dark:text-green-400" />
                                @endif
                                <span
                                    class="max-w-48 truncate text-sm font-medium text-zinc-900 dark:text-white"
                                    title="{{ $domain['domain'] }}"
                                >
                                    {{ $domain['domain'] }}
                                </span>
                                @if ($isSlow)
                                    <flux:icon.exclamation-triangle class="h-4 w-4 text-red-500" />
                                @endif
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ $domain['requests'] }} requests
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $this->formatTime($domain['avg_time'] ?? 0) }} avg
                                </div>
                            </div>
                        </div>
                        <div class="h-2 w-full rounded-full bg-zinc-200 dark:bg-zinc-700">
                            <div
                                class="h-2 rounded-full bg-blue-600 transition-all duration-300"
                                style="width: {{ $percentage }}%"
                            ></div>
                        </div>
                        <div class="flex justify-between text-xs text-zinc-500 dark:text-zinc-400">
                            <span>{{ $percentage }}% of requests</span>
                            <span>{{ $this->formatBytes($domain['size'] ?? 0) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Domain Security Analysis -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Security Analysis</h3>
                <flux:tooltip content="HTTPS usage and security indicators">
                    <flux:icon.information-circle class="h-4 w-4 text-zinc-400" />
                </flux:tooltip>
            </div>
            <div class="space-y-4">
                @php
                    // Get HTTPS data from domain analysis which should include protocol info
                    $httpsDomainsData = collect($this->analysisData['domains'] ?? [])->mapWithKeys(function ($domain) {
                        // Check if this domain has HTTPS by looking for https:// in any URLs
                        $hasHttps = false;
                        if (isset($this->analysisData['overview']['entries'])) {
                            foreach ($this->analysisData['overview']['entries'] as $entry) {
                                if (isset($entry['request']['url'])) {
                                    $url = $entry['request']['url'];
                                    $parsedUrl = parse_url($url);
                                    $host = $parsedUrl['host'] ?? '';
                                    if ($host === $domain['domain'] && str_starts_with($url, 'https://')) {
                                        $hasHttps = true;
                                        break;
                                    }
                                }
                            }
                        }
                        return [$domain['domain'] => $hasHttps];
                    });

                    $httpsCount = collect($this->analysisData['domains'] ?? [])
                        ->where('has_https', true)
                        ->count();
                    $httpsPercentage = $totalDomains > 0 ? round(($httpsCount / $totalDomains) * 100, 1) : 0;
                @endphp

                <!-- HTTPS Usage -->
                <div
                    @class([
                        'flex items-start space-x-3 rounded-lg p-4',
                        'bg-green-50 dark:bg-green-900/20' => $httpsPercentage >= 80,
                        'bg-yellow-50 dark:bg-yellow-900/20' => $httpsPercentage >= 50 && $httpsPercentage < 80,
                        'bg-red-50 dark:bg-red-900/20' => $httpsPercentage < 50,
                    ])
                >
                    <flux:icon
                        @class([
                            'mt-0.5 h-5 w-5 flex-shrink-0',
                            'text-green-600 dark:text-green-400' => $httpsPercentage >= 80,
                            'text-yellow-600 dark:text-yellow-400' => $httpsPercentage >= 50 && $httpsPercentage < 80,
                            'text-red-600 dark:text-red-400' => $httpsPercentage < 50,
                        ])
                        name="{{ $httpsPercentage >= 80 ? 'shield-check' : ($httpsPercentage >= 50 ? 'exclamation-triangle' : 'shield-exclamation') }}"
                    />
                    <div>
                        <h4
                            @class([
                                'font-medium',
                                'text-green-800 dark:text-green-200' => $httpsPercentage >= 80,
                                'text-yellow-800 dark:text-yellow-200' => $httpsPercentage >= 50 && $httpsPercentage < 80,
                                'text-red-800 dark:text-red-200' => $httpsPercentage < 50,
                            ])
                        >
                            HTTPS Coverage: {{ $httpsPercentage }}%
                        </h4>
                        <p
                            @class([
                                'text-sm',
                                'text-green-700 dark:text-green-300' => $httpsPercentage >= 80,
                                'text-yellow-700 dark:text-yellow-300' => $httpsPercentage >= 50 && $httpsPercentage < 80,
                                'text-red-700 dark:text-red-300' => $httpsPercentage < 50,
                            ])
                        >
                            {{ $httpsCount }} of {{ $totalDomains }} domains use HTTPS
                        </p>
                    </div>
                </div>

                <!-- Known Third-Party Services -->
                @php
                    $knownServices = collect($thirdPartyDomains)
                        ->map(function ($domain) {
                            $domainName = $domain['domain'];
                            $service = match (true) {
                                str_contains($domainName, 'google') => ['name' => 'Google Services', 'type' => 'Analytics/Ads', 'color' => 'blue'],
                                str_contains($domainName, 'facebook') || str_contains($domainName, 'fbcdn') => ['name' => 'Facebook', 'type' => 'Social/Tracking', 'color' => 'blue'],
                                str_contains($domainName, 'cloudflare') => ['name' => 'Cloudflare', 'type' => 'CDN/Security', 'color' => 'orange'],
                                str_contains($domainName, 'amazonaws') => ['name' => 'AWS', 'type' => 'Cloud Services', 'color' => 'yellow'],
                                str_contains($domainName, 'jsdelivr') || str_contains($domainName, 'unpkg') => ['name' => 'CDN', 'type' => 'Content Delivery', 'color' => 'green'],
                                str_contains($domainName, 'youtube') || str_contains($domainName, 'youtu.be') => ['name' => 'YouTube', 'type' => 'Video/Media', 'color' => 'red'],
                                str_contains($domainName, 'twitter') || str_contains($domainName, 'twimg') => ['name' => 'Twitter', 'type' => 'Social Media', 'color' => 'blue'],
                                default => ['name' => 'Unknown Service', 'type' => 'Third-Party', 'color' => 'zinc'],
                            };
                            return array_merge($domain, $service);
                        })
                        ->take(5);
                @endphp

                @if ($knownServices->isNotEmpty())
                    <div>
                        <h5 class="mb-2 text-sm font-medium text-zinc-900 dark:text-white">Third-Party Services</h5>
                        <div class="space-y-2">
                            @foreach ($knownServices as $service)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <div class="bg-{{ $service['color'] }}-500 h-3 w-3 rounded-full"></div>
                                        <span class="text-sm text-zinc-700 dark:text-zinc-300">
                                            {{ $service['name'] }}
                                        </span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                            ({{ $service['type'] }})
                                        </span>
                                    </div>
                                    <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                        {{ $service['requests'] }} req
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Detailed Domain Table -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">All Domains</h3>
            <flux:tooltip content="Complete breakdown of all domains and their metrics">
                <flux:icon.information-circle class="h-4 w-4 text-zinc-400" />
            </flux:tooltip>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead>
                    <tr class="bg-zinc-100 dark:bg-zinc-900/50">
                        <th
                            class="px-3 py-3 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase dark:text-zinc-400"
                        >
                            Domain
                        </th>
                        <th
                            class="px-3 py-3 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase dark:text-zinc-400"
                        >
                            Type
                        </th>
                        <th
                            class="px-3 py-3 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase dark:text-zinc-400"
                        >
                            Requests
                        </th>
                        <th
                            class="px-3 py-3 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase dark:text-zinc-400"
                        >
                            Total Size
                        </th>
                        <th
                            class="px-3 py-3 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase dark:text-zinc-400"
                        >
                            Avg Time
                        </th>
                        <th
                            class="px-3 py-3 text-left text-xs font-medium tracking-wide text-zinc-500 uppercase dark:text-zinc-400"
                        >
                            Performance
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                    @foreach ($domains as $domain)
                        @php
                            $isMain = $loop->first;
                            $isThirdParty = in_array($domain, $thirdPartyDomains);
                            $isSlow = ($domain['avg_time'] ?? 0) > 1000;
                            $isLarge = ($domain['size'] ?? 0) > 1000000; // 1MB
                            $percentage = $totalRequests > 0 ? round(($domain['requests'] / $totalRequests) * 100, 1) : 0;
                            $domainHasHttps = $domain['has_https'] ?? false;
                        @endphp

                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                            <td class="px-3 py-4">
                                <div class="flex items-center space-x-2">
                                    @if ($isMain)
                                        <flux:icon.home class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    @elseif ($isThirdParty)
                                        <flux:icon.building-office
                                            class="h-4 w-4 text-orange-600 dark:text-orange-400"
                                        />
                                    @else
                                        <flux:icon.server class="h-4 w-4 text-green-600 dark:text-green-400" />
                                    @endif
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ $domain['domain'] }}
                                            </p>
                                            @if ($domainHasHttps)
                                                <flux:icon.shield-check
                                                    class="h-3 w-3 text-green-600 dark:text-green-400"
                                                />
                                            @else
                                                <flux:icon.shield-exclamation
                                                    class="h-3 w-3 text-red-600 dark:text-red-400"
                                                />
                                            @endif
                                        </div>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $percentage }}% of total requests
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-4">
                                @if ($isMain)
                                    <span
                                        class="inline-flex items-center rounded bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300"
                                    >
                                        Primary
                                    </span>
                                @elseif ($isThirdParty)
                                    <span
                                        class="inline-flex items-center rounded bg-orange-100 px-2 py-1 text-xs font-medium text-orange-800 dark:bg-orange-900/30 dark:text-orange-300"
                                    >
                                        Third-Party
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center rounded bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-300"
                                    >
                                        Subdomain
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-4 text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ number_format($domain['requests']) }}
                            </td>
                            <td class="px-3 py-4">
                                <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $this->formatBytes($domain['size'] ?? 0) }}
                                </div>
                                @if ($isLarge)
                                    <div class="text-xs text-red-600 dark:text-red-400">Large</div>
                                @endif
                            </td>
                            <td class="px-3 py-4">
                                <div
                                    @class(['text-sm font-medium', 'text-red-600 dark:text-red-400' => $isSlow, 'text-zinc-900 dark:text-white' => ! $isSlow])
                                >
                                    {{ $this->formatTime($domain['avg_time'] ?? 0) }}
                                </div>
                                @if ($isSlow)
                                    <div class="text-xs text-red-600 dark:text-red-400">Slow</div>
                                @endif
                            </td>
                            <td class="px-3 py-4">
                                @php
                                    $score = 100;
                                    if ($isSlow) {
                                        $score -= 30;
                                    }
                                    if ($isLarge) {
                                        $score -= 20;
                                    }
                                    if ($isThirdParty && $domain['requests'] > 10) {
                                        $score -= 10;
                                    }
                                    if (! $domainHasHttps) {
                                        $score -= 15;
                                    }
                                    $score = max(0, $score);
                                @endphp

                                <div class="flex items-center space-x-2">
                                    <div class="h-2 w-12 rounded-full bg-zinc-200 dark:bg-zinc-700">
                                        <div
                                            @class([
                                                'h-2 rounded-full transition-all duration-300',
                                                'bg-green-500' => $score >= 80,
                                                'bg-yellow-500' => $score >= 60 && $score < 80,
                                                'bg-red-500' => $score < 60,
                                            ])
                                            style="width: {{ $score }}%"
                                        ></div>
                                    </div>
                                    <span
                                        @class([
                                            'text-xs font-medium',
                                            'text-green-600 dark:text-green-400' => $score >= 80,
                                            'text-yellow-600 dark:text-yellow-400' => $score >= 60 && $score < 80,
                                            'text-red-600 dark:text-red-400' => $score < 60,
                                        ])
                                    >
                                        {{ $score }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Domain Recommendations -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Optimization Recommendations</h3>
            <flux:tooltip content="Actionable insights to improve domain performance">
                <flux:icon.information-circle class="h-4 w-4 text-zinc-400" />
            </flux:tooltip>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @php
                $slowDomains = $domains->filter(fn ($d) => ($d['avg_time'] ?? 0) > 1000);
                $largeDomains = $domains->filter(fn ($d) => ($d['size'] ?? 0) > 1000000);
                $manyThirdParty = count($thirdPartyDomains) > 5;
                $hasInsecureDomains = $httpsPercentage < 100;
            @endphp

            @if ($slowDomains->isNotEmpty())
                <div class="flex items-start space-x-3 rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                    <flux:icon.clock class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-600 dark:text-red-400" />
                    <div>
                        <h4 class="font-medium text-red-800 dark:text-red-200">Slow Response Times</h4>
                        <p class="text-sm text-red-700 dark:text-red-300">
                            {{ $slowDomains->count() }} domains have slow response times (>1s). Consider optimizing or
                            using a CDN.
                        </p>
                    </div>
                </div>
            @endif

            @if ($largeDomains->isNotEmpty())
                <div class="flex items-start space-x-3 rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/20">
                    <flux:icon.archive-box class="mt-0.5 h-5 w-5 flex-shrink-0 text-yellow-600 dark:text-yellow-400" />
                    <div>
                        <h4 class="font-medium text-yellow-800 dark:text-yellow-200">Large Resource Sizes</h4>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            {{ $largeDomains->count() }} domains serve large amounts of data. Consider compression and
                            optimization.
                        </p>
                    </div>
                </div>
            @endif

            @if ($manyThirdParty)
                <div class="flex items-start space-x-3 rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                    <flux:icon.building-office class="mt-0.5 h-5 w-5 flex-shrink-0 text-blue-600 dark:text-blue-400" />
                    <div>
                        <h4 class="font-medium text-blue-800 dark:text-blue-200">Many Third-Party Services</h4>
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            {{ count($thirdPartyDomains) }} third-party domains detected. Review if all are necessary
                            for performance.
                        </p>
                    </div>
                </div>
            @endif

            @if ($hasInsecureDomains)
                <div class="flex items-start space-x-3 rounded-lg bg-purple-50 p-4 dark:bg-purple-900/20">
                    <flux:icon.shield-exclamation
                        class="mt-0.5 h-5 w-5 flex-shrink-0 text-purple-600 dark:text-purple-400"
                    />
                    <div>
                        <h4 class="font-medium text-purple-800 dark:text-purple-200">Improve HTTPS Coverage</h4>
                        <p class="text-sm text-purple-700 dark:text-purple-300">
                            Some domains are not using HTTPS. Ensure all external resources use secure connections.
                        </p>
                    </div>
                </div>
            @endif

            @if ($slowDomains->isEmpty() && $largeDomains->isEmpty() && ! $manyThirdParty && ! $hasInsecureDomains)
                <div class="flex items-start space-x-3 rounded-lg bg-green-50 p-4 md:col-span-2 dark:bg-green-900/20">
                    <flux:icon.check-circle class="mt-0.5 h-5 w-5 flex-shrink-0 text-green-600 dark:text-green-400" />
                    <div>
                        <h4 class="font-medium text-green-800 dark:text-green-200">Excellent Domain Performance</h4>
                        <p class="text-sm text-green-700 dark:text-green-300">
                            Your domains are well-optimized with good response times, reasonable sizes, and secure
                            connections.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
