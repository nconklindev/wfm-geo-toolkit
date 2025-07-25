@props([
    'text' => '',
    'color' => 'zinc',
    'size' => 'sm',
])

@php
    $colorClasses = match ($color) {
        'blue' => 'bg-blue-400 dark:bg-blue-500',
        'green' => 'bg-green-400 dark:bg-green-500',
        'red' => 'bg-red-400 dark:bg-red-500',
        'amber' => 'bg-amber-400 dark:bg-amber-500',
        'purple' => 'bg-purple-400 dark:bg-purple-500',
        default => 'bg-zinc-400 dark:bg-zinc-400',
    };

    $sizeClasses = match ($size) {
        'xs' => 'mt-2.5 h-1 w-1',
        'sm' => 'mt-2 h-2 w-2',
        'md' => 'mt-1.5 h-2.5 w-2.5',
        'lg' => 'mt-1 h-3 w-3',
        default => 'mt-2 h-2 w-2',
    };

    // Default classes for the bullet
    $defaultClasses = 'mr-3 flex-shrink-0 rounded-full';
@endphp

<li {{ $attributes->merge(['class' => 'flex items-start']) }}>
    <span class="{{ $defaultClasses }} {{ $sizeClasses }} {{ $colorClasses }}"></span>

    @if ($text)
        <span>{{ $text }}</span>
    @elseif ($slot->isNotEmpty())
        <div>
            {{ $slot }}
        </div>
    @endif
</li>
