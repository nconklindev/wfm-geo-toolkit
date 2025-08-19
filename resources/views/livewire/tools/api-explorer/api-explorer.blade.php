<div class="flex flex-col space-y-6 overflow-hidden">
    <div class="mt-10">
        <flux:heading size="xl" class="flex items-center space-x-3">
            <flux:icon.code-bracket class="h-6 w-6 text-blue-600 dark:text-blue-400" />
            <span>WFM API Explorer</span>
        </flux:heading>
        <flux:text class="mt-2 text-sm leading-relaxed">
            Configure your API flow type and select an endpoint to explore WFM API functionality.
        </flux:text>
    </div>
    <!-- Flow Configuration Section - First Priority -->
    <div class="rounded-xl border border-zinc-200 bg-gradient-to-br from-blue-50 to-indigo-50 p-6 shadow-sm dark:border-zinc-700 dark:from-blue-950/30 dark:to-indigo-950/30">
        <div class="mb-4">
            <flux:heading size="lg" class="flex items-center space-x-2 text-blue-800 dark:text-blue-200">
                <flux:icon.adjustments-horizontal class="h-5 w-5" />
                <span>API Flow Configuration</span>
            </flux:heading>
            <flux:text size="sm" variant="subtle" class="mt-1">
                Choose how you want to authenticate and interact with the WFM API
            </flux:text>
        </div>

        <div class="space-y-6">
            <!-- Flow Type Selection -->
            <div>
                <flux:label class="mb-3 block text-sm font-medium">Authentication Flow Type</flux:label>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <label class="relative flex cursor-pointer rounded-lg border border-zinc-200 bg-white p-4 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:hover:bg-zinc-700"
                           :class="{'ring-2 ring-blue-500 border-blue-500': $wire.flowType === 'interactive'}">
                        <input type="radio"
                               wire:model.live="flowType"
                               value="interactive"
                               class="sr-only">
                        <div class="flex w-full items-start space-x-3">
                            <div class="flex h-5 items-center">
                                <div class="h-4 w-4 rounded-full border-2 border-zinc-300 dark:border-zinc-500"
                                     :class="{'bg-blue-600 border-blue-600': $wire.flowType === 'interactive'}">
                                    <div x-show="$wire.flowType === 'interactive'"
                                         class="h-full w-full rounded-full bg-white"
                                         style="transform: scale(0.4);"></div>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-zinc-900 dark:text-white">Interactive Flow</div>
                                <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                    User-based authentication with interactive login
                                </div>
                            </div>
                            <flux:icon.user class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                    </label>

                    <label class="relative flex cursor-pointer rounded-lg border border-zinc-200 bg-white p-4 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:hover:bg-zinc-700"
                           :class="{'ring-2 ring-blue-500 border-blue-500': $wire.flowType === 'non-interactive'}">
                        <input type="radio"
                               wire:model.live="flowType"
                               value="non-interactive"
                               class="sr-only">
                        <div class="flex w-full items-start space-x-3">
                            <div class="flex h-5 items-center">
                                <div class="h-4 w-4 rounded-full border-2 border-zinc-300 dark:border-zinc-500"
                                     :class="{'bg-blue-600 border-blue-600': $wire.flowType === 'non-interactive'}">
                                    <div x-show="$wire.flowType === 'non-interactive'"
                                         class="h-full w-full rounded-full bg-white"
                                         style="transform: scale(0.4);"></div>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-zinc-900 dark:text-white">Non-Interactive Flow</div>
                                <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                    Service account authentication with predefined profiles
                                </div>
                            </div>
                            <flux:icon.cog-6-tooth class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                        </div>
                    </label>
                </div>
            </div>

        </div>
    </div>

    <!-- Credentials Section - Compact (Only show after flow selection) -->
    <div x-show="$wire.flowType"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 transform -translate-y-4 scale-95"
         class="rounded-lg bg-white p-4 shadow-sm dark:bg-zinc-800">
        <div class="mb-4 flex flex-col space-y-3 md:flex-row md:items-center md:justify-between md:space-y-0">
            <div class="flex flex-col space-y-2 md:flex-row md:items-center md:space-x-2 md:space-y-0">
                <div class="flex items-center space-x-2">
                    <flux:icon.key class="h-5 w-5 text-amber-500" />
                    <flux:heading size="lg" class="text-amber-700 dark:text-amber-400">Global Configuration
                    </flux:heading>
                </div>
            </div>

            <!-- Authentication Status Badge -->
            <div class="flex items-center space-x-2">
                @if ($isAuthenticated)
                    <flux:badge variant="solid" color="green" icon="check-circle" size="sm">Authenticated
                    </flux:badge>
                @else
                    <flux:badge variant="solid" color="red" icon="exclamation-circle" size="sm">
                        Not Authenticated
                    </flux:badge>
                @endif
            </div>
        </div>

        <!-- Flow Type Change Notice -->
        <div x-data="{ showNotice: false }"
             x-show="showNotice"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform -translate-y-2"
             @flow-type-changed.window="showNotice = true; setTimeout(() => showNotice = false, 4000)"
             class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
            <div class="flex items-center space-x-2">
                <flux:icon.exclamation-triangle class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                <flux:text size="sm" class="text-amber-800 dark:text-amber-200">
                    Authentication cleared due to flow type change. Please re-authenticate with your new flow configuration.
                </flux:text>
            </div>
        </div>

        <form class="space-y-4">
            <!-- Condensed Grid Layout -->
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 md:items-start">
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
                            <flux:input
                                wire:model="orgId"
                                :label="$flowType === 'interactive' ? 'Realm ID' : 'Organization ID'"
                                :placeholder="$flowType === 'interactive' ? 'PGPKzgyQPH6kX7Z-custom' : 'org_PGPKzgyQPH6kX7Z'"
                                value="{{ old('org_id', session('wfm_credentials.org_id')) }}"
                                size="sm"
                                required
                            />
                        </div>
                    </flux:fieldset>
                </div>

                <!-- WFM Configuration -->
                <div>
                    <flux:fieldset>
                        <flux:legend class="text-sm font-medium">WFM Configuration</flux:legend>
                        <div class="space-y-3">
                            <!-- Username and Password - Only for Interactive Flow -->
                            <div class="space-y-3"
                                 x-show="$wire.flowType === 'interactive'"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95">
                                <flux:input
                                    wire:model="username"
                                    label="Username"
                                    placeholder="APIUSER"
                                    value="{{ old('username', session('wfm_credentials.username')) }}"
                                    size="sm"
                                    autocomplete="off"
                                />
                                <flux:input
                                    wire:model="password"
                                    label="Password"
                                    placeholder="Password"
                                    value="{{ old('password') }}"
                                    type="password"
                                    autocomplete="off"
                                    size="sm"
                                    viewable
                                />
                            </div>

                            <!-- Hostname - Always shown -->
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
                        icon="lock-closed"
                        :disabled="$isLoading"
                    >
                    <span wire:loading.remove wire:target="saveCredentials">
                {{ $isAuthenticated ? 'Re-authenticate' : 'Authenticate' }}
            </span>
                        <span wire:loading wire:target="saveCredentials">Authenticating...</span>
                    </flux:button>
                </div>
            </div>
        </form>
    </div>

    <!-- Main API Explorer Content (Only show after authentication) -->
    <div x-show="$wire.flowType && $wire.isAuthenticated"
         x-transition:enter="transition ease-out duration-400 delay-100"
         x-transition:enter-start="opacity-0 transform -translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 transform -translate-y-4 scale-95"
         class="rounded-lg bg-white p-6 shadow-sm dark:bg-zinc-800">
        <div class="mb-8">
            <div class="flex flex-col space-y-4 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">
                <!-- Secondary Authentication Status -->
                <div class="flex items-center">
                    @if ($isAuthenticated)
                        <div class="flex items-center space-x-2 rounded-full bg-green-50 px-3 py-1.5 text-sm font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">
                            <flux:icon.shield-check class="h-4 w-4" />
                            <span>Ready to make API calls</span>
                        </div>
                    @else
                        <div class="flex items-center space-x-2 rounded-full bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 dark:bg-red-900/20 dark:text-red-400">
                            <flux:icon.shield-exclamation class="h-4 w-4" />
                            <span>Authentication required</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Endpoint Selection Section -->
        <div class="mb-8">
            <div class="mb-4">
                <flux:heading size="lg" class="flex items-center space-x-2">
                    <flux:icon.globe-alt class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    <span>API Endpoint Selection</span>
                </flux:heading>
                <flux:text size="sm" variant="subtle" class="mt-1">
                    Browse and select from available WFM API endpoints organized by category
                </flux:text>
            </div>

            <div x-data="{ open: false, selected: null }" class="relative">
                <flux:label class="mb-2 block">Available API Endpoints</flux:label>

                <!-- Enhanced Dropdown Trigger -->
                <button
                    type="button"
                    @click="open = !open"
                    class="flex w-full items-center justify-between rounded-xl border border-zinc-300 bg-white px-4 py-3 text-left shadow-sm ring-1 ring-transparent transition-all duration-200 hover:bg-zinc-50 hover:border-zinc-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700 dark:hover:border-zinc-500"
                >
                    <div class="flex items-center space-x-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/30">
                            <flux:icon.code-bracket class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <span
                            x-text="selected ? selected.label : 'Choose an API endpoint to get started...'"
                            :class="selected ? 'text-zinc-900 dark:text-white font-medium' : 'text-zinc-500 dark:text-zinc-400'"
                        ></span>
                    </div>
                    <flux:icon.chevron-down
                        class="h-5 w-5 text-zinc-400 transition-transform duration-200"
                        :class="$open ? 'rotate-180' : ''" />
                </button>

                <!-- Enhanced Dropdown Panel -->
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95"
                    @click.away="open = false"
                    class="absolute z-20 mt-2 w-full max-h-96 overflow-y-auto rounded-xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-800"
                >
                <!-- GPS Known Places Group -->
                <x-api-endpoint-group title="GPS Known Places" name="map-pin">
                    <x-api-endpoint-item value="places.create" label="Create Known Place" method="POST">
                        Create Known Place
                    </x-api-endpoint-item>
                </x-api-endpoint-group>

                <x-api-endpoint-group title="Common Resources I" name="check">
                    <x-api-endpoint-item value="retrieve-known-ip-addresses" label="Retrieve Known IP Addresses"
                                         method="GET">
                        Retrieve Known IP Addresses
                    </x-api-endpoint-item>
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
                        value="pay-rules-list"
                        label="Retrieve All Timekeeping Pay Rules"
                        method="GET"
                    >
                        Retrieve All Timekeeping Pay Rules
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
                <x-api-endpoint-group title="Report Requests" name="document-text">
                    <x-api-endpoint-item
                        value="scheduled-report-jobs"
                        label="Retrieve Paginated List of Scheduled Report Requests"
                        method="POST">Retrieve Paginated List of Scheduled Report Requests
                    </x-api-endpoint-item>
                </x-api-endpoint-group>
                </div>
            </div>
        </div>

        <!-- API Endpoint Results -->
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
             id="endpoint-selection">
            @if ($selectedEndpoint)
                @php
                    // Convert endpoint to Livewire component name: places.create -> tools.api-explorer.endpoints.places-create
                    $livewireComponentName = 'tools.api-explorer.endpoints.' . str_replace('.', '-', $selectedEndpoint);
                    $livewireComponentClass = "App\\Livewire\\Tools\\ApiExplorer\\Endpoints\\" . Str::studly(str_replace('.', '-', $selectedEndpoint));
                @endphp

                <div class="mb-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:heading size="lg" class="flex items-center space-x-2">
                                <flux:icon.play class="h-5 w-5 text-green-600 dark:text-green-400" />
                                <span>{{ $selectedLabel }}</span>
                            </flux:heading>
                            <flux:text size="sm" variant="subtle" class="mt-1">
                                Configure and test this API endpoint with your selected flow type
                            </flux:text>
                        </div>
                        <flux:badge
                            :variant="$flowType === 'interactive' ? 'primary' : 'secondary'"
                            size="sm"
                        >
                            {{ ucfirst(str_replace('-', ' ', $flowType)) }} Flow
                        </flux:badge>
                    </div>
                </div>

                @if (class_exists($livewireComponentClass))
                    {{-- Lazy load the components for the endpoints which displays a nice loading indicator --}}
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-600 dark:bg-zinc-900/50">
                        <livewire:dynamic-component
                            lazy
                            :component="$livewireComponentName"
                            :isAuthenticated="$isAuthenticated"
                            :hostname="$hostname"
                            :flowType="$flowType"
                            :accessToken="$isAuthenticated ? $wfmService->getAccessToken() : null"
                            :key="$selectedEndpoint . '-' . $flowType . '-' . now()->timestamp"
                        />
                    </div>
                @else
                    <div class="rounded-xl border-2 border-dashed border-amber-200 bg-amber-50 p-8 text-center dark:border-amber-800 dark:bg-amber-900/20">
                        <flux:icon.wrench-screwdriver class="mx-auto mb-4 h-12 w-12 text-amber-500" />
                        <flux:heading size="lg" class="mb-2 text-amber-800 dark:text-amber-200">
                            Interface Under Development
                        </flux:heading>
                        <flux:text variant="subtle" class="mb-4">
                            The interface for "{{ $selectedLabel }}" is currently being built.
                        </flux:text>
                        <div class="rounded-lg bg-white p-4 text-left dark:bg-zinc-800">
                            <flux:text size="sm" variant="subtle" class="block">
                                <strong>Component:</strong> {{ $livewireComponentName }}
                            </flux:text>
                            <flux:text size="sm" variant="subtle" class="mt-1 block">
                                <strong>Class:</strong> {{ $livewireComponentClass }}
                            </flux:text>
                        </div>
                    </div>
                @endif
            @else
                <div class="rounded-xl border-2 border-dashed border-zinc-200 p-12 text-center dark:border-zinc-700">
                    <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon.cursor-arrow-rays class="h-8 w-8 text-zinc-400" />
                    </div>
                    <flux:heading size="lg" class="mb-2 text-zinc-600 dark:text-zinc-400">
                        Ready to Explore
                    </flux:heading>
                    <flux:text variant="subtle" class="mb-6">
                        Select an API endpoint from the dropdown above to begin testing WFM API functionality.
                    </flux:text>
                    <div class="flex items-center justify-center space-x-2 text-sm text-zinc-500 dark:text-zinc-400">
                        <flux:icon.information-circle class="h-4 w-4" />
                        <span>Configure your flow type first, then choose an endpoint to get started</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Next Steps Guide (when authenticated but no flow selected or not authenticated) -->
    <div x-show="$wire.flowType && !$wire.isAuthenticated"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 transform -translate-y-4 scale-95"
         class="rounded-xl border-2 border-dashed border-blue-200 bg-blue-50 p-8 text-center dark:border-blue-800 dark:bg-blue-950/20">
        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
            <flux:icon.arrow-down class="h-6 w-6 text-blue-600 dark:text-blue-400" />
        </div>
        <flux:heading size="lg" class="mb-2 text-blue-800 dark:text-blue-200">
            Next: Complete Authentication
        </flux:heading>
        <flux:text variant="subtle" class="mb-4">
            Great! You've selected <strong x-text="$wire.flowType === 'interactive' ? 'Interactive' : 'Non-Interactive'"></strong> flow.
            Now complete the configuration above and click "Authenticate" to access the API Explorer.
        </flux:text>
        <div class="flex items-center justify-center space-x-2 text-sm text-blue-600 dark:text-blue-400">
            <flux:icon.information-circle class="h-4 w-4" />
            <span>Fill in your credentials and authenticate to continue</span>
        </div>
    </div>
</div>
