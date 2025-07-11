<div class="space-y-6">
    <!-- Endpoint Header -->
    <x-api-endpoint-header
        heading="Retrieve Labor Category Entries"
        method="POST"
        wfm-endpoint="/api/v1/commons/labor_entries/multi_read"
    />

    <!-- Form Content -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Input Section -->
        <section class="space-y-4">
            <!-- Form Input Mode -->
            <div class="space-y-8">
                <flux:field>
                    <flux:label class="mb-4">Labor Category Entry Name</flux:label>
                    <flux:input wire:model="name" placeholder="Labor Category Entry Name" />
                    <flux:error name="name" />
                </flux:field>

                <flux:button
                    variant="primary"
                    wire:click="executeRequest"
                    :loading="false"
                    :disabled="!$isAuthenticated || $isLoading"
                    class="disabled:opacity-50"
                >
                    <span wire:loading.remove>Execute Request</span>
                    <span wire:loading>Loading...</span>
                </flux:button>

                @if (! $isAuthenticated)
                    <flux:error name="isAuthenticated">
                        Please authenticate first using the credentials form above.
                    </flux:error>
                @endif
            </div>
        </section>

        <!-- Documentation Section -->
        <div class="space-y-4">
            <flux:heading size="md">Endpoint Information</flux:heading>

            <!-- Form Mode Documentation -->
            <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                <flux:heading size="sm" class="mb-2">About This Endpoint</flux:heading>
                <ul class="list-inside list-disc space-y-1 text-sm">
                    <li>Retrieves Labor Category Entries from WFM</li>
                    <li>Enter a Labor Category Entry Name to retrieve its data</li>
                </ul>
            </div>

            <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                <flux:heading size="sm" class="mb-2 text-blue-800 dark:text-blue-200">
                    <flux:icon.information-circle class="mr-1 inline h-4 w-4" />
                    Response Format
                </flux:heading>
                <ul class="list-inside list-disc space-y-1 text-sm text-blue-700 dark:text-blue-300">
                    <li>Each entry includes ID, name, description, and Labor Category it belongs to</li>
                    <li>Status codes: 200 (success), 401 (unauthorized)</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Response Section -->
    <x-api-response :response="$apiResponse" :error="$errorMessage" :raw-json-cache-key="$rawJsonCacheKey" />
</div>
