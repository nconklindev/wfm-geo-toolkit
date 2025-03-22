<x-layouts.app :title="__('Edit Known Place')">
    <div class="overflow-hidden " x-data="addressSearch">
        <div class="p-6 bg-white shadow-sm dark:bg-zinc-800 sm:rounded-lg">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Create Known Place') }}</h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Use the form below to create new Known Places. You can search for an address or drag the map marker to set the location. Once submitted, the Known Place will be saved.') }}
                </p>
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
</x-layouts.app>
