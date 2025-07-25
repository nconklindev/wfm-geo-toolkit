@props([
    'class' => '',
])

<div {{ $attributes->merge(['class' => $class]) }}>
    {{ $slot }}
</div>
