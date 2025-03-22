<x-layouts.app :title="__('Create Known Place')">

    <div class="overflow-hidden " x-data="addressSearch">
        <div class="p-6 bg-white shadow-sm dark:bg-zinc-800 sm:rounded-lg">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Create Known Place') }}</h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Use the form below to create new Known Places. You can search for an address or drag the map marker to set the location. Once submitted, the Known Place will be saved.') }}
                </p>
            </div>

            <form method="POST" action="{{ route('known-places.store') }}" class="space-y-6">
                @csrf
                @include('partials.known-place-form', ['knownPlace' => null])
            </form>
        </div>
        <!-- Known Places Table -->
        <flux:separator class="my-8"/>
        <div>
            <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-200 mb-4">{{ __('Your Known Places') }}</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-700">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-muted dark:text-zinc-400 uppercase tracking-wider">
                            Name
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-muted dark:text-zinc-400 uppercase tracking-wider">
                            Description
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-muted dark:text-zinc-400 uppercase tracking-wider">
                            Location
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-muted dark:text-zinc-400 uppercase tracking-wider">
                            Coordinates
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-muted dark:text-zinc-400 uppercase tracking-wider">
                            Radius
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-muted dark:text-zinc-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($knownPlaces as $knownPlace)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $knownPlace->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-muted dark:text-zinc-400">
                                {{ Str::limit($knownPlace->description, 30) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-muted dark:text-zinc-400">
                                {{ $knownPlace->location_path ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-muted dark:text-zinc-400">
                                {{ number_format($knownPlace->latitude, 6) }}
                                , {{ number_format($knownPlace->longitude, 6) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-muted dark:text-zinc-400">
                                {{ $knownPlace->radius }}m
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('known-places.edit', $knownPlace) }}"
                                   class="text-info hover:text-info-hover mr-3">Edit</a>
                                <form id="delete-place-form" class="inline" method="POST"
                                      action="{{ route('known-places.destroy', $knownPlace) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            form="delete-place-form"
                                            class="text-danger hover:text-danger-hover cursor-pointer"
                                            onclick="return confirm('Are you sure you want to delete this place?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-sm text-center text-muted dark:text-zinc-400">
                                No known places found. Create your first one above!
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
                <div class="mt-6">
                    {{ $knownPlaces->links('pagination::tailwind') }}
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
</x-layouts.app>
