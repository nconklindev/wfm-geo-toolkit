@props([
    'color' => null,
    'method',
    'heading',
    '$wfmEndpoint',
])

<div {{ $attributes->class(['border-b border-zinc-200 pb-4 dark:border-zinc-700']) }}>
    <div class="flex items-center space-x-3">
        <x-api-method-badge :method="$method" />
        <div>
            <flux:heading size="lg">{{ $heading }}</flux:heading>
            <flux:text variant="subtle" size="sm">{{ $method }} {{ $wfmEndpoint }}</flux:text>
        </div>
    </div>
</div>
