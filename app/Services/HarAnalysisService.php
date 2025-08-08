<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class HarAnalysisService
{
    protected array $harData;

    protected Collection $entries;

    public function __construct(string $harFilePath)
    {
        $content = file_get_contents($harFilePath);
        $this->harData = json_decode($content, true);

        if (! $this->harData || ! isset($this->harData['log']['entries'])) {
            throw new InvalidArgumentException('Invalid HAR file format');
        }

        $this->entries = collect($this->harData['log']['entries']);
    }

    public function getOverview(): array
    {
        return [
            'total_requests' => $this->entries->count(),
            'total_size' => $this->getTotalSize(),
            'total_transferred' => $this->getTotalTransferred(),
            'load_time' => $this->getLoadTime(),
            'failed_requests' => $this->getFailedRequestsCount(),
            'unique_domains' => $this->getUniqueDomains()->count(),
            'compression_savings' => $this->getCompressionSavings(),
        ];
    }

    private function getTotalSize(): int
    {
        return $this->entries->sum(
            fn ($entry) => max(0, $entry['response']['content']['size'] ?? 0),
        );
    }

    private function getTotalTransferred(): int
    {
        return $this->entries->sum(
            fn ($entry) => max(0, $entry['response']['bodySize'] ?? 0),
        );
    }

    private function getLoadTime(): float
    {
        if ($this->entries->isEmpty()) {
            return 0;
        }

        // Convert all times to timestamps (in milliseconds)
        $timeData = $this->entries->map(function ($entry) {
            $startTime = Carbon::parse($entry['startedDateTime']);
            $startMs = $startTime->timestamp * 1000 + intval(
                $startTime->micro / 1000,
            );
            $duration = floatval($entry['time'] ?? 0);

            return [
                'start' => $startMs,
                'end' => $startMs + $duration,
            ];
        });

        $startTime = $timeData->min('start');
        $endTime = $timeData->max('end');

        return max(0, $endTime - $startTime); // Ensure we never return negative
    }

    private function getFailedRequestsCount(): int
    {
        return $this->entries->filter(
            fn ($entry) => $entry['response']['status'] >= 400,
        )->count();
    }

    private function getUniqueDomains(): Collection
    {
        return $this->entries->map(function ($entry) {
            $url = parse_url($entry['request']['url']);

            return $url['host'] ?? 'unknown';
        })->unique();
    }

    private function getCompressionSavings(): array
    {
        $totalSize = $this->getTotalSize();
        $totalTransferred = $this->getTotalTransferred();

        $savings = $totalSize - $totalTransferred;
        $percentage = $totalSize > 0 ? round(($savings / $totalSize) * 100, 2)
            : 0;

        return [
            'bytes_saved' => $savings,
            'percentage' => $percentage,
        ];
    }

    public function getPerformanceRecommendations(): array
    {
        $recommendations = [];
        $performance = $this->getPerformanceMetrics();

        // High server response time - diagnostic insight
        if (($performance['avg_wait_time'] ?? 0) > 1000) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'High Server Response Time Detected',
                'description' => "Average wait time is {$this->formatTime($performance['avg_wait_time'])}. This suggests potential backend performance issues or database bottlenecks in WFM.",
                'priority' => 'high',
            ];
        }

        // Slow authentication - actionable for user
        if (($performance['auth_api_avg_time'] ?? 0) > 3000) {
            $recommendations[] = [
                'type' => 'error',
                'title' => 'Slow Authentication Response',
                'description' => "Authentication APIs are averaging {$this->formatTime($performance['auth_api_avg_time'])}. Users may experience login delays or timeouts.",
                'priority' => 'critical',
            ];
        }

        // Slow data APIs - diagnostic insight
        if (($performance['data_api_avg_time'] ?? 0) > 2000) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Slow Data API Responses',
                'description' => "Data APIs are averaging {$this->formatTime($performance['data_api_avg_time'])}. This may indicate WFM performance issues during peak usage.",
                'priority' => 'high',
            ];
        }

        // High number of failed requests - actionable insight
        $failedCount = count($this->getFailedRequests());
        if ($failedCount > 10) {
            $recommendations[] = [
                'type' => 'error',
                'title' => 'Multiple Failed Requests',
                'description' => "$failedCount requests failed. Check the Failed Requests tab for specific errors that may indicate WFM service issues.",
                'priority' => 'critical',
            ];
        }

        // Large resource sizes - diagnostic insight
        $largestResource = collect($this->getLargestResources(1))->first();
        if ($largestResource && $largestResource['size'] > 5000000) { // 5MB
            $recommendations[] = [
                'type' => 'info',
                'title' => 'Large Resource Download Detected',
                'description' => "Largest resource is {$this->formatBytes($largestResource['size'])}. This may contribute to slow page loading, especially on mobile devices.",
                'priority' => 'medium',
            ];
        }

        // DNS issues - network diagnostic
        if (($performance['avg_dns_time'] ?? 0) > 500) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'DNS Resolution Delays',
                'description' => "DNS lookups are averaging {$this->formatTime($performance['avg_dns_time'])}. This may indicate network connectivity issues.",
                'priority' => 'medium',
            ];
        }

        // SSL handshake issues - network diagnostic
        if (($performance['avg_ssl_time'] ?? 0) > 1000) {
            $recommendations[] = [
                'type' => 'info',
                'title' => 'SSL Handshake Delays',
                'description' => "SSL handshakes are averaging {$this->formatTime($performance['avg_ssl_time'])}. This may indicate certificate or network issues.",
                'priority' => 'low',
            ];
        }

        // Mobile performance insight
        $totalLoadTime = $this->getLoadTime();
        if ($totalLoadTime > 10000) { // 10 seconds
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Poor Mobile Experience Expected',
                'description' => "Total load time is {$this->formatTime($totalLoadTime)}. Mobile users will likely experience significant delays.",
                'priority' => 'high',
            ];
        }

        // Cache insights - diagnostic only
        if (($performance['cache_hit_rate'] ?? 0) < 30) {
            $recommendations[] = [
                'type' => 'info',
                'title' => 'Low Cache Utilization',
                'description' => "Only {$performance['cache_hit_rate']}% of resources are cached. This indicates most requests are fetching fresh data from WFM servers.",
                'priority' => 'low',
            ];
        }

        return $recommendations;
    }

    public function getPerformanceMetrics(): array
    {
        $times = $this->entries->map(function ($entry) {
            return [
                'dns' => $entry['timings']['dns'] ?? 0,
                'connect' => $entry['timings']['connect'] ?? 0,
                'ssl' => $entry['timings']['ssl'] ?? 0,
                'send' => $entry['timings']['send'] ?? 0,
                'wait' => $entry['timings']['wait'] ?? 0,
                'receive' => $entry['timings']['receive'] ?? 0,
                'total' => $entry['time'] ?? 0,
            ];
        });

        $apiMetrics = $this->getApiPerformanceMetrics();
        $resourceMetrics = $this->getResourcePerformanceMetrics();
        $cacheMetrics = $this->getCacheAnalysis();
        $compressionAnalysis = $this->getCompressionAnalysis();

        return array_merge([
            'avg_dns_time' => round($times->avg('dns'), 2),
            'avg_connect_time' => round($times->avg('connect'), 2),
            'avg_ssl_time' => round($times->avg('ssl'), 2),
            'avg_wait_time' => round($times->avg('wait'), 2),
            'avg_receive_time' => round($times->avg('receive'), 2),
            'avg_send_time' => round($times->avg('send'), 2),
            'slowest_request' => $this->getSlowestRequest(),
            'fastest_request' => $this->getFastestRequest(),
        ], $apiMetrics, $resourceMetrics, $cacheMetrics, $compressionAnalysis);
    }

    public function getApiPerformanceMetrics(): array
    {
        $apiRequests = $this->entries->filter(function ($entry) {
            $url = $entry['request']['url'];
            $mimeType = $entry['response']['content']['mimeType'] ?? '';

            return str_contains($mimeType, 'application/json')
                || str_contains(
                    $url,
                    '/api/',
                )
                || str_contains($url, '/auth/')
                || str_contains($url, '/login')
                || str_contains($url, '/logout');
        });

        $authRequests = $apiRequests->filter(function ($entry) {
            $url = $entry['request']['url'];

            return str_contains($url, '/auth/') || str_contains($url, '/login')
                || str_contains($url, '/logout')
                || str_contains($url, '/token');
        });

        $dataRequests = $apiRequests->filter(function ($entry) {
            $url = $entry['request']['url'];

            return str_contains($url, '/api/')
                &&
                ! str_contains($url, '/auth/')
                &&
                ! str_contains($url, '/upload');
        });

        $uploadRequests = $apiRequests->filter(function ($entry) {
            $url = $entry['request']['url'];
            $method = $entry['request']['method'];

            return str_contains($url, '/upload')
                || ($method === 'POST'
                    && str_contains($url, '/files'));
        });

        return [
            'auth_api_avg_time' => $authRequests->avg('time') ?? 0,
            'data_api_avg_time' => $dataRequests->avg('time') ?? 0,
            'upload_api_avg_time' => $uploadRequests->avg('time') ?? 0,
            'total_api_requests' => $apiRequests->count(),
        ];
    }

    public function getResourcePerformanceMetrics(): array
    {
        $cssRequests = $this->entries->filter(function ($entry) {
            $mimeType = $entry['response']['content']['mimeType'] ?? '';

            return str_contains($mimeType, 'text/css');
        });

        $jsRequests = $this->entries->filter(function ($entry) {
            $mimeType = $entry['response']['content']['mimeType'] ?? '';

            return str_contains($mimeType, 'javascript');
        });

        $imageRequests = $this->entries->filter(function ($entry) {
            $mimeType = $entry['response']['content']['mimeType'] ?? '';

            return str_contains($mimeType, 'image/');
        });

        return [
            'css_load_time' => round($cssRequests->avg('time') ?? 0, 2),
            'js_load_time' => round($jsRequests->avg('time') ?? 0, 2),
            'image_load_time' => round($imageRequests->avg('time') ?? 0, 2),
            'css_requests' => $cssRequests->count(),
            'js_requests' => $jsRequests->count(),
            'image_requests' => $imageRequests->count(),
        ];
    }

    public function getCacheAnalysis(): array
    {
        $cachedRequests = $this->entries->filter(function ($entry) {
            $headers = collect($entry['response']['headers'] ?? []);
            $cacheControl = $headers->firstWhere(
                'name',
                'cache-control',
            )['value'] ?? '';
            $expires = $headers->firstWhere('name', 'expires')['value'] ?? '';
            $etag = $headers->firstWhere('name', 'etag')['value'] ?? '';
            $lastModified = $headers->firstWhere(
                'name',
                'last-modified',
            )['value'] ?? '';

            return ! empty($cacheControl) || ! empty($expires) || ! empty($etag)
                || ! empty($lastModified);
        });

        $totalRequests = $this->entries->count();
        $cacheHitRate = $totalRequests > 0 ? round(
            ($cachedRequests->count() / $totalRequests) * 100,
            1,
        ) : 0;

        return [
            'cache_hit_rate' => $cacheHitRate,
            'cached_resources' => $cachedRequests->count(),
            'non_cached_resources' => $totalRequests - $cachedRequests->count(),
        ];
    }

    public function getCompressionAnalysis(): array
    {
        $compressedRequests = $this->entries->filter(function ($entry) {
            $headers = collect($entry['response']['headers'] ?? []);
            $encoding = $headers->firstWhere(
                'name',
                'content-encoding',
            )['value'] ?? '';

            return str_contains($encoding, 'gzip')
                || str_contains(
                    $encoding,
                    'br',
                )
                || str_contains($encoding, 'deflate');
        });

        $totalSize = $this->getTotalSize();
        $totalTransferred = $this->getTotalTransferred();
        $compressionRatio = $totalSize > 0 ? round(
            ($totalTransferred / $totalSize) * 100,
            1,
        ) : 100;

        return [
            'compression_ratio' => $compressionRatio,
            'compressed_requests' => $compressedRequests->count(),
            'uncompressed_requests' => $this->entries->count()
                - $compressedRequests->count(),
            'compression_savings_bytes' => $totalSize - $totalTransferred,
        ];
    }

    private function getSlowestRequest(): array
    {
        $slowest = $this->entries->sortByDesc('time')->first();

        return [
            'url' => $slowest['request']['url'] ?? '',
            'time' => $slowest['time'] ?? 0,
            'status' => $slowest['response']['status'] ?? 0,
        ];
    }

    private function getFastestRequest(): array
    {
        $fastest = $this->entries->sortBy('time')->first();

        return [
            'url' => $fastest['request']['url'] ?? '',
            'time' => $fastest['time'] ?? 0,
            'status' => $fastest['response']['status'] ?? 0,
        ];
    }

    private function formatTime($milliseconds): string
    {
        if ($milliseconds >= 1000) {
            return number_format($milliseconds / 1000, 2).'s';
        }

        $decimals = $milliseconds < 1 ? 3 : 2;

        return number_format($milliseconds, $decimals).'ms';
    }

    public function getFailedRequests(): array
    {
        return $this->entries
            ->filter(fn ($entry) => $entry['response']['status'] >= 400)
            ->map(function ($entry) {
                return [
                    'url' => $entry['request']['url'],
                    'method' => $entry['request']['method'],
                    'status' => $entry['response']['status'],
                    'status_text' => $entry['response']['statusText'] ?? '',
                    'time' => $entry['time'] ?? 0,
                ];
            })
            ->values()
            ->toArray();
    }

    public function getLargestResources(int $limit = 10): array
    {
        return $this->entries
            ->sortByDesc(
                fn ($entry) => max(0, $entry['response']['content']['size'] ?? 0),
            )
            ->take($limit)
            ->map(function ($entry) {
                return [
                    'url' => $entry['request']['url'],
                    'method' => $entry['request']['method'],
                    'status' => $entry['response']['status'],
                    'size' => max(
                        0,
                        $entry['response']['content']['size'] ?? 0,
                    ),
                    'transferred' => max(
                        0,
                        $entry['response']['bodySize'] ?? 0,
                    ),
                    'time' => $entry['time'] ?? 0,
                    'mime_type' => $entry['response']['content']['mimeType']
                        ?? 'unknown',
                ];
            })
            ->values()
            ->toArray();
    }

    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }

    public function getRequestsByType(): array
    {
        $types = $this->entries->groupBy(function ($entry) {
            $typeMap = [
                'text/html' => 'HTML',
                'text/css' => 'CSS',
                'application/javascript' => 'JavaScript',
                'text/javascript' => 'JavaScript',
                'image/' => 'Images',
                'application/json' => 'JSON/API',
                'font/' => 'Fonts',
                'application/font' => 'Fonts',
            ];
            $mimeType = $entry['response']['content']['mimeType'] ?? '';

            foreach ($typeMap as $pattern => $type) {
                if (str_contains($mimeType, $pattern)) {
                    return $type;
                }
            }

            return 'Other';
        });

        return $types->map(function ($requests, $type) {
            return [
                'type' => $type,
                'count' => $requests->count(),
                'size' => $requests->sum(
                    fn ($r) => max(0, $r['response']['content']['size'] ?? 0),
                ),
                'transferred' => $requests->sum(
                    fn ($r) => max(0, $r['response']['bodySize'] ?? 0),
                ),
            ];
        })->values()->toArray();
    }

    public function getStatusCodes(): array
    {
        return $this->entries
            ->groupBy(fn ($entry) => $entry['response']['status'])
            ->map(fn ($group) => $group->count())
            ->sortKeys()
            ->toArray();
    }

    public function getSecurityAnalysis(): array
    {
        $httpsCount = $this->entries->filter(
            fn ($entry) => str_starts_with($entry['request']['url'], 'https://'),
        )->count();

        $secureHeaders = $this->entries->filter(function ($entry) {
            $headers = collect($entry['response']['headers'] ?? []);

            return $headers->contains(
                fn ($header) => in_array(strtolower($header['name']), [
                    'strict-transport-security',
                    'content-security-policy',
                    'x-frame-options',
                    'x-content-type-options',
                ]),
            );
        })->count();

        $totalEntries = $this->entries->count();

        return [
            'https_percentage' => $totalEntries > 0 ? round(
                ($httpsCount / $totalEntries) * 100,
                2,
            ) : 0,
            'secure_headers_count' => $secureHeaders,
            'mixed_content' => $this->getMixedContentIssues(),
            'insecure_requests' => $this->entries->count() - $httpsCount,
        ];
    }

    private function getMixedContentIssues(): int
    {
        return $this->entries->filter(function ($entry) {
            $url = $entry['request']['url'];
            $referrer = $entry['request']['headers'] ?? [];
            $referrerUrl = collect($referrer)->firstWhere(
                'name',
                'referer',
            )['value'] ?? '';

            /** @noinspection HttpUrlsUsage */
            return str_starts_with($referrerUrl, 'https://')
                && str_starts_with(
                    $url,
                    'http://',
                );
        })->count();
    }

    public function getDomainAnalysis(): array
    {
        return $this->entries
            ->groupBy(function ($entry) {
                $url = parse_url($entry['request']['url']);

                return $url['host'] ?? 'unknown';
            })
            ->map(function ($requests, $domain) {
                // Check if any request to this domain uses HTTPS
                $hasHttps = $requests->contains(function ($entry) {
                    return str_starts_with(
                        $entry['request']['url'],
                        'https://',
                    );
                });

                return [
                    'domain' => $domain,
                    'requests' => $requests->count(),
                    'size' => $requests->sum(
                        fn ($r) => max(0, $r['response']['content']['size'] ?? 0),
                    ),
                    'avg_time' => round(
                        $requests->avg(fn ($r) => $r['time'] ?? 0),
                        2,
                    ),
                    'has_https' => $hasHttps,
                ];
            })
            ->sortByDesc('requests')
            ->values()
            ->toArray();
    }

    public function getTimelineData(): array
    {
        if ($this->entries->isEmpty()) {
            return [];
        }

        $startTime = $this->entries->min('startedDateTime');
        $startTimestamp = Carbon::parse($startTime)->timestamp * 1000;

        return $this->entries->map(function ($entry) use ($startTimestamp) {
            $entryTime = Carbon::parse($entry['startedDateTime'])->timestamp
                * 1000;

            return [
                'url' => $entry['request']['url'],
                'start' => $entryTime - $startTimestamp,
                'duration' => $entry['time'] ?? 0,
                'status' => $entry['response']['status'],
                'method' => $entry['request']['method'],
                'size' => max(
                    0,
                    $entry['response']['content']['size'] ?? 0,
                ),
            ];
        })->sortBy('start')->values()->toArray();
    }
}
