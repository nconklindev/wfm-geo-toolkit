@php
    use App\Models\Location;
@endphp

@php
    use App\Models\KnownPlace;
@endphp

<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <!-- Known Places Summary Card -->
            <div
                class="overflow-hidden rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Known Places</h2>
                    {{-- Count Badge --}}
                    <flux:badge variant="solid" color="teal" class="inline-flex min-w-[2rem] justify-center">
                        {{ $user->known_places_count }}
                    </flux:badge>
                </div>
                <div class="mt-4 space-y-2">
                    @forelse ($user->knownPlaces->take(3) as $knownPlace)
                        <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-2 dark:bg-zinc-700">
                            <span class="font-medium">{{ $knownPlace->name }}</span>
                            <flux:link
                                href="{{ route('known-places.show', $knownPlace) }}"
                                class="mr-2 text-sm"
                                variant="ghost"
                            >
                                View
                            </flux:link>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">No known places yet</p>
                    @endforelse
                    <div class="pt-2">
                        <flux:link href="{{ route('known-places.index') }}" class="text-sm" variant="ghost">
                            View all known places →
                        </flux:link>
                    </div>
                </div>
            </div>

            <!-- Locations Summary Card -->
            <div
                class="flex h-full flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Locations</h2>
                    <flux:badge variant="solid" color="teal" class="inline-flex min-w-[2rem] justify-center">
                        {{ $leafNodes->count() }}
                    </flux:badge>
                </div>
                <div class="mt-4 flex flex-grow flex-col space-y-2">
                    @forelse ($leafNodes as $leafNode)
                        <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-2 dark:bg-zinc-700">
                            <span class="font-medium">{{ $leafNode->path }}</span>
                            <flux:link
                                href="{{ route('business-structure.locations.show', $leafNode) }}"
                                class="mr-2 text-sm"
                                variant="ghost"
                            >
                                View
                            </flux:link>
                        </div>
                    @empty
                        <flux:text variant="subtle">No locations yet</flux:text>
                    @endforelse
                    <div class="mt-auto pt-2">
                        <flux:link
                            href="{{ route('business-structure.locations.index') }}"
                            class="text-sm"
                            variant="ghost"
                        >
                            View all locations →
                        </flux:link>
                    </div>
                </div>
            </div>

            <!-- Types Summary Card -->
            <div
                class="flex h-full flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Business Structure Types</h2>
                    <flux:badge variant="solid" color="teal" class="inline-flex min-w-[2rem] justify-center">
                        <!-- Count of unique types in the system -->
                        {{ $user->types_count }}
                    </flux:badge>
                </div>
                <div class="mt-4 flex flex-grow flex-col space-y-2">
                    @forelse ($user->types as $type)
                        <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-2 dark:bg-zinc-700">
                            <span class="font-medium">{{ $type->name }}</span>
                            <flux:link
                                href="{{ route('business-structure.types.edit', $type) }}"
                                class="mr-2 text-sm"
                                variant="ghost"
                            >
                                Edit
                            </flux:link>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">No place types yet</p>
                    @endforelse
                    <div class="mt-auto pt-2">
                        <flux:link
                            href="{{ route('business-structure.types.index') }}"
                            class="text-sm"
                            variant="ghost"
                        >
                            View all types →
                        </flux:link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Overview -->
        <div
            class="relative h-full flex-1 overflow-hidden rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
        >
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Geographic Overview</h2>
            <div id="map" class="h-[calc(100%-2rem)] w-full rounded-lg" data-places="{{ $user->knownPlaces }}"></div>
        </div>
    </div>
</x-layouts.app>
