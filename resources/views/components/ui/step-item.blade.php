@props([
    'number' => '1',
    'text' => '',
    'color' => 'blue',
    'size' => 'md',
])

@php
    $colorClasses = match ($color) {
        'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
        'green' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
        'red' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200',
        'amber' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200',
        'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200',
        'violet' => 'bg-violet-100 text-violet-800 dark:bg-violet-900/50 dark:text-purple-200',
        'zinc' => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-900/50 dark:text-zinc-200',
        default => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
    };

    $sizeClasses = match ($size) {
        'sm' => 'h-5 w-5 text-xs',
        'md' => 'h-6 w-6 text-sm',
        'lg' => 'h-8 w-8 text-base',
        default => 'h-6 w-6 text-sm',
    };

    // Default classes that should always be present
    $defaultClasses = 'flex flex-shrink-0 items-center justify-center rounded-full font-medium';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start']) }}>
    <span class="{{ $defaultClasses }} {{ $sizeClasses }} {{ $colorClasses }}">
        {{ $number }}
    </span>

    @if ($text)
        <span class="ml-3">{{ $text }}</span>
    @elseif ($slot->isNotEmpty())
        <div class="ml-3">
            {{ $slot }}
        </div>
    @endif
</div>
