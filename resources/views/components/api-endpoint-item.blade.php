@props([
    'value',
    'label',
    'method',
    'color',
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

<button
    type="button"
    @click="selected = { value: '{{ $value }}', label: '{{ $label }}' }; open = false; $wire.selectEndpoint('{{ $value }}', '{{ $label }}')"
    {{ $attributes->class(['flex w-full items-center px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700']) }}
    wire:navigate
>
    <x-api-method-badge :color="$color" :method="$method" />
    <span>{{ $slot }}</span>
</button>
