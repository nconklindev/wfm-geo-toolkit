<x-layouts.app :title="$group->name">
    <div class="container mx-auto">
        <div class="overflow-hidden bg-white shadow-lg sm:rounded-lg dark:bg-zinc-800">
            <div class="p-6">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $group->name }}</h1>
                    <div class="flex space-x-2">
                        <flux:button
                            variant="primary"
                            size="sm"
                            icon="pencil"
                            href="{{ route('groups.edit', $group) }}"
                        >
                            Edit
                        </flux:button>
                        <flux:modal.trigger name="{{ 'delete-groups-' . $group->id }}">
                            <flux:button variant="danger" size="sm" icon="trash" class="cursor-pointer">
                                Delete
                            </flux:button>
                        </flux:modal.trigger>
                        {{-- Delete Confirmation Modal --}}
                        <flux:modal name="{{ 'delete-groups-' . $group->id }}" class="md:w-96">
                            <div class="p-6">
                                <div class="flex">
                                    <flux:icon.exclamation-triangle class="mr-4 size-8 text-red-500" />
                                    <div>
                                        <flux:heading size="lg">Delete Group</flux:heading>
                                        <flux:text class="mt-2">
                                            Are you sure you want to delete "{{ $group->name }}"? This action cannot be
                                            undone.
                                        </flux:text>
                                    </div>
                                </div>
                                <div class="mt-6 flex justify-end space-x-2">
                                    <flux:modal.close>
                                        <flux:button variant="ghost">Cancel</flux:button>
                                    </flux:modal.close>
                                    <form method="POST" action="{{ route('groups.destroy', $group) }}">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button type="submit" variant="danger">Delete</flux:button>
                                    </form>
                                </div>
                            </div>
                        </flux:modal>
                        {{-- Back Button --}}
                        <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ back() }}">Back</flux:button>
                    </div>
                </div>

                {{-- Map Section --}}
                @if ($group->knownPlaces->count() > 0)
                    <div class="mb-6 rounded-lg bg-zinc-50 p-6 shadow-md dark:bg-zinc-700">
                        <flux:heading level="2" size="lg" class="mb-4">Group Locations Map</flux:heading>
                        <div class="h-96 w-full overflow-hidden rounded-lg">
                            <div
                                id="map"
                                class="h-full w-full"
                                data-places="{{
                                    json_encode(
                                        $group->knownPlaces->map(function ($place) {
                                            return [
                                                'id' => $place->id,
                                                'name' => $place->name,
                                                'description' => $place->description,
                                                'latitude' => (float) $place->latitude,
                                                'longitude' => (float) $place->longitude,
                                                'radius' => (int) $place->radius,
                                                'color' => $place->color ?? '#4f46e5',
                                            ];
                                        }),
                                    )
                                }}"
                            ></div>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Left column: Group details -->
                    <div class="col-span-1 lg:col-span-2">
                        <!-- Group Details -->
                        <div class="mb-6 rounded-lg bg-zinc-50 p-6 shadow-md dark:bg-zinc-700">
                            <flux:heading level="2" size="lg" class="mb-4">Group Information</flux:heading>

                            <div class="space-y-4">
                                <div>
                                    <flux:heading level="3" size="lg" class="mb-1">Name</flux:heading>
                                    <flux:text>{{ $group->name }}</flux:text>
                                </div>

                                @if ($group->description)
                                    <flux:separator class="my-2.5" />
                                    <div>
                                        <flux:heading level="3" size="lg" class="mb-1">Description</flux:heading>
                                        <flux:text>{{ $group->description }}</flux:text>
                                    </div>
                                @endif

                                <flux:separator class="my-2.5" />

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <flux:heading level="3" size="lg" class="mb-1">Created</flux:heading>
                                        <flux:text>
                                            {{ $group->created_at->format('F j, Y \a\t g:i a') }}
                                        </flux:text>
                                    </div>
                                    <div>
                                        <flux:heading level="3" size="lg" class="mb-1">Last Updated</flux:heading>
                                        <flux:text>
                                            {{ $group->updated_at->format('F j, Y \a\t g:i a') }}
                                        </flux:text>
                                    </div>
                                </div>

                                @if ($group->parent)
                                    <flux:separator class="my-2.5" />
                                    <div>
                                        <flux:heading level="3" size="lg" class="mb-1">Parent Group</flux:heading>
                                        <flux:link
                                            href="{{ route('groups.show', $group->parent) }}"
                                            class="inline-flex items-center"
                                        >
                                            <flux:icon.folder class="mr-2 size-4" />
                                            {{ $group->parent->name }}
                                        </flux:link>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right column: Associated items and hierarchy -->
                    <div class="col-span-1">
                        <!-- Known Places -->
                        <div class="mb-6 rounded-lg bg-zinc-50 p-6 shadow-md dark:bg-zinc-700">
                            <div class="mb-4 flex items-center justify-between">
                                <flux:heading level="2" size="lg">Known Places</flux:heading>
                                <flux:badge
                                    variant="solid"
                                    color="teal"
                                    class="inline-flex min-w-[2rem] justify-center"
                                >
                                    {{ $group->knownPlaces->count() }}
                                </flux:badge>
                            </div>

                            @if ($group->knownPlaces->count() > 0)
                                <div class="space-y-2">
                                    @foreach ($group->knownPlaces as $knownPlace)
                                        <div class="rounded-md bg-zinc-100 p-3 dark:bg-zinc-600">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <flux:icon.map-pin
                                                        class="mr-2 size-4 text-teal-600 dark:text-teal-300"
                                                    />
                                                    <span class="font-medium text-zinc-700 dark:text-zinc-200">
                                                        {{ $knownPlace->name }}
                                                    </span>
                                                </div>
                                                <flux:link
                                                    href="{{ route('known-places.show', $knownPlace) }}"
                                                    class="text-sm"
                                                    variant="ghost"
                                                >
                                                    View
                                                </flux:link>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <flux:text variant="subtle">No known places in this group yet.</flux:text>
                            @endif
                        </div>

                        <!-- Quick Actions -->
                        <div class="rounded-lg bg-zinc-50 p-6 shadow-md dark:bg-zinc-700">
                            <flux:heading level="2" size="lg" class="mb-4">Quick Actions</flux:heading>
                            <div class="space-y-2">
                                <flux:button
                                    href="{{ route('known-places.create', ['group' => $group->id]) }}"
                                    variant="primary"
                                    size="sm"
                                    icon="plus"
                                    class="w-full justify-start"
                                >
                                    Add Known Place
                                </flux:button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @push('scripts')
            <!-- Map & Echo Scripts -->
            @vite(['resources/js/map.js', 'resources/js/echo.js'])
        @endpush
    @endpush
</x-layouts.app>
