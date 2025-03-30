@props([
    /**@var\App\Models\KnownPlace*/'knownPlace',
])

<flux:button
    variant="filled"
    icon="pencil-square"
    href="{{ route('known-places.edit', $knownPlace) }}')"
    {{ $attributes->class(['cursor-pointer bg-blue-600! tracking-wider uppercase transition hover:bg-blue-700! dark:bg-blue-600 dark:hover:bg-blue-400!']) }}
>
    Edit
</flux:button>
