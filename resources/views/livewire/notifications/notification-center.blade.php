<div>
    {{-- Success/Error Messages --}}
    @if (session('success'))
        <flux:callout
            variant="success"
            :heading="session('success') ? 'Success! ' . session('success') : 'Success! Operation completed'"
            icon="check-circle"
            icon:variant="solid"
            class="m-4 p-1 text-sm"
            role="alert"
        />
    @endif

    @if (session('error'))
        <flux:callout
            variant="error"
            :heading="session('error') ? 'Error! ' . session('error') : 'Error! An error occurred'"
            icon="exclamation-circle"
            icon:variant="solid"
            class="m-4 p-1 text-sm"
            role="alert"
        />
    @endif

    <div class="flex flex-row justify-between">
        <flux:heading level="1" size="xl" class="mb-4">Notification Center</flux:heading>
        <flux:button icon="rotate-ccw" wire:click="refreshNotifications" class="cursor-pointer"></flux:button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <!-- Sidebar for filters (Column 1) -->
        <livewire:notifications.filters :current-filter="$this->filter" :current-status="$this->status" />

        <!-- Notification List (Column 2) -->
        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800">
                {{-- Header for the list --}}
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
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

                        <div>
                            <flux:select size="sm" wire:model.live="sortOrder">
                                <flux:select.option value="newest">Newest First</flux:select.option>
                                <flux:select.option value="oldest">Oldest First</flux:select.option>
                            </flux:select>
                        </div>
                    </div>
                </div>

                {{-- Notification List --}}
                <div class="max-h-[70vh] overflow-y-auto">
                    @forelse ($this->notifications as $notification)
                        <x-notification.card
                            :notification="$notification"
                            wire:key="notification-{{ $notification->id }}"
                            wire:click="selectNotification('{{ $notification->id }}')"
                            wire:navigate
                            @class([
                                'bg-teal-50 dark:bg-teal-900/50 border-l-4 border-teal-500' => $selectedNotificationId === $notification->id,
                                'border-l-4 border-transparent' => $selectedNotificationId !== $notification->id,
                            ])
                        />
                    @empty
                        <div class="flex flex-col items-center justify-center p-12 text-center">
                            @if ($this->filter !== 'all' || $this->status !== 'all')
                                <flux:icon.bell-slash class="size-10 text-gray-400 dark:text-gray-600" />
                                <flux:text variant="subtle" size="lg" class="mt-4">
                                    No notifications match the current filters.
                                </flux:text>
                                <flux:text variant="subtle" class="mt-2">
                                    Try adjusting or clearing the filters.
                                </flux:text>
                            @else
                                <flux:icon.bell class="size-10 text-gray-400 dark:text-gray-600" />
                                <flux:text variant="subtle" size="lg" class="mt-4">No notifications found.</flux:text>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Notification Details (Column 3) -->
        <div class="lg:col-span-2">
            @if ($selectedNotificationData)
                <x-notification.details :details="$selectedNotificationData" />
            @else
                <div
                    class="flex h-64 items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-white p-6 text-center text-gray-500 dark:border-gray-700 dark:bg-gray-800"
                >
                    Select a notification from the list to view its details.
                </div>
            @endif
        </div>
    </div>
</div>
