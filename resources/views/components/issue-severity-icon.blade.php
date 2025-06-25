@props([
    'severity',
    'config',
    'size' => 'h-4 w-4',
    'spacing' => 'mr-1',
])

@php
    $iconClasses = "{$config['text-color']} {$spacing} {$size}";
@endphp

@if ($config['icon'] === 'x-circle')
    <flux:icon.x-circle class="{{ $iconClasses }}" />
@elseif ($config['icon'] === 'exclamation-triangle')
    <flux:icon.exclamation-triangle class="{{ $iconClasses }}" />
@elseif ($config['icon'] === 'exclamation-circle')
    <flux:icon.exclamation-circle class="{{ $iconClasses }}" />
@else
    <flux:icon.information-circle class="{{ $iconClasses }}" />
@endif
