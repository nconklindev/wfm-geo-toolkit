@props([
    'inputMode',
])

<div {{ $attributes->class(['border-b border-zinc-200 dark:border-zinc-700']) }}>
    <nav class="-mb-px flex space-x-8">
        <button
            wire:click="switchInputMode('form')"
            @class([
                'border-b-2 px-1 py-2 text-sm font-medium whitespace-nowrap',
                'border-blue-500 text-blue-600 dark:text-blue-400' => $inputMode === 'form',
                'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' =>
                    $inputMode !== 'form',
            ])
        >
            <flux:icon.document-text class="mr-2 inline h-4 w-4" />
            Form Input
        </button>
        <button
            wire:click="switchInputMode('json')"
            @class([
                'border-b-2 px-1 py-2 text-sm font-medium whitespace-nowrap',
                'border-blue-500 text-blue-600 dark:text-blue-400' => $inputMode === 'json',
                'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' =>
                    $inputMode !== 'json',
            ])
        >
            <flux:icon.code-bracket class="mr-2 inline h-4 w-4" />
            JSON Input
        </button>
    </nav>
</div>
