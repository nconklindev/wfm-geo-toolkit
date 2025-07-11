<div class="space-y-6">
    <!-- Endpoint Header -->
    <x-api-endpoint-header heading="Create Known Places" method="POST" wfm-endpoint="/api/v1/commons/known_places" />

    <!-- Form Content -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Input Section -->
        <div class="space-y-4">
            <flux:heading size="md">Place Details</flux:heading>

            <form wire:submit="createKnownPlace" class="space-y-4">
                <!-- Form Input Mode -->
                <div class="space-y-4">
                    <flux:input wire:model="name" label="Name" placeholder="Main Office" required />

                    <flux:input
                        wire:model="description"
                        label="Description"
                        placeholder="Corporate headquarters (optional)"
                    />

                    <!-- Coordinates Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input wire:model="latitude" label="Latitude" placeholder="40.7128" step="any" required />
                        <flux:input
                            wire:model="longitude"
                            label="Longitude"
                            placeholder="-74.0060"
                            step="any"
                            required
                        />
                    </div>

                    <!-- TODO: Locations -->

                    <!-- Measurements Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input
                            wire:model="radius"
                            label="Radius (meters)"
                            placeholder="75"
                            type="number"
                            min="1"
                            max="10000"
                            required
                        />
                        <flux:input
                            wire:model="accuracy"
                            label="GPS Accuracy Threshold (meters)"
                            placeholder="100"
                            type="number"
                            min="1"
                            max="10000"
                            required
                        />
                    </div>

                    <flux:checkbox.group wire:model="validationOrder" label="Validation Order">
                        <flux:checkbox value="WIFI" label="WiFi" />
                        <flux:checkbox value="GPS" label="GPS" checked />
                    </flux:checkbox.group>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center space-x-3">
                    <flux:button
                        type="submit"
                        variant="primary"
                        :disabled="!$isAuthenticated || $isLoading"
                        wire:loading.attr="disabled"
                    >
                        <flux:icon.plus class="mr-2 h-4 w-4" />
                        <span wire:loading.remove wire:target="createKnownPlace">Create Place</span>
                        <span wire:loading wire:target="createKnownPlace">Creating...</span>
                    </flux:button>
                </div>

                @if (! $isAuthenticated)
                    <flux:error>Please authenticate first using the credentials form above.</flux:error>
                @endif
            </form>
        </div>

        <!-- Documentation Section -->
        <div class="space-y-4">
            <flux:heading size="md">
                @if ($inputMode === 'form')
                    Tips & Guidelines
                @else
                    Documentation
                @endif
            </flux:heading>

            @if ($inputMode === 'form')
                <!-- Form Mode Documentation -->
                <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                    <flux:heading size="sm" class="mb-2 text-blue-800 dark:text-blue-200">
                        <flux:icon.information-circle class="mr-1 inline h-4 w-4" />
                        Form Guidelines
                    </flux:heading>
                    <ul class="list-inside list-disc space-y-1 text-sm text-blue-700 dark:text-blue-300">
                        <li>
                            <strong>Name:</strong>
                            Choose a descriptive, unique name
                        </li>
                        <li>
                            <strong>Coordinates:</strong>
                            Use decimal degrees format
                        </li>
                        <li>
                            <strong>Radius:</strong>
                            Geofence boundary (50-500m typical)
                        </li>
                        <li>
                            <strong>Accuracy:</strong>
                            GPS precision threshold
                        </li>
                        <li>Place ID will be automatically assigned</li>
                        <li>
                            The generated Known Place will automatically be marked as "active". Please use the JSON
                            Input if you'd like more control over this field.
                        </li>
                    </ul>
                </div>

                <div class="rounded-lg bg-amber-50 p-4 dark:bg-amber-900/20">
                    <flux:heading size="sm" class="mb-2 text-amber-800 dark:text-amber-200">
                        <flux:icon.exclamation-triangle class="mr-1 inline h-4 w-4" />
                        Best Practices
                    </flux:heading>
                    <ul class="list-inside list-disc space-y-1 text-sm text-amber-700 dark:text-amber-300">
                        <li>Test coordinates on a map first</li>
                        <li>Consider building size when setting radius</li>
                        <li>Use consistent naming conventions</li>
                        <li>Account for GPS accuracy in areas with taller buildings and less cellular service</li>
                    </ul>
                </div>
            @else
                <!-- JSON Mode Documentation -->
                <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                    <flux:heading size="sm" class="mb-2">Required Fields</flux:heading>
                    <ul class="space-y-1 text-sm">
                        <li>
                            <code class="rounded bg-zinc-200 px-1 py-0.5 text-xs dark:bg-zinc-700">name</code>
                            - Place name (string)
                        </li>
                        <li>
                            <code class="rounded bg-zinc-200 px-1 py-0.5 text-xs dark:bg-zinc-700">description</code>
                            - The Known Place description (string)
                        </li>
                        <li>
                            <code class="rounded bg-zinc-200 px-1 py-0.5 text-xs dark:bg-zinc-700">latitude</code>
                            - Latitude coordinate (float)
                        </li>
                        <li>
                            <code class="rounded bg-zinc-200 px-1 py-0.5 text-xs dark:bg-zinc-700">longitude</code>
                            - Longitude coordinate (float)
                        </li>
                        <li>
                            <code class="rounded bg-zinc-200 px-1 py-0.5 text-xs dark:bg-zinc-700">radius</code>
                            - Geofence radius in meters (integer)
                        </li>
                        <li>
                            <code class="rounded bg-zinc-200 px-1 py-0.5 text-xs dark:bg-zinc-700">accuracy</code>
                            - The GPS accuracy of a Known Place in meters (integer)
                        </li>
                        <li>
                            <code class="rounded bg-zinc-200 px-1 py-0.5 text-xs dark:bg-zinc-700">
                                validationOrder
                            </code>
                            - An array defining the location validation order of a Known Place which supports two
                            values: WIFI and GPS.
                        </li>
                    </ul>
                </div>

                <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                    <flux:heading size="sm" class="mb-2 text-blue-800 dark:text-blue-200">
                        <flux:icon.information-circle class="mr-1 inline h-4 w-4" />
                        JSON Tips
                    </flux:heading>
                    <ul class="list-inside list-disc space-y-1 text-sm text-blue-700 dark:text-blue-300">
                        <li>Use unique IDs to avoid conflicts</li>
                        <li>Coordinates should be in decimal degrees</li>
                        <li>You can create multiple places in one request</li>
                        <li>Optional fields: description, accuracy, active</li>
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <!-- Response Section -->
    <x-api-response :response="$apiResponse" :error="$errorMessage" />
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('json-validated', (event) => {
            alert(event.message);
        });
    });
</script>
