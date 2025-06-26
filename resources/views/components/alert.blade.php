@props([
    'status' => 'success',
    'message' => '',
])

@php
    $statusClasses = [
        'success' => 'border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-200',
        'error' => 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200',
        'warning' => 'border-yellow-200 bg-yellow-50 text-yellow-800 dark:border-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200',
        'info' => 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-200',
    ];

    $iconClasses = [
        'success' => 'text-green-600 dark:text-green-400',
        'error' => 'text-red-600 dark:text-red-400',
        'warning' => 'text-yellow-600 dark:text-yellow-400',
        'info' => 'text-blue-600 dark:text-blue-400',
    ];

    $icons = [
        'success' => 'flux:icon.check-circle',
        'error' => 'flux:icon.x-circle',
        'warning' => 'flux:icon.exclamation-triangle',
        'info' => 'flux:icon.information-circle',
    ];

    $mergedClasses = \Illuminate\Support\Arr::toCssClasses(['mb-6 rounded-lg border p-4', $statusClasses[$status] ?? $statusClasses['info']]);

    $mergedIconClasses = \Illuminate\Support\Arr::toCssClasses(['mr-2 h-5 w-5', $iconClasses[$status] ?? $iconClasses['info']]);

    $iconComponent = $icons[$status] ?? $icons['info'];
@endphp

@if ($message)
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => (show = false), 5000)"
        x-show="show"
        x-transition:enter="transition duration-300 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition duration-200 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        {{ $attributes->merge(['class' => $mergedClasses]) }}
        role="alert"
    >
        <div class="flex w-full items-center justify-between">
            <div class="flex items-center">
                @switch($status)
                    @case('success')
                        <flux:icon.check-circle :class="$mergedIconClasses" />

                        @break
                    @case('error')
                        <flux:icon.x-circle :class="$mergedIconClasses" />

                        @break
                    @case('warning')
                        <flux:icon.exclamation-triangle :class="$mergedIconClasses" />

                        @break
                    @default
                        <flux:icon.information-circle :class="$mergedIconClasses" />
                @endswitch
                <span>{{ $message }}</span>
            </div>
            <button
                type="button"
                @click="show = false"
                class="-my-1.5 -mr-1.5 ml-4 inline-flex items-center justify-center rounded-full p-1.5 ring-current hover:bg-black/10 focus:ring-2 focus:outline-none dark:hover:bg-white/10"
                aria-label="Dismiss"
            >
                <flux:icon.x-mark class="h-5 w-5" />
            </button>
        </div>
    </div>
@endif
