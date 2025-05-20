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
            'bg-teal-100 text-teal-700 dark:bg-teal-900 dark:text-teal-300' => $isActive, // Use isActive prop
            'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' => ! $isActive, // Use isActive prop
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
