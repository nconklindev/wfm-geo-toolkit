@props([
    /**@var\App\Models\KnownPlace*/'knownPlace',
])

<flux:button
    variant="primary"
    size="sm"
    icon="pencil-square"
    href="{{ route('known-places.edit', $knownPlace) }}"
    {{ $attributes->class(['cursor-pointer bg-blue-600! transition hover:bg-blue-700! dark:bg-blue-600 dark:hover:bg-blue-500!']) }}
>
    Edit
</flux:button>
