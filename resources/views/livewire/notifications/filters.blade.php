<div class="space-y-6">
    {{-- Filter: Read/Unread --}}
    <div class="rounded-lg bg-white p-4 dark:bg-gray-800">
        <flux:heading level="2" size="md" class="mb-3 border-b pb-2 dark:border-gray-700">
            Filter Notifications
        </flux:heading>
        <div class="flex flex-col space-y-2">
            <x-filter-button query="filter" filter="all" :is-active="$currentFilter === 'all'">
                <x-slot:icon>
                    <flux:icon.inbox-stack class="size-5" variant="solid" />
                </x-slot>
                All Notifications
            </x-filter-button>
            <x-filter-button query="filter" filter="unread" :is-active="$currentFilter === 'unread'">
                <x-slot:icon>
                    <flux:icon.envelope class="size-5" variant="solid" />
                </x-slot>
                Unread
            </x-filter-button>
            <x-filter-button query="filter" filter="read" :is-active="$currentFilter === 'read'">
                <x-slot:icon>
                    <flux:icon.envelope-open class="size-5" variant="solid" />
                </x-slot>
                Read
            </x-filter-button>
        </div>
    </div>

    {{-- Filter: Severity Levels --}}
    <div class="rounded-lg bg-white p-4 dark:bg-gray-800">
        <flux:heading level="2" size="md" class="mb-3 border-b pb-2 dark:border-gray-700">Severity Levels</flux:heading>
        <div class="flex flex-col space-y-2">
            <x-filter-button query="status" filter="all" :is-active="$currentStatus === 'all'">
                <x-slot:icon>
                    <flux:icon.chart-pie class="size-5" variant="solid" />
                </x-slot>
                All Levels
            </x-filter-button>
            <x-filter-button query="status" filter="critical" :is-active="$currentStatus === 'critical'">
                <x-slot:icon>
                    <flux:icon.exclamation-circle class="size-5 text-red-500" variant="solid" />
                </x-slot>
                Critical
            </x-filter-button>
            <x-filter-button query="status" filter="warning" :is-active="$currentStatus === 'warning'">
                <x-slot:icon>
                    <flux:icon.exclamation-triangle class="size-5 text-orange-500" variant="solid" />
                </x-slot>
                Warning
            </x-filter-button>
            <x-filter-button query="status" filter="info" :is-active="$currentStatus === 'info'">
                <x-slot:icon>
                    <flux:icon.information-circle class="size-5 text-blue-500" variant="solid" />
                </x-slot>
                Info
            </x-filter-button>
            <x-filter-button query="status" filter="notification" :is-active="$currentStatus === 'notification'">
                <x-slot:icon>
                    <flux:icon.bell class="size-5 text-gray-500" variant="solid" />
                </x-slot>
                General
            </x-filter-button>
        </div>
    </div>

    {{-- Actions --}}
    @if (auth()->user()->notifications->count() > 0)
        <livewire:notifications.actions />
    @endif
</div>
