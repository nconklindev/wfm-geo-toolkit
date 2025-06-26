<?php

namespace App\Concerns;

trait FormatsHarData
{
    public function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }

    public function formatTime($milliseconds): string
    {
        if ($milliseconds >= 1000) {
            return number_format($milliseconds / 1000, 2).'s';
        }

        $decimals = $milliseconds < 1 ? 3 : 2;

        return number_format($milliseconds, $decimals).'ms';
    }
}
