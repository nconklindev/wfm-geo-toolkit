<div class="space-y-6">
    <!-- Endpoint Header -->
    <x-api-endpoint-header
        heading="Retrieve Paginated List of Locations"
        method="POST"
        wfm-endpoint="/api/v2/commons/locations/multi_read"
    />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Input Section -->
        <section class="space-y-4">
            <div class="grid grid-cols-2 gap-8">
                <flux:field>
                    <flux:label>Qualifier</flux:label>
                    <flux:description>
                        The name of the node from the Business Structure to retrieve all descendant nodes of.
                    </flux:description>
                    <flux:input
                        wire:model="qualifier"
                        class="self-end-safe"
                        placeholder="UKG Inc."
                        :value="old('qualifier')"
                    />
                    <flux:error name="qualifier" />
                </flux:field>

                <flux:field>
                    <flux:label class="self-start">Date</flux:label>
                    <flux:description>The date to search from.</flux:description>
                    <flux:input wire:model="date" type="date" class="self-end-safe" :value="old('date')" />
                    <flux:error name="date" />
                </flux:field>
            </div>
            <!-- Execute Button -->
            <div class="space-y-4">
                <flux:button
                    variant="primary"
                    wire:click="executeRequest"
                    wire:loading.attr="disabled"
                    :disabled="!$isAuthenticated"
                    class="disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="executeRequest">Execute Request</span>
                    <span wire:loading wire:target="executeRequest">Loading...</span>
                </flux:button>

                @if (! $isAuthenticated)
                    <x-alert type="warning" class="mb-4">Please authenticate to execute this request</x-alert>
                @endif
            </div>
        </section>

        <!-- Documentation Section -->
        <div class="space-y-4">
            <flux:heading size="md">Endpoint Information</flux:heading>

            <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                <flux:heading size="sm" class="mb-2">About This Endpoint</flux:heading>
                <ul class="list-inside list-disc space-y-1 text-sm">
                    <li>Retrieves a paginated list of Organizational Map locations</li>
                    <li>Review the table for at-a-glance information or the raw JSON output for additional details</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Always show the table component once data has been loaded at least once --}}
    @if (! empty($tableColumns) && $totalRecords > 0)
        <x-api-data-table
            :paginatedData="$paginatedData"
            :columns="$tableColumns"
            title="Pay Codes"
            :totalRecords="$totalRecords"
            :search="$search"
            :sortField="$sortField"
            :sortDirection="$sortDirection"
            :perPage="$perPage"
        />
    @endif

    <x-api-response :response="$apiResponse" :error="$errorMessage" :raw-json-cache-key="$rawJsonCacheKey" />
</div>
