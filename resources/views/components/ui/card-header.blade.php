@props(['title' => null])

<div {{ $attributes->merge(['class' => 'flex items-center justify-between']) }}>
    @if ($title)
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $title }}</h3>
    @endif

    {{ $slot }}
</div>
