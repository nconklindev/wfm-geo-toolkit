<div class="space-y-6 lg:col-span-1" wire:cloak>
    {{-- Filter: Read/Unread --}}
    <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
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

    {{-- Filter: Issue Types --}}
    <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
        <flux:heading level="2" size="md" class="mb-3 border-b pb-2 dark:border-gray-700">Issue Types</flux:heading>
        <div class="flex flex-col space-y-2">
            <x-filter-button query="status" filter="all" :is-active="$currentStatus === 'all'">
                <x-slot:icon>
                    <flux:icon.chart-pie class="size-5" variant="solid" />
                </x-slot>
                All Types
            </x-filter-button>
            <x-filter-button
                query="status"
                filter="Possible Conflict"
                :is-active="$currentStatus === 'Possible Conflict'"
            >
                <x-slot:icon>
                    <flux:icon.exclamation-triangle class="size-5" variant="solid" />
                </x-slot>
                Possible Conflict
            </x-filter-button>
            <x-filter-button query="status" filter="Notification" :is-active="$currentStatus === 'Notification'">
                <x-slot:icon>
                    <flux:icon.information-circle class="size-5" variant="solid" />
                </x-slot>
                Information
            </x-filter-button>
        </div>
    </div>

    {{-- Actions --}}
    @if (auth()->user()->notifications->count() > 0)
        <livewire:notifications.actions />
    @endif
</div>
