<div class="@container">
    {{-- Success/Error Messages --}}
    @if (session('success'))
        <flux:callout
            variant="success"
            icon="check-circle"
            icon:variant="solid"
            class="m-4 p-1 text-sm"
            role="alert"
            dismissible
            inline
            x-data="{ visible: true }"
            x-show="visible"
        >
            <flux:callout.heading class="@max-md:flex-col flex items-start gap-2">
                {{ session('success') ? 'Success! ' . session('success') : 'Success! Operation completed' }}
            </flux:callout.heading>

            <x-slot name="controls">
                <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
            </x-slot>
        </flux:callout>
    @endif

    @if (session('error'))
        <flux:callout
            variant="error"
            icon="exclamation-circle"
            icon:variant="solid"
            class="m-4 p-1 text-sm"
            role="alert"
            dismissible
            inline
            x-data="{ visible: true }"
            x-show="visible"
        >
            <flux:callout.heading class="@max-md:flex-col flex items-start gap-2">
                {{ session('error') ? 'Error! ' . session('error') : 'Error! An error occurred' }}
            </flux:callout.heading>

            <x-slot name="controls">
                <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
            </x-slot>
        </flux:callout>
    @endif

    <div class="flex flex-row justify-between">
        <flux:heading level="1" size="xl" class="mb-4">Notification Center</flux:heading>
        <flux:button icon="rotate-ccw" wire:click="refreshNotifications" class="cursor-pointer"></flux:button>
    </div>

    <div class="@max-3xl:flex @max-3xl:flex-col grid grid-cols-1 gap-6 lg:grid-cols-5">
        <!-- Filters sidebar -->
        <livewire:notifications.filters :current-filter="$this->filter" :current-status="$this->status" />

        <!-- Notification List -->
        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-lg bg-white dark:bg-zinc-800">
                {{-- Header for the list --}}
                <div class="border-b border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <div>
                            @if ($this->status && $this->status !== 'all')
                                <flux:text size="sm" variant="subtle">Filtered by Type: {{ $this->status }}</flux:text>
                            @endif

                            <flux:heading level="2" size="md">
                                @if ($this->filter === 'read')
                                    Read Notifications
                                @elseif ($this->filter === 'unread')
                                    Unread Notifications
                                @else
                                    All Notifications
                                @endif
                            </flux:heading>
                        </div>

                        <div class="flex items-center gap-3">
                            {{-- Items per page selector --}}

                            <flux:text size="sm" variant="subtle">Show:</flux:text>
                            {{--
                                wire:cloak prevents the select from flickering and showing 10 first
                                since it's ordered first, but our default is 15
                            --}}
                            <flux:select size="sm" wire:model.live="perPage" wire:cloak>
                                <flux:select.option value="10">10</flux:select.option>
                                <flux:select.option value="15">15</flux:select.option>
                                <flux:select.option value="25">25</flux:select.option>
                                <flux:select.option value="50">50</flux:select.option>
                            </flux:select>

                            {{-- Sort order --}}
                            <flux:select size="sm" wire:model.live="sortOrder" class="min-w-40">
                                <flux:select.option value="newest">Newest First</flux:select.option>
                                <flux:select.option value="oldest">Oldest First</flux:select.option>
                            </flux:select>
                        </div>
                    </div>
                </div>

                {{-- Notification List --}}
                <div class="max-h-[60vh] overflow-y-auto">
                    @forelse ($this->notifications as $notification)
                        <x-notification.card
                            :notification="$notification"
                            wire:key="notification-{{ $notification->id }}"
                            wire:click="selectNotification('{{ $notification->id }}')"
                            wire:navigate
                            @class([
                                'bg-sky-50 dark:bg-sky-900/50 border-l-4 border-sky-500' => $selectedNotificationId === $notification->id,
                                'border-l-4 border-transparent' => $selectedNotificationId !== $notification->id,
                            ])
                        />
                    @empty
                        <div class="flex flex-col items-center justify-center p-12 text-center">
                            @if ($this->filter !== 'all' || $this->status !== 'all')
                                <flux:icon.bell-slash class="size-10 text-zinc-400 dark:text-zinc-600" />
                                <flux:text variant="subtle" size="lg" class="mt-4">
                                    No notifications match the current filters.
                                </flux:text>
                                <flux:text variant="subtle" class="mt-2">
                                    Try adjusting or clearing the filters.
                                </flux:text>
                            @else
                                <flux:icon.bell class="size-10 text-zinc-400 dark:text-zinc-600" />
                                <flux:text variant="subtle" size="lg" class="mt-4">No notifications found.</flux:text>
                            @endif
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if ($this->notifications->hasPages())
                    <div class="border-t border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <flux:text size="sm" variant="subtle">
                                    Showing {{ $this->notifications->firstItem() }} to
                                    {{ $this->notifications->lastItem() }} of {{ $this->notifications->total() }}
                                    notifications
                                </flux:text>
                            </div>

                            <div class="flex items-center gap-1">
                                {{-- Previous button --}}
                                @if ($this->notifications->onFirstPage())
                                    <flux:button size="sm" variant="ghost" icon="chevron-left" disabled>
                                        Previous
                                    </flux:button>
                                @else
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="chevron-left"
                                        wire:click="previousPage"
                                    >
                                        Previous
                                    </flux:button>
                                @endif

                                {{-- Page numbers --}}
                                @foreach ($this->notifications->getUrlRange(1, $this->notifications->lastPage()) as $page => $url)
                                    @if ($page == $this->notifications->currentPage())
                                        <flux:button size="sm" variant="primary">{{ $page }}</flux:button>
                                    @else
                                        <flux:button size="sm" variant="ghost" wire:click="gotoPage({{ $page }})">
                                            {{ $page }}
                                        </flux:button>
                                    @endif
                                @endforeach

                                {{-- Next button --}}
                                @if ($this->notifications->hasMorePages())
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon-trailing="chevron-right"
                                        wire:click="nextPage"
                                    >
                                        Next
                                    </flux:button>
                                @else
                                    <flux:button size="sm" variant="ghost" icon-trailing="chevron-right" disabled>
                                        Next
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Notification Details (Column 3) -->
        <div class="lg:col-span-2">
            @if ($selectedNotificationData)
                <x-notification.details :details="$selectedNotificationData" />
            @else
                <div
                    class="flex h-64 items-center justify-center rounded-lg border-2 border-dashed border-zinc-300 bg-white p-6 text-center text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800"
                >
                    Select a notification from the list to view its details.
                </div>
            @endif
        </div>
    </div>
    @push('scripts')
        @vite(['resources/js/echo.js'])
    @endpush
</div>
