<div class="space-y-6">
    <!-- Endpoint Header -->
    <x-api-endpoint-header
        heading="Retrieve Data Element Definitions"
        method="GET"
        wfm-endpoint="/api/v1/commons/data_dictionary/data_elements"
    />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Input Section -->
        <section class="space-y-4">
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
                    <li>Retrieves all available columns from all available entities</li>
                    <li>Provides useful information about columns</li>
                    <li>Review the table for at-a-glance information or the raw JSON output for additional details</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Always show the table component once data has been loaded at least once -->
    @if (! empty($tableColumns) && ($totalRecords > 0 || ! empty($cacheKey)))
        <x-api-data-table
            :paginated-data="$paginatedData"
            :columns="$tableColumns"
            title="Data Element Definitions"
            :total-records="$totalRecords"
            :search="$search"
            :sort-field="$sortField"
            :sort-direction="$sortDirection"
            :per-page="$perPage"
        />
    @endif

    <x-api-response
        :response="$apiResponse"
        :error="$errorMessage"
        :show-raw-json="$showRawJson"
        component-id="{{ $this->getId() }}"
    />
</div>
