@props([
    'paginatedData' => null,
    'title' => 'Adjustment Rules',
    'totalRecords' => 0,
    'showBorder' => true,
    'search' => '',
    'sortField' => '',
    'sortDirection' => 'asc',
    'perPage' => 15,
])

<div
    @class([
        'border-t border-zinc-200 pt-6 dark:border-zinc-700' => $showBorder,
        'space-y-4',
    ])
    x-data="{
        expandedRules: {},
        expandAll: false,
        toggleRule(ruleId) {
            this.expandedRules[ruleId] = ! this.expandedRules[ruleId]
        },
        toggleAll() {
            this.expandAll = ! this.expandAll
            Object.keys(this.expandedRules).forEach((key) => {
                this.expandedRules[key] = this.expandAll
            })
        },
    }"
>
    <!-- Header -->
    <div class="flex flex-col space-y-3 md:flex-row md:items-center md:justify-between md:space-y-0">
        <flux:heading size="md">{{ $title }} ({{ $totalRecords }} total)</flux:heading>

        <div class="flex flex-row items-center space-y-0 space-x-2">
            <flux:button @click="toggleAll()" variant="ghost" size="sm" class="w-full sm:w-auto">
                <span x-text="expandAll ? 'Collapse All' : 'Expand All'"></span>
            </flux:button>

            <div class="flex flex-row space-x-2">
                <flux:button
                    wire:click="exportToCsv"
                    variant="subtle"
                    size="sm"
                    icon="arrow-down-tray"
                    class="flex-1 sm:flex-none"
                >
                    <span class="hidden sm:inline">Export (CSV)</span>
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Search and Controls -->
    <div class="flex items-center justify-between space-x-4">
        <div class="max-w-md flex-1 text-sm md:text-base">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search adjustment rules..."
                class:input="text-sm md:text-base"
                icon="magnifying-glass"
            />
        </div>

        <div class="flex items-center space-x-2">
            <flux:text size="sm" variant="subtle">Show:</flux:text>
            <select
                wire:model.live="perPage"
                class="rounded-md border border-zinc-300 px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800"
            >
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>

    <!-- Rules List -->
    <div class="space-y-4">
        @if ($paginatedData && $paginatedData->count() > 0)
            @foreach ($paginatedData->items() as $rule)
                @php
                    $ruleVersions = $rule['ruleVersions']['adjustmentRuleVersion'] ?? [];
                    $firstVersion = $ruleVersions[0] ?? null;
                    $newestVersion = end($ruleVersions);
                    $ruleId = $rule['id'] ?? '';
                @endphp

                <div
                    class="rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
                    x-init="expandedRules['{{ $ruleId }}'] = false"
                >
                    <!-- Rule Header -->
                    <div
                        class="flex cursor-pointer items-center justify-between p-4 hover:bg-zinc-50 dark:hover:bg-zinc-700/50"
                        @click="toggleRule('{{ $ruleId }}')"
                    >
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center space-x-3">
                                <flux:icon.chevron-right
                                    class="h-4 w-4 text-zinc-400 transition-transform"
                                    x-bind:class="{ 'rotate-90': expandedRules['{{ $ruleId }}'] }"
                                />
                                <div>
                                    <flux:heading size="sm" class="font-medium">
                                        {{ $rule['name'] ?? 'Unnamed Rule' }}
                                    </flux:heading>
                                    @if ($newestVersion)
                                        <flux:text size="sm" variant="subtle">
                                            {{ $newestVersion['description'] ?? 'No description' }}
                                        </flux:text>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div
                            class="hidden content-start items-center space-x-4 text-sm text-zinc-600 md:flex dark:text-zinc-400"
                        >
                            @if ($newestVersion)
                                <div class="text-center">
                                    <div class="text-xs font-medium">Effective</div>
                                    <div>{{ $newestVersion['effectiveDate'] ?? '-' }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-xs font-medium">Expires</div>
                                    <div>{{ $newestVersion['expirationDate'] ?? '-' }}</div>
                                </div>
                            @endif
                            
                            <div class="text-center">
                                <div class="text-xs font-medium">Versions</div>
                                <div>{{ count($ruleVersions) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Expanded Content -->
                    <div
                        x-show="expandedRules['{{ $ruleId }}']"
                        x-transition:enter="transition duration-200 ease-out"
                        x-transition:enter-start="scale-95 transform opacity-0"
                        x-transition:enter-end="scale-100 transform opacity-100"
                        x-transition:leave="transition duration-150 ease-in"
                        x-transition:leave-start="scale-100 transform opacity-100"
                        x-transition:leave-end="scale-95 transform opacity-0"
                        class="border-t border-zinc-200 dark:border-zinc-700"
                    >
                        <div class="space-y-6 p-4">
                            <!-- Rule Versions -->
                            @foreach ($ruleVersions as $version)
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <flux:heading size="sm" class="text-zinc-800 dark:text-zinc-200">
                                            Version {{ $version['versionId'] ?? 'Unknown' }}
                                        </flux:heading>
                                        <div
                                            class="flex items-center space-x-4 text-sm text-zinc-600 dark:text-zinc-400"
                                        >
                                            <span>{{ $version['effectiveDate'] ?? '-' }}</span>
                                            <span><flux:icon.arrow-long-right class="size-4" /></span>
                                            <span>{{ $version['expirationDate'] ?? '-' }}</span>
                                        </div>
                                    </div>

                                    @if (! empty($version['description']))
                                        <flux:text size="sm" variant="subtle">
                                            {{ $version['description'] }}
                                        </flux:text>
                                    @endif

                                    <!-- Triggers -->
                                    @php
                                        $triggers = $version['triggers']['adjustmentTriggerForRule'] ?? [];
                                    @endphp

                                    @if (! empty($triggers))
                                        <div class="space-y-3">
                                            <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300">
                                                Triggers ({{ count($triggers) }})
                                            </flux:heading>

                                            <div class="grid gap-3">
                                                @foreach ($triggers as $trigger)
                                                    <div
                                                        class="rounded-md border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-600 dark:bg-zinc-700/50"
                                                    >
                                                        <div
                                                            class="grid grid-cols-1 gap-3 text-sm md:auto-cols-fr md:grid-flow-col"
                                                        >
                                                            <!-- Job/Location -->
                                                            <div>
                                                                <div
                                                                    class="font-medium text-zinc-700 dark:text-zinc-300"
                                                                >
                                                                    Job/Location
                                                                </div>
                                                                <div class="text-zinc-600 dark:text-zinc-400">
                                                                    {{ $trigger['jobOrLocation']['qualifier'] ?? '-' }}
                                                                </div>
                                                                @if (! empty($trigger['jobOrLocationEffectiveDate']))
                                                                    <div
                                                                        class="text-xs text-zinc-500 dark:text-zinc-500"
                                                                    >
                                                                        Effective:
                                                                        {{ $trigger['jobOrLocationEffectiveDate'] }}
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <!-- Labor Category Entries -->
                                                            <div>
                                                                <div
                                                                    class="font-medium text-zinc-700 dark:text-zinc-300"
                                                                >
                                                                    Labor Category Entries
                                                                </div>
                                                                <div class="text-zinc-600 dark:text-zinc-400">
                                                                    {{ $trigger['laborCategoryEntries'] ?? '-' }}
                                                                </div>
                                                            </div>

                                                            <!-- Cost Center -->
                                                            <div>
                                                                <div
                                                                    class="font-medium text-zinc-700 dark:text-zinc-300"
                                                                >
                                                                    Cost Center:
                                                                    <div class="text-zinc-600 dark:text-zinc-400">
                                                                        {{ $trigger['costCenter'] ?? '-' }}
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Pay Codes -->
                                                            <div>
                                                                <div
                                                                    class="font-medium text-zinc-700 dark:text-zinc-300"
                                                                >
                                                                    Pay Codes
                                                                </div>
                                                                <div class="text-zinc-600 dark:text-zinc-400">
                                                                    @php
                                                                        $payCodes = $trigger['payCodes'] ?? [];
                                                                        $payCodeNames = collect($payCodes)
                                                                            ->pluck('qualifier')
                                                                            ->filter()
                                                                            ->implode(', ');
                                                                    @endphp

                                                                    {{ $payCodeNames ?: '-' }}
                                                                </div>
                                                            </div>

                                                            <!-- Allocation -->
                                                            <div>
                                                                <div
                                                                    class="font-medium text-zinc-700 dark:text-zinc-300"
                                                                >
                                                                    Allocation
                                                                </div>
                                                                @php
                                                                    $allocation = $trigger['adjustmentAllocation']['adjustmentAllocation'] ?? null;
                                                                @endphp

                                                                @if ($allocation)
                                                                    <div class="text-zinc-600 dark:text-zinc-400">
                                                                        {{ $allocation['adjustmentType'] ?? '-' }}
                                                                        @if (($allocation['adjustmentType'] ?? '') === 'Wage' && ! empty($allocation['type']))
                                                                            <span
                                                                                class="text-xs text-zinc-500 dark:text-zinc-500"
                                                                            >
                                                                                ({{ $allocation['type'] }})
                                                                            </span>
                                                                        @endif
                                                                    </div>

                                                                    @if (! empty($allocation['amount']) || ! empty($allocation['bonusRateAmount']) || ! empty($allocation['bonusRateHourlyRate']))
                                                                        <div
                                                                            class="text-xs text-zinc-500 dark:text-zinc-500"
                                                                        >
                                                                            @if (! empty($allocation['amount']))
                                                                                Amount: ${{ $allocation['amount'] }}
                                                                            @elseif (! empty($allocation['bonusRateAmount']))
                                                                                <div>
                                                                                    Bonus:
                                                                                    ${{ $allocation['bonusRateAmount'] }}
                                                                                </div>
                                                                                <div>
                                                                                    Pay Code:
                                                                                    {{ $allocation['payCode']['name'] ?? ($allocation['payCode']['qualifier'] ?? '-') }}
                                                                                </div>
                                                                            @elseif (! empty($allocation['bonusRateHourlyRate']))
                                                                                <div>
                                                                                    Bonus Hourly Rate:
                                                                                    +${{ $allocation['bonusRateHourlyRate'] }}
                                                                                </div>
                                                                                <div>
                                                                                    Pay Code:
                                                                                    {{ $allocation['payCode']['name'] ?? ($allocation['payCode']['qualifier'] ?? '-') }}
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @endif
                                                                @else
                                                                    <div class="text-zinc-600 dark:text-zinc-400">
                                                                        -
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <!-- Additional Details -->
                                                        @if (! empty($trigger['costCenter']) || ! empty($trigger['genericLocations']))
                                                            <div
                                                                class="mt-2 border-t border-zinc-200 pt-2 dark:border-zinc-600"
                                                            >
                                                                <div
                                                                    class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2"
                                                                >
                                                                    @if (! empty($trigger['costCenter']))
                                                                        <div>
                                                                            <span
                                                                                class="font-medium text-zinc-700 dark:text-zinc-300"
                                                                            >
                                                                                Cost Center:
                                                                            </span>
                                                                            <span
                                                                                class="text-zinc-600 dark:text-zinc-400"
                                                                            >
                                                                                {{ $trigger['costCenter'] }}
                                                                            </span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="py-4 text-center text-zinc-500 dark:text-zinc-400">
                                            No triggers defined for this version
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="py-8 text-center text-zinc-500 dark:text-zinc-400">
                @if (! empty($search))
                    No adjustment rules found matching "{{ $search }}"
                @else
                    No adjustment rules available
                @endif
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if ($paginatedData && $paginatedData->hasPages())
        <div class="mt-6">
            {{ $paginatedData->links(data: ['scrollTo' => false]) }}
        </div>
    @endif
</div>
