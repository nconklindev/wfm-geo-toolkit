<div class="flex flex-col space-y-6 overflow-hidden">
    <!-- Credentials Section - Compact -->
    <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-zinc-800">
        <div class="mb-4 flex flex-col space-y-3 md:flex-row md:items-center md:justify-between md:space-y-0">
            <div class="flex flex-col space-y-2 md:flex-row md:items-center md:space-x-2 md:space-y-0">
                <div class="flex items-center space-x-2">
                    <flux:icon.key class="h-5 w-5 text-amber-500" />
                    <flux:heading size="lg" class="text-amber-700 dark:text-amber-400">Global Configuration
                    </flux:heading>
                </div>
                <flux:badge variant="warning" size="sm" class="w-fit">Required for all endpoints</flux:badge>
            </div>

            <!-- Authentication Status Badge -->
            <div class="flex items-center space-x-2">
                @if ($isAuthenticated)
                    <flux:badge variant="success" size="sm">
                        <flux:icon.check-circle class="mr-1 h-3 w-3" />
                        <span class="md:inline">Authenticated</span>
                    </flux:badge>
                @else
                    <flux:badge variant="danger" size="sm">
                        <flux:icon.exclamation-circle class="mr-1 h-3 w-3" />
                        <span class="md:inline">Not Authenticated</span>
                    </flux:badge>
                @endif
            </div>
        </div>

        <form class="space-y-4">
            <!-- Condensed Grid Layout -->
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <!-- Client Configuration -->
                <div>
                    <flux:fieldset>
                        <flux:legend class="text-sm font-medium">Client Configuration</flux:legend>
                        <div class="space-y-3">
                            <flux:input
                                wire:model="clientId"
                                label="Client ID"
                                placeholder="Enter Client ID"
                                value="{{ old('client_id', session('wfm_credentials.client_id')) }}"
                                size="sm"
                                autofocus
                                tabindex="0"
                                required
                            />
                            <flux:input
                                wire:model="clientSecret"
                                label="Client Secret"
                                placeholder="Enter Client Secret"
                                value="{{ old('client_secret', session('wfm_credentials.client_secret')) }}"
                                type="password"
                                autocomplete="off"
                                size="sm"
                                required
                                viewable
                            />
                            <div class="">
                                <flux:input
                                    wire:model="orgId"
                                    label="Organization ID"
                                    placeholder="org_PGHKngyxtxV6kU7Z"
                                    value="{{ old('org_id', session('wfm_credentials.org_id')) }}"
                                    size="sm"
                                    required
                                />
                            </div>
                        </div>
                    </flux:fieldset>
                </div>

                <!-- WFM Configuration -->
                <div>
                    <flux:fieldset>
                        <flux:legend class="text-sm font-medium">WFM Configuration</flux:legend>
                        <div class="space-y-3">
                            <flux:input
                                wire:model="username"
                                label="Username"
                                placeholder="APIUSER"
                                value="{{ old('username', session('wfm_credentials.username')) }}"
                                size="sm"
                                required
                            />
                            <flux:input
                                wire:model="password"
                                label="Password"
                                placeholder="Password"
                                value="{{ old('password') }}"
                                type="password"
                                autocomplete="off"
                                size="sm"
                                required
                                viewable
                            />
                            <flux:input
                                wire:model="hostname"
                                label="Hostname"
                                placeholder="https://host.prd.mykronos.com"
                                value="{{ old('hostname', session('wfm_credentials.hostname')) }}"
                                type="url"
                                size="sm"
                                required
                            />
                        </div>
                    </flux:fieldset>
                </div>
            </div>

            <!-- Save Credentials Button -->
            <div
                class="space-y-4 border-t pt-3 dark:border-zinc-700 md:flex md:items-center md:justify-between md:space-y-0">
                <div class="space-y-2 md:flex md:items-center md:space-x-4 md:space-y-0">
                    <flux:text size="sm" variant="subtle">
                        <flux:icon.information-circle class="mr-1 inline h-4 w-4" />
                        Credentials are cached for this session
                    </flux:text>

                    @if ($isAuthenticated)
                        <flux:text size="sm" class="text-green-600 dark:text-green-400">
                            <flux:icon.wifi class="mr-1 inline h-4 w-4" />
                            Connected to
                            {{ parse_url($hostname, PHP_URL_HOST) ?? 'API' }}
                        </flux:text>
                    @endif
                </div>

                <div class="flex items-center space-x-2">
                    @if ($isAuthenticated)
                        <flux:button
                            size="sm"
                            variant="ghost"
                            icon="arrow-right-start-on-rectangle"
                            wire:click="logout"
                        >
                            Logout
                        </flux:button>
                    @endif

                    <flux:button
                        size="sm"
                        class="cursor-pointer"
                        wire:click="saveCredentials"
                        icon="check"
                        :disabled="$isLoading"
                    >
            <span wire:loading.remove wire:target="saveCredentials">
                {{ $isAuthenticated ? 'Re-authenticate' : 'Save Credentials' }}
            </span>
                        <span wire:loading wire:target="saveCredentials">Authenticating...</span>
                    </flux:button>
                </div>
            </div>
        </form>
    </div>

    <!-- Main API Explorer Content -->
    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-zinc-800">
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="xl">WFM API Explorer</flux:heading>
                    <flux:text class="mt-1.5 text-sm">
                        Select an endpoint below to explore and test WFM API functionality.
                    </flux:text>
                </div>

                <!-- Secondary Authentication Status -->
                <div class="hidden md:block">
                    @if ($isAuthenticated)
                        <div class="flex items-center space-x-2 text-sm text-green-600 dark:text-green-400">
                            <flux:icon.shield-check class="h-4 w-4" />
                            <span>Ready to make API calls</span>
                        </div>
                    @else
                        <div class="flex items-center space-x-2 text-sm text-red-600 dark:text-red-400">
                            <flux:icon.shield-exclamation class="h-4 w-4" />
                            <span>Authentication required</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Basic Custom Dropdown -->
        <div class="mb-6" x-data="{ open: false, selected: null }">
            <flux:label>Select API Endpoint</flux:label>

            <!-- Dropdown Trigger -->
            <button
                type="button"
                @click="open = !open"
                class="flex w-full items-center justify-between rounded-lg border border-zinc-300 bg-white px-3 py-2 text-left shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none md:w-md dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
            >
                <span
                    x-text="selected ? selected.label : 'Choose an endpoint...'"
                    :class="selected ? 'text-zinc-900 dark:text-white' : 'text-zinc-500 dark:text-zinc-400'"
                ></span>
                <flux:icon.chevron-down class="h-4 w-4 text-zinc-400" />
            </button>

            <!-- Dropdown Panel -->
            <div
                x-show="open"
                @click.away="open = false"
                class="absolute z-10 mt-1 max-h-96 max-w-64 min-w-96 overflow-y-auto rounded-lg border border-zinc-200 bg-white text-wrap shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
            >
                <!-- GPS Known Places Group -->
                <x-api-endpoint-group title="GPS Known Places" name="map-pin">
                    {{-- <x-api-endpoint-item value="places.list" label="Retrieve All Known Places" method="GET"> --}}
                    {{-- Retrieve All Known Places --}}
                    {{-- </x-api-endpoint-item> --}}

                    <x-api-endpoint-item value="places.create" label="Create Known Place" method="POST">
                        Create Known Place
                    </x-api-endpoint-item>

                    {{-- <x-api-endpoint-item value="places.delete" label="Delete Known Places" method="POST"> --}}
                    {{-- Delete Known Places --}}
                    {{-- </x-api-endpoint-item> --}}

                    {{-- <x-api-endpoint-item value="places.delete_by_id" label="Delete Known Place by ID" method="DELETE"> --}}
                    {{-- Delete Known Place by ID --}}
                    {{-- </x-api-endpoint-item> --}}
                </x-api-endpoint-group>

                <x-api-endpoint-group title="Common Resources II" name="check">
                    <x-api-endpoint-item value="hyperfind-queries-list" label="Retrieve Public Hyperfind Queries"
                                         method="GET">
                        Retrieve Public Hyperfind Queries
                    </x-api-endpoint-item>
                </x-api-endpoint-group>

                <x-api-endpoint-group title="Labor Category Entries" name="briefcase">
                    <x-api-endpoint-item
                        value="labor-categories.list"
                        label="Retrieve Labor Category Entries"
                        method="POST"
                    >
                        Retrieve Labor Category Entries
                    </x-api-endpoint-item>
                    <x-api-endpoint-item
                        value="labor-categories-paginated.list"
                        label="Retrieve Paginated List of Labor Category Entries"
                        method="POST"
                    >
                        Retrieve Paginated List of Labor Category Entries
                    </x-api-endpoint-item>
                </x-api-endpoint-group>

                <!-- Data Dictionary -->
                <x-api-endpoint-group title="Data Dictionary" name="book-open">
                    <x-api-endpoint-item
                        value="data-elements.list"
                        label="Retrieve Data Element Definitions"
                        method="GET"
                    >
                        Retrieve Data Element Definitions
                    </x-api-endpoint-item>
                </x-api-endpoint-group>

                <!-- Timekeeping -->
                <x-api-endpoint-group title="Timekeeping" name="clock">
                    <x-api-endpoint-item
                        value="adjustment-rules-list"
                        label="Retrieve All Adjustment Rules"
                        method="GET"
                    >
                        Retrieve All Adjustment Rules
                    </x-api-endpoint-item>
                    <x-api-endpoint-item
                        value="percent-allocation-rules-list"
                        label="Retrieve All Percentage Allocation Rules"
                        method="GET"
                    >
                        Retrieve All Percentage Allocation Rules
                    </x-api-endpoint-item>
                    <x-api-endpoint-item
                        value="paycodes-list"
                        label="Retrieve Paycodes as a Manager"
                        method="GET"
                    >
                        Retrieve Paycodes as a Manager
                    </x-api-endpoint-item>
                </x-api-endpoint-group>
                <x-api-endpoint-group title="People" name="user">
                    <x-api-endpoint-item
                        value="retrieve-all-persons"
                        label="Retrieve All Persons"
                        method="POST"
                    >Retrieve All Persons
                    </x-api-endpoint-item>
                </x-api-endpoint-group>
                <x-api-endpoint-group title="Locations" name="building-storefront">
                    <x-api-endpoint-item
                        value="locations-paginated-list"
                        label="Retrieve Paginated List of Locations"
                        method="POST"
                    >Retrieve Paginated List of Locations
                    </x-api-endpoint-item>
                </x-api-endpoint-group>
            </div>
        </div>

        <!-- API Endpoint Selection -->
        <div id="endpoint-selection">
            @if ($selectedEndpoint)
                @php
                    // Convert endpoint to Livewire component name: places.create -> tools.api-explorer.endpoints.places-create
                    $livewireComponentName = 'tools.api-explorer.endpoints.' . str_replace('.', '-', $selectedEndpoint);
                    $livewireComponentClass = "App\\Livewire\\Tools\\ApiExplorer\\Endpoints\\" . Str::studly(str_replace('.', '-', $selectedEndpoint));
                @endphp

                @if (class_exists($livewireComponentClass))
                    {{-- Lazy load the components for the endpoints which displays a nice loading indicator --}}
                    <livewire:dynamic-component
                        lazy
                        :component="$livewireComponentName"
                        :isAuthenticated="$isAuthenticated"
                        :hostname="$hostname"
                        :key="$selectedEndpoint . '-' . now()->timestamp"
                    />
                @else
                    <div class="rounded-lg border-2 border-dashed border-zinc-200 p-8 text-center dark:border-zinc-700">
                        <flux:icon.code-bracket class="mx-auto mb-4 h-12 w-12 text-zinc-400" />
                        <flux:text variant="subtle">Interface for "{{ $selectedLabel }}" coming soon...</flux:text>
                        <flux:text size="sm" variant="subtle" class="mt-2">
                            Component: {{ $livewireComponentName }}
                        </flux:text>
                        <flux:text size="sm" variant="subtle" class="mt-1">
                            Class: {{ $livewireComponentClass }}
                        </flux:text>
                    </div>
                @endif
            @else
                <div class="rounded-lg border-2 border-dashed border-zinc-200 p-8 text-center dark:border-zinc-700">
                    <flux:icon.code-bracket class="mx-auto mb-4 h-12 w-12 text-zinc-400" />
                    <flux:text variant="subtle">Select an endpoint above to get started</flux:text>
                </div>
            @endif
        </div>
    </div>
</div>
