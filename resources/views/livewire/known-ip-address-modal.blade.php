<flux:modal name="create-known-ip-address" class="md:w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Create Known IP Address</flux:heading>
            <flux:text class="mt-2">Add a new known IP address to the application.</flux:text>
        </div>

        <form wire:submit="save" class="space-y-4">
            <flux:input wire:model.live.debounce.300ms="form.name" label="Name" badge="Required" required />
            <flux:textarea
                wire:model.live.debounce.300ms="form.description"
                label="Description"
                badge="Optional"
            ></flux:textarea>
            <div class="flex flex-row justify-between">
                <flux:input wire:model.live.debounce.300ms="form.start" label="Start" badge="Required" required />
                <flux:input wire:model.live.debounce.300ms="form.end" label="End" badge="Required" required />
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary" class="cursor-pointer">Create</flux:button>
            </div>
        </form>
    </div>
</flux:modal>
