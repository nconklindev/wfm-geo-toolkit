<flux:button
    href="{{ url()->previous() }}"
    icon="arrow-left"
    size="sm"
    {{ $attributes->class('font-medium transition') }}
>
    Back
</flux:button>
