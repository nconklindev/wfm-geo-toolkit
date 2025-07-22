<div class="space-y-6">
    <!-- Endpoint Header -->
    <x-api-endpoint-header
        heading="Retrieve Paginated List of Labor Category Entries"
        method="POST"
        wfm-endpoint="/api/v1/commons/labor_entries/apply_read"
    />

    <!-- Form Content -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Input Section -->
        <section class="space-y-4">
            <!-- Multi-Select for Labor Categories -->
            <div class="space-y-2">
                <flux:field>
                    <flux:label>Select Labor Categories</flux:label>
                    <div class="relative">
                        <select
                            wire:model.live="selectedLaborCategories"
                            multiple
                            class="{{ ! $isAuthenticated || empty($laborCategories) ? 'opacity-50' : '' }} min-h-[120px] w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                            {{ ! $isAuthenticated || empty($laborCategories) ? 'disabled' : '' }}
                        >
                            @if ($isAuthenticated && ! empty($laborCategories))
                                @foreach ($laborCategories as $category)
                                    <option
                                        value="{{ $category['id'] }}"
                                        wire:key="labor-category-{{ $category['id'] }}"
                                        class="selected:bg-blue-700/50"
                                    >
                                        {{ $category['name'] }}
                                    </option>
                                @endforeach
                            @else
                                <option disabled class="text-wrap">
                                    @if (! $isAuthenticated)
                                        Please authenticate to load categories.
                                        If you were previously authenticated,
                                        your session may have expired and you will need to re-authenticate.
                                    @elseif (empty($laborCategories))
                                        No categories available - check authentication
                                    @endif
                                </option>
                            @endif
                        </select>
                    </div>
                    <flux:description class="text-xs">
                        @if ($isAuthenticated && ! empty($laborCategories))
                            Hold Ctrl (Windows) or Cmd (Mac) to select multiple categories. Leave empty to retrieve all
                            entries.
                        @elseif (! $isAuthenticated)
                            Authentication required to load labor categories
                        @else
                                Unable to load categories - re-authentication may be required
                        @endif
                    </flux:description>
                </flux:field>

                <!-- Selected Categories Display -->
                @if (! empty($selectedLaborCategories))
                    <div class="mt-2">
                        <flux:label>Selected Categories:</flux:label>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            @foreach ($selectedLaborCategories as $selectedId)
                                @php
                                    $selectedCategory = collect($laborCategories)->firstWhere('id', $selectedId);
                                @endphp

                                @if ($selectedCategory)
                                    <span
                                        wire:key="selected-labor-category-{{ $selectedId }}"
                                        class="inline-flex items-center gap-1 rounded-md bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-700/50 dark:text-zinc-300"
                                    >
                                        {{ $selectedCategory['name'] }}
                                        <button
                                            wire:click="removeCategory('{{ $selectedId }}')"
                                            class="ml-1 inline-flex cursor-pointer text-blue-500 hover:text-blue-700"
                                        >
                                            <flux:icon.x-mark class="h-4 w-4" />
                                        </button>
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
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
                    <flux:error>Please authenticate first using the credentials form above.</flux:error>
                @elseif ($isAuthenticated && empty($laborCategories))
                    <flux:error>
                        <flux:icon.exclamation-triangle class="mr-2 inline h-4 w-4" />
                        Labor categories could not be loaded. Your session may have expired. Please re-enter your
                        credentials above.
                    </flux:error>
                @endif
            </div>
        </section>

        <!-- Documentation Section -->
        <div class="space-y-4">
            <flux:heading size="md">Endpoint Information</flux:heading>

            <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                <flux:heading size="sm" class="mb-2">About This Endpoint</flux:heading>
                <ul class="list-inside list-disc space-y-1 text-sm">
                    <li>Retrieves labor category entries from the system</li>
                    <li>Select specific Labor Categories to filter results, or leave empty to retrieve all entries</li>
                    <li>Returns the matching Labor Category Entries from the system</li>
                </ul>
            </div>

            <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                <flux:heading size="sm" class="mb-2 text-blue-800 dark:text-blue-200">
                    <flux:icon.information-circle class="mr-1 inline h-4 w-4" />
                    Performance Tips
                </flux:heading>
                <ul class="list-inside list-disc space-y-1 text-sm text-blue-700 dark:text-blue-300">
                    <li>Selecting fewer categories improves loading time</li>
                    <li>Use search to filter large result sets</li>
                    <li>Large datasets (50k+ records) may take longer to load</li>
                </ul>
            </div>

            @if ($isAuthenticated && empty($laborCategories))
                <div class="rounded-lg bg-amber-50 p-4 dark:bg-amber-900/20">
                    <flux:heading size="sm" class="mb-2 text-amber-800 dark:text-amber-200">
                        <flux:icon.exclamation-triangle class="mr-1 inline h-4 w-4" />
                        Authentication Notice
                    </flux:heading>
                    <p class="text-sm text-amber-700 dark:text-amber-300">
                        If the labor categories selector appears empty but you're authenticated, your session may have
                        expired. Please re-enter your credentials in the authentication form above to reload the
                        categories.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Enhanced Data Table -->
    @if (! empty($tableColumns) && $totalRecords > 0)
        <x-api-data-table
            :paginated-data="$paginatedData"
            :columns="$tableColumns"
            title="Labor Category Entries"
            :total-records="$totalRecords"
            :search="$search"
            :sort-field="$sortField"
            :sort-direction="$sortDirection"
            :per-page="$perPage"
        />
    @endif

    <!-- Response Section (Raw JSON) -->
    <x-api-response :response="$apiResponse" :error="$errorMessage" :raw-json-cache-key="$rawJsonCacheKey" />
</div>
