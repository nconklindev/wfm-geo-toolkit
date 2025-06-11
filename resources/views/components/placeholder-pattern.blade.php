@props([
    'id' => uniqid('pattern-'),
])

{{--
    Generates an SVG placeholder with a cross-hatch pattern.
    The pattern lines inherit the text color of the element.
    Example: <x-placeholder-pattern class="h-32 w-full text-zinc-400 dark:text-zinc-600" />
--}}
<svg {{ $attributes->merge(['fill' => 'none']) }}>
    <defs>
        <pattern id="{{ $id }}" x="0" y="0" width="8" height="8" patternUnits="userSpaceOnUse">
            {{-- Cross-hatch pattern lines: one diagonal top-left to bottom-right, one diagonal top-right to bottom-left --}}
            <path d="M0 0L8 8M8 0L0 8" stroke-width="0.5" stroke="currentColor"></path>
            {{-- Use stroke="currentColor" --}}
        </pattern>
    </defs>
    {{-- Fill the rectangle with the defined pattern --}}
    <rect stroke="none" fill="url(#{{ $id }})" width="100%" height="100%"></rect>
</svg>
