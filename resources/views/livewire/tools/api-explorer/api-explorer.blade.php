<div class="flex flex-col space-y-6 overflow-hidden">
    <!-- Credentials Section - Compact -->
    <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-zinc-800">
        <div class="mb-4 flex items-center space-x-2">
            <flux:icon.key class="h-5 w-5 text-amber-500" />
            <flux:heading size="lg" class="text-amber-700 dark:text-amber-400">Global Configuration</flux:heading>
            <flux:badge variant="warning" size="sm">Required for all endpoints</flux:badge>
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
            <div class="flex items-center justify-between border-t pt-3 dark:border-zinc-700">
                <flux:text size="sm" variant="subtle">
                    <flux:icon.information-circle class="mr-1 inline h-4 w-4" />
                    Credentials are cached for this session
                </flux:text>
                <flux:button size="sm" class="cursor-pointer" wire:click="saveCredentials" icon="check">
                    Save Credentials
                </flux:button>
            </div>
        </form>
    </div>

    <!-- Main API Explorer Content -->
    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-zinc-800">
        <div class="mb-6">
            <flux:heading size="xl">WFM API Explorer</flux:heading>
            <flux:text class="mt-1.5 text-sm">
                Select an endpoint below to explore and test WFM API functionality.
            </flux:text>
        </div>

        <!-- Basic Custom Dropdown -->
        <div class="mb-6" x-data="{ open: false, selected: null }">
            <flux:label>Select API Endpoint</flux:label>

            <!-- Dropdown Trigger -->
            <button
                type="button"
                @click="open = !open"
                class="flex w-full items-center justify-between rounded-lg border border-zinc-300 bg-white px-3 py-2 text-left shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
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
                class="absolute z-10 mt-1 w-full rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
            >
                <!-- GPS Known Places Group -->
                <x-api-endpoint-group title="GPS Known Places" name="map-pin">
                    <x-api-endpoint-item value="places.list" label="Retrieve All Known Places" method="GET">
                        Retrieve All Known Places
                    </x-api-endpoint-item>

                    <x-api-endpoint-item value="places.create" label="Create Known Place" method="POST">
                        Create Known Place
                    </x-api-endpoint-item>

                    <x-api-endpoint-item value="places.delete" label="Delete Known Places" method="POST">
                        Delete Known Places
                    </x-api-endpoint-item>

                    <x-api-endpoint-item value="places.delete_by_id" label="Delete Known Place by ID" method="DELETE">
                        Delete Known Place by ID
                    </x-api-endpoint-item>
                </x-api-endpoint-group>

                <!-- Locations Group -->
                <x-api-endpoint-group title="Data Dictionary" name="book-open">
                    <x-api-endpoint-item
                        value="data_elements.list"
                        label="Retrieve Data Element Definitions"
                        method="GET"
                    >
                        Retrieve Data Element Definitions
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
                    $livewireComponentClass = 'App\\Livewire\\Tools\\ApiExplorer\\Endpoints\\' . Str::studly(str_replace('.', '-', $selectedEndpoint));
                    '';
                    '';
                @endphp

                @if (class_exists($livewireComponentClass))
                    <livewire:dynamic-component
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
