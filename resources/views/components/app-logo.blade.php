@props([])

{{-- Root element to contain the logo parts and accept merged attributes --}}
<div {{ $attributes->merge(['class' => 'inline-flex items-center']) }}>
    {{-- Use inline-flex for proper alignment if used outside a flex parent --}}
    <x-app-logo-icon />
    <span class="ml-1.5 truncate leading-none font-semibold">
        {{-- Add margin for spacing, adjust as needed --}}
        WFM Geo Toolkit
    </span>
</div>
