@props([])

<div {{ $attributes->merge(['class' => 'inline-flex items-center']) }}>
    <x-app-logo-icon />
    <span class="ml-1.5 truncate leading-none font-semibold">WFM Toolkit</span>
</div>
