<x-layouts.app :title="__('Create Known Place')">
    <div class="overflow-hidden" x-data="addressSearch">
        <div class="bg-white p-6 shadow-sm sm:rounded-lg dark:bg-zinc-800">
            <div class="mb-6">
                <flux:heading size="xl">{{ __('Create Known Place') }}</flux:heading>
                <flux:text variant="subtle">
                    {{ __('Use the form below to create new Known Places. You can search for an address or drag the map marker to set the location. Once submitted, the Known Place will be saved.') }}
                </flux:text>
                <flux:callout color="blue" class="mt-6">
                    <flux:callout.heading icon="information-circle">Note</flux:callout.heading>
                    <flux:callout.text>
                        To make it easier to see the
                        <flux:callout.link href="{{ route('known-places.index') }}">known places</flux:callout.link>
                        you create, during your active session, they will be displayed in the table below. In this
                        table, you can also edit and delete the places you have created.
                    </flux:callout.text>
                </flux:callout>
            </div>

            <form method="POST" action="{{ route('known-places.store') }}" class="space-y-6">
                @csrf
                @include('partials.known-place-form', ['knownPlace' => null, 'groups' => $groups ?? []])
            </form>
        </div>
        <!-- Known Places Table -->
        <flux:separator class="my-8" />
        <div>
            <flux:heading size="lg" class="mb-4">{{ __('Your Known Places') }}</flux:heading>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-700">
                        <tr>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted uppercase dark:text-zinc-400"
                            >
                                Name
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted uppercase dark:text-zinc-400"
                            >
                                Description
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted uppercase dark:text-zinc-400"
                            >
                                Locations
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted uppercase dark:text-zinc-400"
                            >
                                Coordinates
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted uppercase dark:text-zinc-400"
                            >
                                Radius
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted uppercase dark:text-zinc-400"
                            >
                                Accuracy
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted uppercase dark:text-zinc-400"
                            >
                                Active
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted uppercase dark:text-zinc-400"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                        @forelse ($sessionKnownPlaces as $knownPlace)
                            <tr>
                                <td
                                    class="px-6 py-4 text-sm font-medium whitespace-nowrap text-zinc-900 dark:text-zinc-100"
                                >
                                    {{ $knownPlace->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-muted dark:text-zinc-400">
                                    {{ Str::limit($knownPlace->description, 30) ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-muted dark:text-zinc-400">
                                    {{-- Check if there are any locations to display (using the new property) --}}
                                    @if (! empty($knownPlace->display_locations))
                                        <div class="flex flex-col gap-1">
                                            {{-- Loop through the limited locations provided by the controller --}}
                                            @foreach ($knownPlace->display_locations as $locationPathString)
                                                <div>{{ implode('/', $locationPathString) }}</div>
                                            @endforeach

                                            {{-- If the controller indicated remaining locations, show the message --}}
                                            @if ($knownPlace->remaining_locations_count > 0)
                                                <div class="text-xs text-zinc-500 italic dark:text-zinc-500">
                                                    ... and {{ $knownPlace->remaining_locations_count }} more
                                                    location(s).
                                                    {{-- Link to the detail page --}}
                                                    <a
                                                        href="{{ route('known-places.show', $knownPlace) }}"
                                                        class="hover:text-primary-500 dark:hover:text-primary-400 underline"
                                                    >
                                                        View All
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        N/A
                                        {{-- Display N/A if there were no locations initially or after filtering --}}
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-muted dark:text-zinc-400">
                                    {{ number_format($knownPlace->latitude, 6) }}
                                    , {{ number_format($knownPlace->longitude, 6) }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-muted dark:text-zinc-400">
                                    {{ $knownPlace->radius }}m
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-muted dark:text-zinc-400">
                                    {{ $knownPlace->accuracy }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                    @foreach ($knownPlace->validation_order as $method)
                                        <flux:badge
                                            :color="$method === 'gps' ? 'lime' : 'blue'"
                                            class="inline-flex items-center text-xs"
                                        >
                                            {{ strtoupper($method) }}
                                        </flux:badge>
                                    @endforeach
                                </td>
                                <!-- Table Actions -->
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                    <x-table-actions :model="$knownPlace" resource-name="known-places" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-muted">
                                    No known places found. Create your first one!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-6">
                    {{ $sessionKnownPlaces->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Custom styling to match Flux inputs until checkbox is fixed --}}
    <style>
        #validation_order {
            background-color: color-mix(in oklab, var(--color-white) 10%, transparent) !important;
        }

        /* Making sure that the address search field is the proper color regardless of the appearance settings */
        .dark #address_search {
            background-color: color-mix(in srgb, #27272a 100%, var(--color-white) 10%) !important;
        }
    </style>
    @push('scripts')
        <!-- Map & Echo Scripts -->
        @vite(['resources/js/map.js', 'resources/js/echo.js'])
    @endpush
</x-layouts.app>
