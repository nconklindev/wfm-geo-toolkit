<x-layouts.app :title="__('Edit Known Place')">
    <div class="overflow-hidden" x-data="addressSearch">
        <div class="bg-white p-6 shadow-sm sm:rounded-lg dark:bg-zinc-800">
            <div class="mb-6">
                <flux:heading size="xl">{{ __('Edit Known Place') }}</flux:heading>
                <flux:text variant="subtle">
                    {{ __('Use the form below to edit the specified Known Place. You can search for an address or drag the map marker to set the location. Once submitted, the Known Place will be updated.') }}
                </flux:text>
            </div>

            <form method="POST" action="{{ route('known-places.update', $knownPlace) }}" class="space-y-6">
                @csrf
                @method('PATCH')
                @include('partials.known-place-form', ['knownPlace' => $knownPlace])
            </form>
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
