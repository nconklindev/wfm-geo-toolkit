<x-layouts.app>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg dark:bg-zinc-800">
                <div class="p-6">
                    <div class="mb-6 flex items-center justify-between">
                        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $knownPlace->name }}</h1>
                        <div class="flex space-x-2">
                            <x-edit-item-button-link :knownPlace="$knownPlace" />
                            <form
                                method="POST"
                                action="{{ route('known-places.destroy', $knownPlace) }}"
                                class="inline"
                            >
                                @csrf
                                @method('DELETE')
                                <x-delete-item-button />
                            </form>
                            <a
                                href="{{ route('known-places.index') }}"
                                class="inline-flex items-center rounded-md border border-transparent bg-zinc-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition hover:bg-zinc-700 focus:border-zinc-900 focus:ring focus:ring-zinc-300 focus:outline-none active:bg-zinc-900 disabled:opacity-25"
                            >
                                <svg
                                    class="mr-2 h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18"
                                    ></path>
                                </svg>
                                Back
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
                        <!-- Left column: Map and details -->
                        <div class="col-span-1 lg:col-span-3">
                            <!-- Interactive Map -->
                            {{-- TODO: This isn't working currently and does not plot the place --}}
                            <div
                                id="map"
                                class="mb-6 h-96 w-full rounded-lg shadow-md"
                                data-places="{{ json_encode($knownPlace) }}"
                            ></div>

                            <!-- Place Details -->
                            <div class="mb-6 rounded-lg bg-zinc-50 p-6 shadow-md dark:bg-zinc-700">
                                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Place Details</h2>

                                @if ($knownPlace->description)
                                    <div class="mb-4">
                                        <p class="mb-1 text-sm font-semibold text-zinc-600 dark:text-zinc-300">
                                            Description
                                        </p>
                                        <p class="text-zinc-800 dark:text-zinc-200">{{ $knownPlace->description }}</p>
                                    </div>
                                    <hr class="my-4 border-zinc-200 dark:border-zinc-600" />
                                @endif

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="mb-1 text-sm font-semibold text-zinc-600 dark:text-zinc-300">
                                            Latitude
                                        </p>
                                        <p class="text-zinc-800 dark:text-zinc-200">{{ $knownPlace->latitude }}</p>
                                    </div>
                                    <div>
                                        <p class="mb-1 text-sm font-semibold text-zinc-600 dark:text-zinc-300">
                                            Longitude
                                        </p>
                                        <p class="text-zinc-800 dark:text-zinc-200">{{ $knownPlace->longitude }}</p>
                                    </div>
                                </div>

                                <hr class="my-4 border-zinc-200 dark:border-zinc-600" />

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="mb-1 text-sm font-semibold text-zinc-600 dark:text-zinc-300">
                                            Radius
                                        </p>
                                        <p class="text-zinc-800 dark:text-zinc-200">
                                            {{ $knownPlace->radius }} meters
                                        </p>
                                    </div>
                                    <div>
                                        <p class="mb-1 text-sm font-semibold text-zinc-600 dark:text-zinc-300">
                                            Accuracy
                                        </p>
                                        <p class="text-zinc-800 dark:text-zinc-200">
                                            {{ $knownPlace->accuracy }} meters
                                        </p>
                                    </div>
                                </div>

                                <hr class="my-4 border-zinc-200 dark:border-zinc-600" />

                                <div>
                                    <p class="mb-1 text-sm font-semibold text-zinc-600 dark:text-zinc-300">Created</p>
                                    <p class="text-zinc-800 dark:text-zinc-200">
                                        {{ $knownPlace->created_at->format('F j, Y \a\t g:i a') }}
                                    </p>
                                </div>

                                <hr class="my-4 border-zinc-200 dark:border-zinc-600" />

                                <div>
                                    <p class="mb-1 text-sm font-semibold text-zinc-600 dark:text-zinc-300">
                                        Last Updated
                                    </p>
                                    <p class="text-zinc-800 dark:text-zinc-200">
                                        {{ $knownPlace->updated_at->format('F j, Y \a\t g:i a') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Right column: Validation and WiFi Locations -->
                        <div class="col-span-1 lg:col-span-2">
                            <!-- Validation Methods -->
                            <div class="mb-6 rounded-lg bg-zinc-50 p-6 shadow-md dark:bg-zinc-700">
                                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
                                    Validation Methods
                                </h2>

                                @if (is_array($knownPlace->validation_order) && count($knownPlace->validation_order) > 0)
                                    <ol class="ml-2 list-inside list-decimal space-y-2">
                                        @foreach ($knownPlace->validation_order as $method)
                                            <li class="flex items-center">
                                                @if ($method == 'gps')
                                                    <span
                                                        class="mr-3 inline-flex items-center justify-center rounded-full bg-green-100 p-2 dark:bg-green-800"
                                                    >
                                                        <svg
                                                            class="h-5 w-5 text-green-600 dark:text-green-300"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                                            ></path>
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                                            ></path>
                                                        </svg>
                                                    </span>
                                                    <span class="text-zinc-800 dark:text-zinc-200">GPS Location</span>
                                                @elseif ($method == 'wifi')
                                                    <span
                                                        class="mr-3 inline-flex items-center justify-center rounded-full bg-blue-100 p-2 dark:bg-blue-800"
                                                    >
                                                        <svg
                                                            class="h-5 w-5 text-blue-600 dark:text-blue-300"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"
                                                            ></path>
                                                        </svg>
                                                    </span>
                                                    <span class="text-zinc-800 dark:text-zinc-200">WiFi Networks</span>
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
                                    <p class="text-zinc-500 italic dark:text-zinc-400">
                                        No validation methods configured
                                    </p>
                                @endif
                            </div>

                            <!-- WiFi Locations -->
                            <div class="rounded-lg bg-zinc-50 p-6 shadow-md dark:bg-zinc-700">
                                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
                                    Associated WiFi Networks
                                </h2>

                                @if (is_array($knownPlace->locations) && count($knownPlace->locations) > 0)
                                    <ul class="space-y-2">
                                        @foreach ($knownPlace->locations as $location)
                                            <li
                                                class="flex items-center rounded-md bg-white p-2 shadow-sm dark:bg-zinc-600"
                                            >
                                                <span
                                                    class="mr-3 inline-flex items-center justify-center rounded-full bg-blue-100 p-2 dark:bg-blue-800"
                                                >
                                                    <svg
                                                        class="h-5 w-5 text-blue-600 dark:text-blue-300"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"
                                                        ></path>
                                                    </svg>
                                                </span>
                                                <span class="text-zinc-800 dark:text-zinc-200">{{ $location }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-zinc-500 italic dark:text-zinc-400">
                                        No WiFi networks associated with this place
                                    </p>
                                @endif

                                <div class="mt-6">
                                    <a
                                        href="{{ route('known-places.edit', $knownPlace) }}"
                                        class="flex items-center text-sm text-blue-600 hover:underline dark:text-blue-400"
                                    >
                                        <svg
                                            class="mr-1 h-4 w-4"
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
                                        Add or modify WiFi networks
                                    </a>
                                </div>
                            </div>

                            <!-- Actions Card -->
                            <div class="mt-6 rounded-lg bg-zinc-50 p-6 shadow-md dark:bg-zinc-700">
                                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Quick Actions</h2>

                                <div class="grid grid-cols-1 gap-3">
                                    <a
                                        href="#"
                                        class="flex items-center justify-between rounded-md bg-blue-50 p-3 transition hover:bg-blue-100 dark:bg-blue-900 dark:hover:bg-blue-800"
                                    >
                                        <div class="flex items-center">
                                            <svg
                                                class="mr-3 h-5 w-5 text-blue-600 dark:text-blue-300"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                                ></path>
                                            </svg>
                                            <span class="text-blue-800 dark:text-blue-200">
                                                Validate Current Position
                                            </span>
                                        </div>
                                        <svg
                                            class="h-4 w-4 text-blue-600 dark:text-blue-300"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 5l7 7-7 7"
                                            ></path>
                                        </svg>
                                    </a>

                                    <a
                                        href="#"
                                        class="flex items-center justify-between rounded-md bg-green-50 p-3 transition hover:bg-green-100 dark:bg-green-900 dark:hover:bg-green-800"
                                    >
                                        <div class="flex items-center">
                                            <svg
                                                class="mr-3 h-5 w-5 text-green-600 dark:text-green-300"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                ></path>
                                            </svg>
                                            <span class="text-green-800 dark:text-green-200">Schedule Events</span>
                                        </div>
                                        <svg
                                            class="h-4 w-4 text-green-600 dark:text-green-300"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 5l7 7-7 7"
                                            ></path>
                                        </svg>
                                    </a>

                                    <a
                                        href="#"
                                        class="flex items-center justify-between rounded-md bg-purple-50 p-3 transition hover:bg-purple-100 dark:bg-purple-900 dark:hover:bg-purple-800"
                                    >
                                        <div class="flex items-center">
                                            <svg
                                                class="mr-3 h-5 w-5 text-purple-600 dark:text-purple-300"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                ></path>
                                            </svg>
                                            <span class="text-purple-800 dark:text-purple-200">View Analytics</span>
                                        </div>
                                        <svg
                                            class="h-4 w-4 text-purple-600 dark:text-purple-300"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 5l7 7-7 7"
                                            ></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
