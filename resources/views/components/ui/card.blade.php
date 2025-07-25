@props([
    'header' => null,
    'variant' => 'default',
    'warning' => 'warning',
    'info' => 'info',
    'success' => 'success',
    'danger' => 'danger',
])

@php
    $classes = match ($variant) {
        'warning' => 'border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20',
        'info' => 'border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20',
        'success' => 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20',
        'danger' => 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20',
        default => 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800',
    };
@endphp

<div {{ $attributes->merge(['class' => "rounded-lg border shadow-sm $classes"]) }}>
    @if ($header)
        <div class="border-b border-inherit px-6 py-4">
            {{ $header }}
        </div>
    @endif

    <div class="p-6">
        {{ $slot }}
    </div>
</div>
