@props([
    'model',
    'heading' => 'Confirm delete',
])

<flux:modal.trigger name="{{ 'delete-known-place-' . $model }}">
    <flux:icon.trash
        class="h-5 w-5 cursor-pointer text-red-600 hover:text-red-800 dark:text-red-500 dark:hover:text-red-300"
    />
</flux:modal.trigger>

<flux:modal name="{{ 'delete-known-place-' . $model }}" class="min-w-[22rem]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Delete {{ $heading }}</flux:heading>

            <flux:text class="mt-2">
                <p>You are about to delete this known place.</p>
                <p>This action cannot be reversed.</p>
            </flux:text>
        </div>

        <div class="flex gap-2">
            <flux:spacer />

            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>

            <flux:button type="submit" variant="danger">Delete</flux:button>
        </div>
    </div>
</flux:modal>
