<x-layouts.app :title="__($knownPlace->name)">
    <div class="container mx-auto">
        <div class="overflow-hidden bg-white shadow-lg sm:rounded-lg dark:bg-zinc-800">
            <div class="p-6">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $knownPlace->name }}</h1>
                    <div class="flex space-x-2">
                        <x-edit-item-button-link :knownPlace="$knownPlace" />
                        <flux:modal.trigger
                            name="{{ 'delete-' . Str::plural(Str::kebab(class_basename($knownPlace))) . '-' . $knownPlace->id }}"
                        >
                            <flux:button variant="danger" size="sm" icon="trash" class="cursor-pointer">
                                Delete
                            </flux:button>
                        </flux:modal.trigger>
                        {{-- Delete --}}
                        <x-delete-confirmation-modal :model="$knownPlace" :item-to-delete="$knownPlace->name" />
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
                                <flux:separator class="my-2.5" />
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

                            <flux:separator class="my-2.5" />

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

                            {{-- The separator goes in the if block to make sure there isn't one stranded if there is no group --}}
                            {{-- @dd($knownPlace->groups) --}}
                            @if (isset($knownPlace->groups))
                                <flux:separator class="my-2.5" />
                                <div>
                                    <flux:heading level="3" size="lg">Groups</flux:heading>
                                    @foreach ($knownPlace->groups as $group)
                                        <flux:link href="route('groups.show', $knownPlace->group)" class="text-sm">
                                            {{ $group->name }}
                                        </flux:link>
                                    @endforeach
                                </div>
                            @endif

                            <flux:separator class="my-2.5" />

                            <div class="grid grid-cols-2">
                                <div>
                                    <flux:heading level="3" size="lg">Created</flux:heading>
                                    <flux:text>
                                        {{ $knownPlace->created_at->format('F j, Y \a\t g:i a') }}
                                    </flux:text>
                                </div>

                                <div>
                                    <flux:heading level="3" size="lg">Last Updated</flux:heading>
                                    <flux:text>
                                        {{ $knownPlace->updated_at->format('F j, Y \a\t g:i a') }}
                                    </flux:text>
                                </div>
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
                                        <div class="rounded-md bg-zinc-100 p-3 dark:bg-zinc-600">
                                            <div class="flex items-center">
                                                <flux:icon.map-pin class="mr-2 size-5 text-sky-600 dark:text-sky-300" />
                                                <span class="font-medium text-zinc-700 dark:text-zinc-200">
                                                    {{ $node->pivot->path ? $node->path : '' }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <flux:text variant="subtle">No locations assigned to this known place.</flux:text>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <!-- Map & Echo Scripts -->
        @vite(['resources/js/map.js', 'resources/js/echo.js'])
    @endpush
</x-layouts.app>
