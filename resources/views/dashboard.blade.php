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
                class="flex h-full flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Known Places</h2>
                    {{-- Count Badge --}}
                    <flux:badge variant="solid" color="teal" class="inline-flex min-w-[2rem] justify-center">
                        {{ $user->known_places_count }}
                    </flux:badge>
                </div>
                <div class="mt-4 flex flex-grow flex-col space-y-2">
                    @forelse ($user->knownPlaces->take(5) as $knownPlace)
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
                        <flux:text variant="subtle">
                            No known places yet.
                            <flux:link href="{{ route('known-places.create') }}">Create one</flux:link>
                        </flux:text>
                    @endforelse
                    <div class="mt-auto self-end pt-2">
                        <flux:link href="{{ route('known-places.index') }}" class="text-sm" variant="ghost">
                            View all known places →
                        </flux:link>
                    </div>
                </div>
            </div>

            <!-- Known IPs Summary Card -->
            <div
                class="flex h-full flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Known IP Addresses</h2>
                    {{-- Count Badge --}}
                    <flux:badge variant="solid" color="teal" class="inline-flex min-w-[2rem] justify-center">
                        {{ $user->known_ip_addresses_count }}
                    </flux:badge>
                </div>
                <div class="mt-4 flex flex-grow flex-col space-y-2">
                    @forelse ($user->knownIpAddresses->take(5) as $ipAddress)
                        <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-2 dark:bg-zinc-700">
                            <span class="font-medium">{{ $ipAddress->name }}</span>
                        </div>
                    @empty
                        <flux:text variant="subtle">
                            No known places yet.
                            <flux:link href="{{ route('known-places.create') }}">Create one</flux:link>
                        </flux:text>
                    @endforelse
                    <div class="mt-auto self-end pt-2">
                        <flux:link href="{{ route('known-ip-addresses.index') }}" class="text-sm" variant="ghost">
                            View all known IP addresses →
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
                                href="{{ route('locations.show', $leafNode) }}"
                                class="mr-2 text-sm"
                                variant="ghost"
                            >
                                View
                            </flux:link>
                        </div>
                    @empty
                        <flux:text variant="subtle">No locations yet.</flux:text>
                    @endforelse
                    <div class="mt-auto self-end pt-2">
                        <flux:link href="{{ route('locations.index') }}" class="text-sm" variant="ghost">
                            View all locations →
                        </flux:link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Overview -->
        <div
            class="relative flex min-h-[500px] flex-1 flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
        >
            <flux:heading level="2" size="lg" class="mb-2 font-semibold text-zinc-900 dark:text-white">
                Geographic Overview
            </flux:heading>
            <div id="map" class="flex w-full flex-1 flex-grow rounded-lg" data-places="{{ $user->knownPlaces }}"></div>
        </div>
    </div>
    @push('scripts')
        <!-- Map & Echo Scripts -->
        @vite(['resources/js/map.js', 'resources/js/echo.js'])
    @endpush
</x-layouts.app>
