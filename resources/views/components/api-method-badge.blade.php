@props([
    'method' => 'GET',
    'color' => 'default',
])

<flux:badge
    variant="solid"
    size="sm"
    :color="$color"
    {{ $attributes->merge(['class' => 'mr-3']) }}
>
    {{ strtoupper($method) }}
</flux:badge>
