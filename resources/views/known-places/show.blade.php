<x-layouts.app :title="__($knownPlace->name)">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg dark:bg-zinc-800">
            <div class="p-6">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $knownPlace->name }}</h1>
                    <div class="flex space-x-2">
                        <x-edit-item-button-link :knownPlace="$knownPlace" />
                        <form method="POST" action="{{ route('known-places.destroy', $knownPlace) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            {{-- Delete --}}
                            <x-delete-item-button />
                        </form>
                        {{-- Back --}}
                        <x-back-button-link />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
                    <!-- Left column: Map and details -->
                    <div class="col-span-1 lg:col-span-3">
                        <!-- Map -->
                        <div
                            id="map"
                            class="mb-6 h-96 w-full rounded-lg shadow-md"
                            data-places="{{ json_encode($knownPlace) }}"
                        ></div>

                        <!-- Place Details -->
                        <div class="mb-6 rounded-lg bg-zinc-50 p-6 shadow-md dark:bg-zinc-700">
                            <h2 class="mb-2 text-lg font-semibold">Place Details</h2>

                            @if ($knownPlace->description)
                                <div class="mb-4">
                                    <flux:heading level="3" class="mb-1" size="lg">Description</flux:heading>
                                    <flux:text>
                                        {{ $knownPlace->description }}
                                    </flux:text>
                                </div>
                                <hr class="my-4 border-zinc-200 dark:border-zinc-600" />
                            @endif

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <flux:heading level="3" class="mb-1" size="lg">Latitude</flux:heading>
                                    <flux:text>{{ $knownPlace->latitude }}</flux:text>
                                </div>
                                <div>
                                    <flux:heading level="3" size="lg" class="mb-1">Longitude</flux:heading>
                                    <flux:text>{{ $knownPlace->longitude }}</flux:text>
                                </div>
                            </div>

                            <hr class="my-4 border-zinc-200 dark:border-zinc-600" />

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <flux:heading level="3" size="lg">Radius</flux:heading>
                                    <flux:text>{{ $knownPlace->radius }} meters</flux:text>
                                </div>
                                <div>
                                    <flux:heading level="3" size="lg">Accuracy</flux:heading>
                                    <flux:text>{{ $knownPlace->accuracy }} meters</flux:text>
                                </div>
                            </div>

                            <hr class="my-4 border-zinc-200 dark:border-zinc-600" />

                            <div>
                                <flux:heading level="3" size="lg">Created</flux:heading>
                                <flux:text>
                                    {{ $knownPlace->created_at->format('F j, Y \a\t g:i a') }}
                                </flux:text>
                            </div>

                            <hr class="my-4 border-zinc-200 dark:border-zinc-600" />

                            <div>
                                <flux:heading level="3" size="lg">Last Updated</flux:heading>
                                <flux:text>
                                    {{ $knownPlace->updated_at->format('F j, Y \a\t g:i a') }}
                                </flux:text>
                            </div>
                        </div>
                    </div>

                    <!-- Right column: Validation and WiFi Locations -->
                    <div class="col-span-1 lg:col-span-2">
                        <!-- Validation Methods -->
                        <div class="mb-6 rounded-lg bg-zinc-50 p-6 shadow-md dark:bg-zinc-700">
                            <flux:heading level="2" size="lg" class="mb-2">Validation Methods</flux:heading>

                            @if (is_array($knownPlace->validation_order) && count($knownPlace->validation_order) > 0)
                                <ol class="ml-2 list-inside list-decimal space-y-2">
                                    @foreach ($knownPlace->validation_order as $method)
                                        <li class="flex items-center">
                                            @if ($method == 'gps')
                                                <span
                                                    class="mr-3 inline-flex items-center justify-center rounded-full bg-lime-100 p-2 dark:bg-lime-800"
                                                >
                                                    <flux:icon.map-pin
                                                        class="h-4 w-4 text-lime-600 dark:text-lime-300"
                                                    />
                                                </span>
                                                <span class="text-sm text-zinc-800 dark:text-zinc-200">GPS</span>
                                            @elseif ($method == 'wifi')
                                                <span
                                                    class="mr-3 inline-flex items-center justify-center rounded-full bg-blue-100 p-2 dark:bg-blue-800"
                                                >
                                                    <flux:icon.wifi class="h-4 w-4 text-blue-600 dark:text-blue-300" />
                                                </span>
                                                <span class="text-sm text-zinc-800 dark:text-zinc-200">WiFi</span>
                                            @else
                                                <span
                                                    class="mr-3 inline-flex items-center justify-center rounded-full bg-zinc-100 p-2 dark:bg-zinc-600"
                                                >
                                                    <svg
                                                        class="h-5 w-5 text-zinc-600 dark:text-zinc-300"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                                        ></path>
                                                    </svg>
                                                </span>
                                                <span class="text-zinc-800 dark:text-zinc-200">
                                                    {{ ucfirst($method) }}
                                                </span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ol>
                            @else
                                <flux:text class="italic">No validation methods configured</flux:text>
                            @endif
                        </div>

                        <!-- Business Locations Card -->
                        <div class="mt-6 mb-6 rounded-lg bg-zinc-50 p-6 shadow-md dark:bg-zinc-700">
                            <flux:heading level="2" size="lg" class="mb-2">Locations</flux:heading>

                            @if ($knownPlace->nodes->count() > 0)
                                <div class="space-y-2">
                                    @foreach ($knownPlace->nodes as $node)
                                        @if ($node->isLeaf())
                                            <div class="rounded-md bg-zinc-100 p-3 dark:bg-zinc-600">
                                                <div class="flex items-center">
                                                    <flux:icon.map-pin
                                                        class="mr-2 size-5 text-teal-600 dark:text-teal-300"
                                                    />
                                                    <span class="font-medium text-zinc-700 dark:text-zinc-200">
                                                        {{ $node->path }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <p class="text-zinc-500 italic dark:text-zinc-400">
                                    No business locations assigned to this known place.
                                </p>
                            @endif
                        </div>

                        <!-- WiFi Networks -->
                        <div class="rounded-lg bg-zinc-50 p-6 shadow-md dark:bg-zinc-700">
                            <flux:heading level="2" size="lg" class="mb-2">Associated WiFi Networks</flux:heading>

                            {{-- TODO: Implement Wifi Network validation --}}
                            @if (is_array($knownPlace->wifi_networks) && count($knownPlace->wifi_networks) > 0)
                                <ul class="space-y-2">
                                    @foreach ($knownPlace->wifi_networks as $network)
                                        <li
                                            class="flex items-center rounded-md bg-white p-2 shadow-sm dark:bg-zinc-600"
                                        >
                                            <span
                                                class="mr-3 inline-flex items-center justify-center rounded-full bg-blue-100 p-2 dark:bg-blue-800"
                                            >
                                                <flux:icon.wifi class="h-5 w-5 text-blue-600 dark:text-blue-300" />
                                            </span>
                                            <span class="text-zinc-800 dark:text-zinc-200">{{ $network }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <flux:text variant="subtle" class="mt-2 italic">
                                    No WiFi networks associated with this place
                                </flux:text>
                            @endif

                            <div class="mt-6">
                                <a
                                    href="{{ route('known-places.edit', $knownPlace) }}"
                                    class="flex items-center text-sm text-blue-600 hover:underline dark:text-blue-400"
                                >
                                    <flux:icon.plus class="mr-1 h-4 w-4" />
                                    Add or modify WiFi networks
                                </a>
                            </div>
                        </div>

                        <!-- Actions Card -->
                        {{-- <div class="mt-6 rounded-lg bg-zinc-50 p-6 shadow-md dark:bg-zinc-700"> --}}
                        {{-- <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Quick Actions</h2> --}}

                        {{-- <div class="grid grid-cols-1 gap-3"> --}}
                        {{-- <a --}}
                        {{-- href="#" --}}
                        {{-- class="flex items-center justify-between rounded-md bg-blue-50 p-3 transition hover:bg-blue-100 dark:bg-blue-900 dark:hover:bg-blue-800" --}}
                        {{-- > --}}
                        {{-- <div class="flex items-center"> --}}
                        {{-- <svg --}}
                        {{-- class="mr-3 h-5 w-5 text-blue-600 dark:text-blue-300" --}}
                        {{-- fill="none" --}}
                        {{-- stroke="currentColor" --}}
                        {{-- viewBox="0 0 24 24" --}}
                        {{-- xmlns="http://www.w3.org/2000/svg" --}}
                        {{-- > --}}
                        {{-- <path --}}
                        {{-- stroke-linecap="round" --}}
                        {{-- stroke-linejoin="round" --}}
                        {{-- stroke-width="2" --}}
                        {{-- d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" --}}
                        {{-- ></path> --}}
                        {{-- </svg> --}}
                        {{-- <span class="text-blue-800 dark:text-blue-200">Validate Current Position</span> --}}
                        {{-- </div> --}}
                        {{-- <svg --}}
                        {{-- class="h-4 w-4 text-blue-600 dark:text-blue-300" --}}
                        {{-- fill="none" --}}
                        {{-- stroke="currentColor" --}}
                        {{-- viewBox="0 0 24 24" --}}
                        {{-- xmlns="http://www.w3.org/2000/svg" --}}
                        {{-- > --}}
                        {{-- <path --}}
                        {{-- stroke-linecap="round" --}}
                        {{-- stroke-linejoin="round" --}}
                        {{-- stroke-width="2" --}}
                        {{-- d="M9 5l7 7-7 7" --}}
                        {{-- ></path> --}}
                        {{-- </svg> --}}
                        {{-- </a> --}}

                        {{-- <a --}}
                        {{-- href="#" --}}
                        {{-- class="flex items-center justify-between rounded-md bg-green-50 p-3 transition hover:bg-green-100 dark:bg-green-900 dark:hover:bg-green-800" --}}
                        {{-- > --}}
                        {{-- <div class="flex items-center"> --}}
                        {{-- <svg --}}
                        {{-- class="mr-3 h-5 w-5 text-green-600 dark:text-green-300" --}}
                        {{-- fill="none" --}}
                        {{-- stroke="currentColor" --}}
                        {{-- viewBox="0 0 24 24" --}}
                        {{-- xmlns="http://www.w3.org/2000/svg" --}}
                        {{-- > --}}
                        {{-- <path --}}
                        {{-- stroke-linecap="round" --}}
                        {{-- stroke-linejoin="round" --}}
                        {{-- stroke-width="2" --}}
                        {{-- d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" --}}
                        {{-- ></path> --}}
                        {{-- </svg> --}}
                        {{-- <span class="text-green-800 dark:text-green-200">Schedule Events</span> --}}
                        {{-- </div> --}}
                        {{-- <svg --}}
                        {{-- class="h-4 w-4 text-green-600 dark:text-green-300" --}}
                        {{-- fill="none" --}}
                        {{-- stroke="currentColor" --}}
                        {{-- viewBox="0 0 24 24" --}}
                        {{-- xmlns="http://www.w3.org/2000/svg" --}}
                        {{-- > --}}
                        {{-- <path --}}
                        {{-- stroke-linecap="round" --}}
                        {{-- stroke-linejoin="round" --}}
                        {{-- stroke-width="2" --}}
                        {{-- d="M9 5l7 7-7 7" --}}
                        {{-- ></path> --}}
                        {{-- </svg> --}}
                        {{-- </a> --}}

                        {{-- <a --}}
                        {{-- href="#" --}}
                        {{-- class="flex items-center justify-between rounded-md bg-purple-50 p-3 transition hover:bg-purple-100 dark:bg-purple-900 dark:hover:bg-purple-800" --}}
                        {{-- > --}}
                        {{-- <div class="flex items-center"> --}}
                        {{-- <svg --}}
                        {{-- class="mr-3 h-5 w-5 text-purple-600 dark:text-purple-300" --}}
                        {{-- fill="none" --}}
                        {{-- stroke="currentColor" --}}
                        {{-- viewBox="0 0 24 24" --}}
                        {{-- xmlns="http://www.w3.org/2000/svg" --}}
                        {{-- > --}}
                        {{-- <path --}}
                        {{-- stroke-linecap="round" --}}
                        {{-- stroke-linejoin="round" --}}
                        {{-- stroke-width="2" --}}
                        {{-- d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" --}}
                        {{-- ></path> --}}
                        {{-- </svg> --}}
                        {{-- <span class="text-purple-800 dark:text-purple-200">View Analytics</span> --}}
                        {{-- </div> --}}
                        {{-- <svg --}}
                        {{-- class="h-4 w-4 text-purple-600 dark:text-purple-300" --}}
                        {{-- fill="none" --}}
                        {{-- stroke="currentColor" --}}
                        {{-- viewBox="0 0 24 24" --}}
                        {{-- xmlns="http://www.w3.org/2000/svg" --}}
                        {{-- > --}}
                        {{-- <path --}}
                        {{-- stroke-linecap="round" --}}
                        {{-- stroke-linejoin="round" --}}
                        {{-- stroke-width="2" --}}
                        {{-- d="M9 5l7 7-7 7" --}}
                        {{-- ></path> --}}
                        {{-- </svg> --}}
                        {{-- </a> --}}
                        {{-- </div> --}}
                        {{-- </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
