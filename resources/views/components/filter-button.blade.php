@props([
    'query' => 'filter',
    'filter' => 'all',
    'icon' => null,
    'isActive' => false,
])

<a
    href="{{ route('notifications', array_merge(request()->query() ?? [], [$query => $filter])) }}"
    {{
        $attributes->class([
            'bg-sky-100 text-zinc-700 dark:bg-sky-900 dark:text-zinc-300' => $isActive, // Use isActive prop
            'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700' => ! $isActive, // Use isActive prop
            'flex items-center gap-2 rounded-md p-2',
        ])
    }}
    wire:navigate
>
    <div>
        {{ $icon }}
    </div>

    <span>{{ $slot }}</span>
</a>
