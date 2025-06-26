<!-- Security Tab -->
<div class="space-y-8">
    <!-- Security Overview Cards -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        <!-- HTTPS Usage -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center">
                <div
                    @class([
                        'flex h-12 w-12 items-center justify-center rounded-lg',
                        'bg-green-100 dark:bg-green-900/30' => ($analysisData['security']['https_percentage'] ?? 0) >= 90,
                        'bg-yellow-100 dark:bg-yellow-900/30' =>
                            ($analysisData['security']['https_percentage'] ?? 0) >= 50 &&
                            ($analysisData['security']['https_percentage'] ?? 0) < 90,
                        'bg-red-100 dark:bg-red-900/30' => ($analysisData['security']['https_percentage'] ?? 0) < 50,
                    ])
                >
                    <flux:icon.lock-closed
                        @class([
                            'h-6 w-6',
                            'text-green-600 dark:text-green-400' => ($analysisData['security']['https_percentage'] ?? 0) >= 90,
                            'text-yellow-600 dark:text-yellow-400' =>
                                ($analysisData['security']['https_percentage'] ?? 0) >= 50 &&
                                ($analysisData['security']['https_percentage'] ?? 0) < 90,
                            'text-red-600 dark:text-red-400' => ($analysisData['security']['https_percentage'] ?? 0) < 50,
                        ])
                    />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">HTTPS Usage</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        {{ $analysisData['security']['https_percentage'] ?? 0 }}%
                    </p>
                </div>
            </div>
        </div>

        <!-- Secure Headers -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center">
                <div
                    @class([
                        'flex h-12 w-12 items-center justify-center rounded-lg',
                        'bg-green-100 dark:bg-green-900/30' => ($analysisData['security']['secure_headers_count'] ?? 0) > 0,
                        'bg-red-100 dark:bg-red-900/30' => ($analysisData['security']['secure_headers_count'] ?? 0) === 0,
                    ])
                >
                    <flux:icon.shield-check
                        @class([
                            'h-6 w-6',
                            'text-green-600 dark:text-green-400' => ($analysisData['security']['secure_headers_count'] ?? 0) > 0,
                            'text-red-600 dark:text-red-400' => ($analysisData['security']['secure_headers_count'] ?? 0) === 0,
                        ])
                    />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Secure Headers</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        {{ $analysisData['security']['secure_headers_count'] ?? 0 }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Mixed Content Issues -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center">
                @php
                    $mixedContentCount = is_array($analysisData['security']['mixed_content'] ?? [])
                        ? count($analysisData['security']['mixed_content'])
                        : $analysisData['security']['mixed_content'] ?? 0;
                @endphp

                <div
                    @class([
                        'flex h-12 w-12 items-center justify-center rounded-lg',
                        'bg-green-100 dark:bg-green-900/30' => $mixedContentCount == 0,
                        'bg-red-100 dark:bg-red-900/30' => $mixedContentCount > 0,
                    ])
                >
                    <flux:icon.exclamation-triangle
                        @class([
                            'h-6 w-6',
                            'text-green-600 dark:text-green-400' => $mixedContentCount == 0,
                            'text-red-600 dark:text-red-400' => $mixedContentCount > 0,
                        ])
                    />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Mixed Content</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        {{ $mixedContentCount }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Insecure Requests -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center">
                <div
                    @class([
                        'flex h-12 w-12 items-center justify-center rounded-lg',
                        'bg-green-100 dark:bg-green-900/30' => ($analysisData['security']['insecure_requests'] ?? 0) == 0,
                        'bg-red-100 dark:bg-red-900/30' => ($analysisData['security']['insecure_requests'] ?? 0) > 0,
                    ])
                >
                    <flux:icon.lock-open
                        @class([
                            'h-6 w-6',
                            'text-green-600 dark:text-green-400' => ($analysisData['security']['insecure_requests'] ?? 0) == 0,
                            'text-red-600 dark:text-red-400' => ($analysisData['security']['insecure_requests'] ?? 0) > 0,
                        ])
                    />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Insecure Requests</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white">
                        {{ $analysisData['security']['insecure_requests'] ?? 0 }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Analysis Details -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
        <!-- HTTPS Analysis -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">HTTPS Analysis</h3>
                <flux:tooltip content="HTTPS encrypts data in transit">
                    <flux:icon.information-circle class="h-4 w-4 text-zinc-400" />
                </flux:tooltip>
            </div>
            <div class="space-y-4">
                @if (($analysisData['security']['https_percentage'] ?? 0) >= 90)
                    <div class="flex items-start space-x-3 rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                        <flux:icon.check-circle
                            class="mt-0.5 h-5 w-5 flex-shrink-0 text-green-600 dark:text-green-400"
                        />
                        <div>
                            <h4 class="font-medium text-green-800 dark:text-green-200">Excellent HTTPS Usage</h4>
                            <p class="text-sm text-green-700 dark:text-green-300">
                                {{ $analysisData['security']['https_percentage'] ?? 0 }}% of requests are using HTTPS.
                                Great job securing your traffic!
                            </p>
                        </div>
                    </div>
                @elseif (($analysisData['security']['https_percentage'] ?? 0) >= 50)
                    <div class="flex items-start space-x-3 rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/20">
                        <flux:icon.exclamation-triangle
                            class="mt-0.5 h-5 w-5 flex-shrink-0 text-yellow-600 dark:text-yellow-400"
                        />
                        <div>
                            <h4 class="font-medium text-yellow-800 dark:text-yellow-200">Moderate HTTPS Usage</h4>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                {{ $analysisData['security']['https_percentage'] ?? 0 }}% of requests use HTTPS.
                                Consider migrating remaining HTTP requests to HTTPS.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="flex items-start space-x-3 rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                        <flux:icon.x-circle class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-600 dark:text-red-400" />
                        <div>
                            <h4 class="font-medium text-red-800 dark:text-red-200">Low HTTPS Usage</h4>
                            <p class="text-sm text-red-700 dark:text-red-300">
                                Only {{ $analysisData['security']['https_percentage'] ?? 0 }}% of requests use HTTPS.
                                This exposes sensitive data to potential interception.
                            </p>
                        </div>
                    </div>
                @endif

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Secure Requests (HTTPS)</span>
                        <span class="font-semibold text-green-600 dark:text-green-400">
                            {{ ($analysisData['overview']['total_requests'] ?? 0) - ($analysisData['security']['insecure_requests'] ?? 0) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Insecure Requests (HTTP)</span>
                        <span class="font-semibold text-red-600 dark:text-red-400">
                            {{ $analysisData['security']['insecure_requests'] ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Headers -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Security Headers</h3>
                <flux:tooltip content="Headers that enhance security like CSP, HSTS">
                    <flux:icon.information-circle class="h-4 w-4 text-zinc-400" />
                </flux:tooltip>
            </div>
            <div class="space-y-4">
                @if (($analysisData['security']['secure_headers_count'] ?? 0) > 0)
                    <div class="flex items-start space-x-3 rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                        <flux:icon.check-circle
                            class="mt-0.5 h-5 w-5 flex-shrink-0 text-green-600 dark:text-green-400"
                        />
                        <div>
                            <h4 class="font-medium text-green-800 dark:text-green-200">Security Headers Detected</h4>
                            <p class="text-sm text-green-700 dark:text-green-300">
                                Found {{ $analysisData['security']['secure_headers_count'] ?? 0 }} responses with
                                security headers.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="flex items-start space-x-3 rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                        <flux:icon.x-circle class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-600 dark:text-red-400" />
                        <div>
                            <h4 class="font-medium text-red-800 dark:text-red-200">No Security Headers</h4>
                            <p class="text-sm text-red-700 dark:text-red-300">
                                No security headers detected. Consider implementing HSTS, CSP, and other security
                                headers.
                            </p>
                        </div>
                    </div>
                @endif

                <div class="space-y-2">
                    <h5 class="text-sm font-medium text-zinc-900 dark:text-white">Recommended Headers:</h5>
                    <div class="space-y-1 text-xs text-zinc-600 dark:text-zinc-400">
                        <div>
                            •
                            <strong>Strict-Transport-Security</strong>
                            - Forces HTTPS connections
                        </div>
                        <div>
                            •
                            <strong>Content-Security-Policy</strong>
                            - Prevents XSS attacks
                        </div>
                        <div>
                            •
                            <strong>X-Frame-Options</strong>
                            - Prevents clickjacking
                        </div>
                        <div>
                            •
                            <strong>X-Content-Type-Options</strong>
                            - Prevents MIME type sniffing
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mixed Content Issues -->
    @php
        $mixedContentArray = is_array($analysisData['security']['mixed_content'] ?? [])
            ? $analysisData['security']['mixed_content']
            : [];
    @endphp

    @if (count($mixedContentArray) > 0)
        <div class="rounded-xl border border-red-200 bg-red-50 p-6 shadow-sm dark:border-red-800 dark:bg-red-900/20">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
                        <flux:icon.exclamation-triangle class="h-5 w-5 text-red-600 dark:text-red-400" />
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-semibold text-red-900 dark:text-red-100">Mixed Content Detected</h3>
                        <p class="text-sm text-red-700 dark:text-red-300">
                            HTTP resources loaded on HTTPS pages compromise security
                        </p>
                    </div>
                </div>
                <flux:tooltip content="HTTP content on HTTPS pages can be blocked by browsers">
                    <flux:icon.information-circle class="h-4 w-4 text-red-500" />
                </flux:tooltip>
            </div>
            <div class="space-y-3">
                @foreach (array_slice($mixedContentArray, 0, 10) as $mixedContent)
                    <div
                        class="flex items-center justify-between rounded-lg border border-red-200 bg-white p-3 dark:border-red-700 dark:bg-red-900/10"
                    >
                        <div class="min-w-0 flex-1">
                            <p
                                class="truncate text-sm font-medium text-zinc-900 dark:text-white"
                                title="{{ $mixedContent['url'] ?? '' }}"
                            >
                                {{ basename(parse_url($mixedContent['url'] ?? '', PHP_URL_PATH)) ?: parse_url($mixedContent['url'] ?? '', PHP_URL_HOST) }}
                            </p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $mixedContent['type'] ?? 'Unknown' }}
                                • {{ parse_url($mixedContent['url'] ?? '', PHP_URL_HOST) }}
                            </p>
                        </div>
                        <div class="ml-4">
                            <span
                                class="inline-flex items-center rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-800 dark:bg-red-900/30 dark:text-red-300"
                            >
                                HTTP
                            </span>
                        </div>
                    </div>
                @endforeach

                @if (count($mixedContentArray) > 10)
                    <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">
                        ... and {{ count($mixedContentArray) - 10 }} more mixed content issues
                    </p>
                @endif
            </div>
        </div>
    @endif

    <!-- Security Recommendations -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Security Recommendations</h3>
            <flux:tooltip content="Actionable steps to improve security">
                <flux:icon.information-circle class="h-4 w-4 text-zinc-400" />
            </flux:tooltip>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @if (($analysisData['security']['https_percentage'] ?? 0) < 100)
                <div class="flex items-start space-x-3 rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                    <flux:icon.lock-closed class="mt-0.5 h-5 w-5 flex-shrink-0 text-blue-600 dark:text-blue-400" />
                    <div>
                        <h4 class="font-medium text-blue-800 dark:text-blue-200">Migrate to HTTPS</h4>
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            Convert all HTTP requests to HTTPS to encrypt data in transit and improve user trust.
                        </p>
                    </div>
                </div>
            @endif

            @if (($analysisData['security']['secure_headers_count'] ?? 0) == 0)
                <div class="flex items-start space-x-3 rounded-lg bg-purple-50 p-4 dark:bg-purple-900/20">
                    <div class="mt-0.5 h-5 w-5 flex-shrink-0 rounded-full bg-purple-500"></div>
                    <div>
                        <h4 class="font-medium text-purple-800 dark:text-purple-200">Implement Security Headers</h4>
                        <p class="text-sm text-purple-700 dark:text-purple-300">
                            Add security headers like HSTS, CSP, and X-Frame-Options to protect against common attacks.
                        </p>
                    </div>
                </div>
            @endif

            @if (count($mixedContentArray) > 0)
                <div class="flex items-start space-x-3 rounded-lg bg-amber-50 p-4 dark:bg-amber-900/20">
                    <div class="mt-0.5 h-5 w-5 flex-shrink-0 rounded-full bg-amber-500"></div>
                    <div>
                        <h4 class="font-medium text-amber-800 dark:text-amber-200">Fix Mixed Content</h4>
                        <p class="text-sm text-amber-700 dark:text-amber-300">
                            Update HTTP resources to use HTTPS to prevent browsers from blocking them.
                        </p>
                    </div>
                </div>
            @endif

            @if (($analysisData['security']['https_percentage'] ?? 0) >= 90 && ($analysisData['security']['secure_headers_count'] ?? 0) > 0 && count($mixedContentArray) == 0)
                <div class="flex items-start space-x-3 rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                    <flux:icon.check-circle class="mt-0.5 h-5 w-5 flex-shrink-0 text-green-600 dark:text-green-400" />
                    <div>
                        <h4 class="font-medium text-green-800 dark:text-green-200">Excellent Security Posture</h4>
                        <p class="text-sm text-green-700 dark:text-green-300">
                            Your application demonstrates good security practices with HTTPS and security headers in
                            place.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
