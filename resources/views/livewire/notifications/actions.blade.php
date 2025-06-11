<div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
    <flux:heading level="2" size="md" class="mb-3 border-b pb-2 dark:border-gray-700">Actions</flux:heading>
    <div class="flex flex-col space-y-2">
        <form wire:submit="markAllAsRead">
            <flux:button
                type="submit"
                icon="eye"
                class="flex w-full cursor-pointer items-center justify-start rounded-md p-2 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
            >
                Mark All as Read
            </flux:button>
        </form>
        <flux:modal.trigger name="delete-all-notifications">
            <flux:button
                variant="danger"
                icon="trash"
                class="flex w-full cursor-pointer items-center justify-start rounded-md p-2 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
            >
                Delete All
            </flux:button>
        </flux:modal.trigger>
        <flux:modal name="delete-all-notifications">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Delete all notifications?</flux:heading>
                </div>
                <flux:text class="space-y-1">
                    <p>You are about to delete all your notifications.</p>
                    <p>This action cannot be reversed</p>
                </flux:text>
                <div class="flex flex-row items-center justify-end space-x-2">
                    <flux:button>Cancel</flux:button>
                    <flux:button variant="danger" wire:click="deleteAllNotifications">Delete</flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
</div>
