<x-layouts.app :title="__('WFM Import')">
    <div class="flex flex-col space-y-8 overflow-hidden">
        <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-zinc-800">
            <div class="mb-6">
                <flux:heading size="xl">{{ __('WFM Import') }}</flux:heading>
                <flux:text class="mt-1.5 text-sm">
                    {{ __('Use the form below to create a new Known Place entry directly in WFM.') }}
                </flux:text>
            </div>
            <flux:callout variant="warning" icon="exclamation-circle" class="mb-6">
                <flux:callout.heading>
                    A valid Auth0 ROPC client must be created in WFM before using this feature.
                </flux:callout.heading>
            </flux:callout>

            <form method="POST" action="{{ route('known-places.storeWfm') }}" class="space-y-6">
                @csrf
                <div class="flex flex-col space-y-8 md:grid md:grid-cols-2 md:gap-4">
                    {{-- Left Column --}}
                    <div class="col-span-1 flex flex-col space-y-6">
                        <flux:fieldset>
                            <flux:legend>{{ __('Client Configuration') }}</flux:legend>
                            <div class="grid grid-cols-2 gap-4">
                                <flux:input
                                    name="client_id"
                                    label="Client ID"
                                    placeholder="Client ID"
                                    value="{{ old('client_id', session('wfm_credentials.client_id')) }}"
                                    required
                                />
                                <flux:input
                                    name="client_secret"
                                    label="Client Secret"
                                    placeholder="Client Secret"
                                    value="{{ old('client_secret', session('wfm_credentials.client_secret')) }}"
                                    type="password"
                                    required
                                    viewable
                                />
                                <div class="col-span-2">
                                    <flux:input
                                        name="org_id"
                                        label="Organization ID"
                                        placeholder="org_PGHKngyxtxV6kU7Z"
                                        value="{{ old('org_id', session('wfm_credentials.org_id')) }}"
                                        required
                                    />
                                </div>
                            </div>
                        </flux:fieldset>
                        <flux:fieldset>
                            <flux:legend>{{ __('WFM Configuration') }}</flux:legend>
                            <flux:input
                                name="username"
                                label="Username"
                                placeholder="APIUSER"
                                value="{{ old('username', session('wfm_credentials.username')) }}"
                                required
                            />
                            <flux:input
                                name="password"
                                label="Password"
                                placeholder="Password"
                                value="{{ old('password') }}"
                                type="password"
                                required
                                viewable
                            />
                            <flux:input
                                name="hostname"
                                label="Hostname"
                                placeholder="https://host.prd.mykronos.com"
                                value="{{ old('hostname', session('wfm_credentials.hostname')) }}"
                                type="url"
                                required
                            />
                        </flux:fieldset>
                    </div>

                    {{-- Right Column (Known Place) --}}
                    <div class="col-span-1">
                        <flux:fieldset>
                            <flux:legend>{{ __('Known Place') }}</flux:legend>
                            <flux:input
                                name="name"
                                label="Name"
                                placeholder="Name"
                                value="{{ old('name') }}"
                                required
                            />

                            <flux:input
                                name="description"
                                label="Description"
                                placeholder="Description"
                                value="{{ old('description') }}"
                                x-on:coordinates-updated.window="$el.value = $event.detail.formatted_address;"
                            />

                            {{-- Latitude and Longitude Grid --}}
                            <div class="grid grid-cols-2 gap-4">
                                <flux:input
                                    id="latitude"
                                    name="latitude"
                                    label="Latitude"
                                    placeholder="41.64235"
                                    value="{{ old('latitude', '41.64235') }}"
                                    x-on:coordinates-updated.window="$el.value = $event.detail.latitude;"
                                    required
                                />

                                <flux:input
                                    id="longitude"
                                    name="longitude"
                                    label="Longitude"
                                    placeholder="-71.56489"
                                    value="{{ old('longitude', '-71.56489') }}"
                                    x-on:coordinates-updated.window="$el.value = $event.detail.longitude;"
                                    required
                                />
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-4">
                                <flux:input
                                    id="radius"
                                    name="radius"
                                    label="Radius"
                                    placeholder="75"
                                    value="{{ old('radius', '75') }}"
                                    required
                                />

                                <flux:input
                                    id="accuracy"
                                    name="accuracy"
                                    label="Accuracy"
                                    placeholder="100"
                                    value="{{ old('accuracy', '100') }}"
                                    required
                                />
                            </div>

                            {{-- Color input required for the map.js to work properly --}}
                            {{-- I'm way too lazy to remove or modify the requirement, so maybe in the future --}}
                            <input type="hidden" id="color" name="color" value="#3b82f6" />
                        </flux:fieldset>
                    </div>
                </div>
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="inline-flex">Submit</flux:button>
                </div>
            </form>
        </div>
        <!-- Map -->
        <div class="relative h-[480px] rounded-lg p-8 dark:bg-zinc-800">
            <!-- Map container -->
            <div class="h-full rounded-lg">
                <div id="map" class="h-full w-full rounded-md"></div>
            </div>

            <!-- Address search overlay -->
            <div class="absolute inset-8 z-[800] w-3/4 px-3 md:inset-10 md:w-lg">
                <livewire:address-search />
            </div>
        </div>
    </div>
    @push('scripts')
        <!-- Map & Echo Scripts -->
        @vite(['resources/js/map.js', 'resources/js/echo.js'])
    @endpush
</x-layouts.app>
