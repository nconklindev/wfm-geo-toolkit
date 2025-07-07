@props([
    'paginatedData' => null,
    'columns' => [],
    'title' => 'Data Table',
    'totalRecords' => 0,
    'showBorder' => true,
    'search' => '',
    'sortField' => '',
    'sortDirection' => 'asc',
    'perPage' => 15,
])

<div @class([
    'border-t border-zinc-200 pt-6 dark:border-zinc-700' => $showBorder,
    'space-y-4',
])>
    @if (! empty($columns))
        <div class="flex items-center justify-between">
            <flux:heading size="md">{{ $title }} ({{ $totalRecords }} total)</flux:heading>

            <div class="flex items-center space-x-2">
                <flux:button wire:click="exportAllToCsv" variant="subtle" size="sm" icon="arrow-down-tray">
                    Export All (CSV)
                </flux:button>
                <flux:button wire:click="exportSelectionsToCsv" variant="subtle" size="sm" icon="arrow-down-tray">
                    Export Selections (CSV)
                </flux:button>
            </div>
        </div>

        <!-- Search and Controls -->
        <div class="flex items-center justify-between space-x-4">
            <div class="max-w-md flex-1">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search entries..."
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

        <!-- Data Table -->
        <div class="overflow-x-auto rounded-lg border border-zinc-200 shadow-md dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-900">
                    <tr>
                        @foreach ($columns as $column)
                            <th
                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-400"
                            >
                                <button
                                    wire:click="sortBy('{{ $column['field'] }}')"
                                    class="flex items-center space-x-1 hover:text-zinc-700 dark:hover:text-zinc-300"
                                >
                                    <span>{{ $column['label'] }}</span>
                                    @if ($sortField === $column['field'])
                                        <flux:icon.chevron-up
                                            class="{{ $sortDirection === 'desc' ? 'rotate-180' : '' }} h-3 w-3"
                                        />
                                    @endif
                                </button>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                    @if ($paginatedData && $paginatedData->count() > 0)
                        @foreach ($paginatedData->items() as $row)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                @foreach ($columns as $column)
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-zinc-900 dark:text-zinc-100">
                                        {{ data_get($row, $column['field'], '-') }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td
                                colspan="{{ count($columns) }}"
                                class="px-6 py-8 text-center text-zinc-500 dark:text-zinc-400"
                            >
                                @if (! empty($search))
                                    No records found matching "{{ $search }}"
                                @else
                                    No data available
                                @endif
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($paginatedData && $paginatedData->hasPages())
            {{ $paginatedData->links(data: ['scrollTo' => false]) }}
        @endif
    @else
        <div class="py-8 text-center text-zinc-500 dark:text-zinc-400">No table columns defined</div>
    @endif
</div>
