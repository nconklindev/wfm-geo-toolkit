<x-layouts.app :title="__('Known Places')">
    <div class="container mx-auto">
        <!-- Header with Actions -->
        <div class="mb-6 flex flex-col items-start justify-between sm:flex-row sm:items-center">
            <div>
                <flux:heading level="1" size="xl">
                    {{ __('My Known Places') }}
                </flux:heading>
                <flux:text class="mt-1.5 text-sm">
                    {{ __('Manage your saved locations and geographical points of interest') }}
                </flux:text>
            </div>

            <div class="mt-4 flex flex-row gap-3 sm:mt-0">
                <flux:button
                    type="button"
                    variant="primary"
                    icon="plus"
                    href="{{ route('known-places.create') }}"
                    class="cursor-pointer text-xs font-semibold tracking-widest uppercase transition-colors duration-150 ease-in-out"
                >
                    {{ __('Add') }}
                </flux:button>

                <div class="flex space-x-2">
                    <flux:button
                        type="button"
                        variant="filled"
                        icon="arrow-up-tray"
                        href="{{ route('known-places.import') }}"
                        class="cursor-pointer text-xs font-semibold tracking-widest uppercase transition-colors duration-150 ease-in-out"
                    >
                        {{ __('Upload') }}
                    </flux:button>

                    <flux:button
                        type="button"
                        variant="filled"
                        icon="arrow-down-tray"
                        href="{{ route('known-places.export') }}"
                        class="cursor-pointer text-xs font-semibold tracking-widest uppercase transition-colors duration-150 ease-in-out"
                    >
                        {{ __('Download') }}
                    </flux:button>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="mb-6 rounded-lg bg-white shadow-sm dark:bg-zinc-800">
            <div class="p-6">
                <form class="flex flex-col gap-4 md:flex-row">
                    <div class="flex-grow items-center">
                        <livewire:search />
                    </div>

                    <div class="sm:w-48">
                        <label for="sort" class="sr-only">Sort</label>
                        <select
                            id="sort"
                            name="sort"
                            class="block w-full rounded-md border border-zinc-300 bg-white py-2 pr-10 pl-3 text-base leading-5 focus:border-accent focus:ring-accent-content focus:outline-none sm:text-sm dark:border-zinc-700 dark:bg-zinc-900"
                        >
                            <option value="name-asc">Name (A-Z)</option>
                            <option value="name-desc">Name (Z-A)</option>
                            <option value="updated-desc">Recently Updated</option>
                            <option value="created-desc">Recently Created</option>
                        </select>
                    </div>

                    <flux:button
                        type="button"
                        variant="primary"
                        class="cursor-pointer text-xs font-semibold tracking-widest uppercase transition-colors duration-150 ease-in-out"
                    >
                        {{ __('Filter') }}
                    </flux:button>
                </form>
            </div>
        </div>

        <!-- Map Overview -->
        <div class="mb-6 overflow-hidden rounded-lg bg-white shadow-sm dark:bg-zinc-800">
            <div class="p-6">
                <h2 class="mb-4 text-lg font-medium text-zinc-900 dark:text-zinc-100">
                    {{ __('Map Overview') }}
                </h2>
                <div
                    id="map"
                    data-places="{{ json_encode($knownPlaces->items()) }}"
                    class="h-64 rounded-lg border border-zinc-200 sm:h-96 dark:border-zinc-700"
                ></div>
            </div>
        </div>

        <!-- Known Places List -->
        <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-zinc-800">
            <div class="p-6">
                <h2 class="mb-4 text-lg font-medium text-zinc-900 dark:text-zinc-100">
                    {{ __('Known Places List') }}
                </h2>

                @if ($knownPlaces->isEmpty())
                    <div class="py-10 text-center">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="mx-auto h-12 w-12 text-zinc-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ __('No places found') }}
                        </h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Add a new known place to get started.') }}
                        </p>
                        <div class="mt-6">
                            <flux:button
                                type="button"
                                variant="primary"
                                icon="plus"
                                href="{{ route('known-places.create') }}"
                                class="cursor-pointer text-xs font-semibold tracking-widest uppercase transition-colors duration-150 ease-in-out"
                            >
                                {{ __('Add Place') }}
                            </flux:button>
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto" x-data="{}">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                    >
                                        {{ __('Name') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                    >
                                        {{ __('Description') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                    >
                                        {{ __('Coordinates') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                    >
                                        {{ __('Radius') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                    >
                                        {{ __('Accuracy') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                    >
                                        {{ __('Active') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                    >
                                        {{ __('Validation') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                    >
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                                @foreach ($knownPlaces as $place)
                                    <tr
                                        class="cursor-pointer text-sm hover:bg-zinc-50 dark:hover:bg-zinc-900"
                                        x-on:click="
                                            $dispatch('fly-to-point', {
                                                lat: {{ $place->latitude }},
                                                lng: {{ $place->longitude }},
                                            })
                                        "
                                        data-latitude="{{ $place->latitude }}"
                                        data-longitude="{{ $place->longitude }}"
                                        data-radius="{{ $place->radius }}"
                                        data-place-id="{{ $place->id }}"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ $place->name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="line-clamp-2 text-zinc-500 dark:text-zinc-400">
                                                {{ $place->description ?? __('No description') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                            {{ $place->latitude }}, {{ $place->longitude }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                            {{ $place->radius }}m
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                            {{ $place->accuracy }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                            @if ($place->is_active)
                                                <flux:icon.check class="h-5 w-5 text-green-500" />
                                            @else
                                                <flux:icon.x-mark class="h-5 w-5 text-red-500" />
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                            @foreach ($place->validation_order as $method)
                                                <flux:badge
                                                    :color="$method === 'gps' ? 'sky' : 'blue'"
                                                    class="inline-flex items-center text-xs"
                                                >
                                                    {{ strtoupper($method) }}
                                                </flux:badge>
                                            @endforeach
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                            <x-table-actions :model="$place" resource-name="known-places" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $knownPlaces->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    @push('scripts')
        <!-- Map & Echo Scripts -->
        @vite(['resources/js/map.js', 'resources/js/echo.js'])
    @endpush
</x-layouts.app>
