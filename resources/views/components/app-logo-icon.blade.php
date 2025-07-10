@props([])

<svg
    {{ $attributes->merge(['class' => 'size-6']) }}
    viewBox="0 0 24 24"
    xmlns="http://www.w3.org/2000/svg"
>
    <rect
        x="4"
        y="8"
        width="16"
        height="10"
        rx="2"
        stroke="currentColor"
        stroke-width="2"
        fill="none"
        class="text-teal-600 dark:text-teal-500"
    />
    <path
        d="M8 8V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
        stroke="currentColor"
        stroke-width="2"
        fill="none"
        class="text-teal-600 dark:text-teal-500"
    />
    <path
        d="M12 12v4"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        class="text-teal-600 dark:text-teal-500"
    />
    <path
        d="M10 14h4"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        class="text-teal-600 dark:text-teal-500"
    />
</svg>
