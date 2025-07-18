@props([
    'paginatedData' => null,
    'title' => 'Percent Allocation Rules',
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

        <div class="flex flex-col space-y-2 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-2">
            <flux:button @click="toggleAll()" variant="ghost" size="sm" class="w-full sm:w-auto">
                <span x-text="expandAll ? 'Collapse All' : 'Expand All'"></span>
            </flux:button>

            <div class="flex space-x-2">
                <flux:button
                    wire:click="exportToCsv"
                    variant="subtle"
                    size="sm"
                    icon="arrow-down-tray"
                    class="flex-1 sm:flex-none"
                >
                    <span class="hidden sm:inline">Export All (CSV)</span>
                    <span class="sm:hidden">Export All</span>
                </flux:button>
                <flux:button
                    wire:click="exportSelectionsToCsv"
                    variant="subtle"
                    size="sm"
                    icon="arrow-down-tray"
                    class="flex-1 sm:flex-none"
                >
                    <span class="hidden sm:inline">Export Selections (CSV)</span>
                    <span class="sm:hidden">Export Selections</span>
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Search and Controls -->
    <div class="flex items-center justify-between space-x-4">
        <div class="max-w-md flex-1 text-sm md:text-base">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search percent allocation rules..."
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
                    $fpaRuleVersions = $rule['fpaRuleVersions'] ?? [];
                    $firstVersion = $fpaRuleVersions[0] ?? null;
                    $newestVersion = end($fpaRuleVersions);
                    $ruleId = $rule['id'] ?? null;
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
                                    @if ($rule['id'] ?? false)
                                        <flux:text size="xs" variant="subtle">ID: {{ $rule['id'] }}</flux:text>
                                    @endif

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
                                    <div class="text-xs font-medium">Start Date</div>
                                    <div>{{ $newestVersion['startEffectiveDate'] ?? '-' }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-xs font-medium">End Date</div>
                                    <div>{{ $newestVersion['endEffectiveDate'] ?? '-' }}</div>
                                </div>
                            @endif

                            <div class="text-center">
                                <div class="text-xs font-medium">Versions</div>
                                <div>{{ count($fpaRuleVersions) }}</div>
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
                            @foreach ($fpaRuleVersions as $version)
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <flux:heading size="sm" class="text-zinc-800 dark:text-zinc-200">
                                            Version
                                        </flux:heading>
                                        <div
                                            class="flex items-center space-x-4 text-sm text-zinc-600 dark:text-zinc-400"
                                        >
                                            <span>{{ $version['startEffectiveDate'] ?? '-' }}</span>
                                            <span><flux:icon.arrow-long-right class="size-4" /></span>
                                            <span>{{ $version['endEffectiveDate'] ?? '-' }}</span>
                                        </div>
                                    </div>

                                    @if (! empty($version['description']))
                                        <flux:text size="sm" variant="subtle">
                                            {{ $version['description'] }}
                                        </flux:text>
                                    @endif

                                    <!-- Triggers -->
                                    @php
                                        $triggers = $version['triggers'] ?? [];
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
                                                            class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2 lg:grid-cols-3"
                                                        >
                                                            <!-- Job/Location -->
                                                            <div>
                                                                <div
                                                                    class="font-medium text-zinc-700 dark:text-zinc-300"
                                                                >
                                                                    Job/Location
                                                                </div>
                                                                <div class="text-zinc-600 dark:text-zinc-400">
                                                                    {{ $trigger['jobOrLocation']['qualifier'] ?? ($trigger['jobOrLocation']['name'] ?? '-') }}
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
                                                                    Labor Category
                                                                </div>
                                                                <div class="text-zinc-600 dark:text-zinc-400">
                                                                    {{ $trigger['laborCategoryEntries'] ?? '-' }}
                                                                </div>
                                                            </div>

                                                            <!-- Match Anywhere -->
                                                            <div>
                                                                <div
                                                                    class="font-medium text-zinc-700 dark:text-zinc-300"
                                                                >
                                                                    Match Anywhere
                                                                </div>
                                                                <div class="text-zinc-600 dark:text-zinc-400">
                                                                    {{ $trigger['matchAnywhere'] ?? false ? 'Yes' : 'No' }}
                                                                </div>
                                                            </div>

                                                            <!-- Pay Codes -->
                                                            <div class="md:col-span-2 lg:col-span-3">
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
                                                        </div>

                                                        <!-- Allocations -->
                                                        @php
                                                            $allocations = $trigger['allocations'] ?? [];
                                                        @endphp

                                                        @if (! empty($allocations))
                                                            <div
                                                                class="mt-4 border-t border-zinc-200 pt-3 dark:border-zinc-600"
                                                            >
                                                                <div
                                                                    class="mb-3 font-medium text-zinc-700 dark:text-zinc-300"
                                                                >
                                                                    Allocations ({{ count($allocations) }})
                                                                </div>

                                                                <div class="space-y-2">
                                                                    @foreach ($allocations as $allocation)
                                                                        <div
                                                                            class="rounded border border-zinc-300 bg-white p-2 dark:border-zinc-500 dark:bg-zinc-800"
                                                                        >
                                                                            <div
                                                                                class="grid grid-cols-1 gap-2 text-sm md:grid-cols-2 lg:grid-cols-4"
                                                                            >
                                                                                <div>
                                                                                    <span
                                                                                        class="font-medium text-zinc-700 dark:text-zinc-300"
                                                                                    >
                                                                                        Percentage:
                                                                                    </span>
                                                                                    <span
                                                                                        class="text-zinc-600 dark:text-zinc-400"
                                                                                    >
                                                                                        {{ $allocation['percentage'] ?? '0' }}%
                                                                                    </span>
                                                                                </div>

                                                                                <div>
                                                                                    <span
                                                                                        class="font-medium text-zinc-700 dark:text-zinc-300"
                                                                                    >
                                                                                        Job:
                                                                                    </span>
                                                                                    <span
                                                                                        class="text-zinc-600 dark:text-zinc-400"
                                                                                    >
                                                                                        {{ $allocation['job']['name'] ?? ($allocation['job']['qualifier'] ?? ($allocation['job']['id'] ?? '-')) }}
                                                                                    </span>
                                                                                </div>

                                                                                <div>
                                                                                    <span
                                                                                        class="font-medium text-zinc-700 dark:text-zinc-300"
                                                                                    >
                                                                                        Wage Adj. Amount:
                                                                                    </span>
                                                                                    <span
                                                                                        class="text-zinc-600 dark:text-zinc-400"
                                                                                    >
                                                                                        @if ($allocation['wageAdjustmentType'] === 2)
                                                                                            x{{ $allocation['wageAdjustmentAmount'] ?? '0.00' }}
                                                                                        @else
                                                                                            ${{ $allocation['wageAdjustmentAmount'] ?? '0.00' }}
                                                                                        @endif
                                                                                    </span>
                                                                                </div>

                                                                                <div>
                                                                                    <span
                                                                                        class="font-medium text-zinc-700 dark:text-zinc-300"
                                                                                    >
                                                                                        Wage Adj. Type:
                                                                                    </span>
                                                                                    <span
                                                                                        class="text-zinc-600 dark:text-zinc-400"
                                                                                    >
                                                                                        {{-- We have to hard-code the typeIds because they aren't easily found in documentation --}}
                                                                                        {{-- This was tested in CFN032 with each type added to a trigger and viewed in the response --}}

                                                                                        @switch($allocation['wageAdjustmentType'])
                                                                                            @case('1')
                                                                                                Addition

                                                                                                @break
                                                                                            @case('2')
                                                                                                Multiplier

                                                                                                @break
                                                                                            @case('3')
                                                                                                Flat Rate

                                                                                                @break
                                                                                            @case('4')
                                                                                                None

                                                                                                @break
                                                                                            @default
                                                                                                None

                                                                                                @break
                                                                                        @endswitch
                                                                                    </span>
                                                                                </div>
                                                                            </div>

                                                                            @if (! empty($allocation['laborCategoryEntries']))
                                                                                <div
                                                                                    class="mt-2 text-sm text-zinc-600 dark:text-zinc-400"
                                                                                >
                                                                                    <span
                                                                                        class="font-medium text-zinc-700 dark:text-zinc-300"
                                                                                    >
                                                                                        Labor Category:
                                                                                    </span>
                                                                                    {{ $allocation['laborCategoryEntries'] }}
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
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
                    No percent allocation rules found matching "{{ $search }}"
                @else
                    No percent allocation rules available
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
