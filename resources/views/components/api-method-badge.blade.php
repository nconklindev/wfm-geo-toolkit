@props([
    'method' => 'GET',
    'size' => 'sm',
    'color' => 'default',
])

<flux:badge
    variant="solid"
    size="{{ $size }}"
    :color="$color"
    {{ $attributes->merge(['class' => 'mr-3']) }}
>
    {{ strtoupper($method) }}
</flux:badge>
