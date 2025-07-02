@props([
    'title',
    'name' => null,
])

<div>
    <!-- Group Header -->
    <div
        {{ $attributes->class(['bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-500 uppercase dark:bg-zinc-900/50 dark:text-zinc-400']) }}
    >
        @if ($name)
            <flux:icon :$name variant="solid" class="mr-2 inline size-4" />
        @endif

        {{ $title }}
    </div>

    {{ $slot }}
</div>
