@props([
    'method' => 'GET',
    'color' => 'default',
])

@php
    $color = match (strtolower($method)) {
        'get' => 'green',
        'post' => 'amber',
        'delete' => 'red',
        'put' => 'purple',
        default => 'zinc',
    };
@endphp

<flux:badge
    variant="solid"
    size="sm"
    :color="$color"
    {{ $attributes->merge(['class' => 'mr-3']) }}
>
    {{ strtoupper($method) }}
</flux:badge>
