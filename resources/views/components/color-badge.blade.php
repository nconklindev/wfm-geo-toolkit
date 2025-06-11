@props([
    'color',
])

<span
    {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium']) }}
    style="color: {{ $color }}; background-color: {{ $color }}20"
>
    {{ $slot }}
</span>
