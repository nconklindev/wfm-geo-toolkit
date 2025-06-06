<flux:modal name="edit-known-ip-address" class="md:w-lg">
    @if ($ipAddress)
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Update Known IP Address</flux:heading>
                <flux:text class="mt-2">Make changes to {{ $ipAddress->name }}.</flux:text>
            </div>

            <form wire:submit="save" class="space-y-4">
                <flux:input wire:model="form.name" label="Name" badge="Required" required />
                <flux:textarea wire:model="form.description" label="Description" badge="Optional"></flux:textarea>
                <div class="flex flex-row justify-between space-x-4">
                    <flux:input wire:model="form.start" label="Start" badge="Required" required />
                    <flux:input wire:model="form.end" label="End" badge="Required" required />
                </div>

                <div class="flex">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">Cancel</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Update</flux:button>
                </div>
            </form>
        </div>
    @endif
</flux:modal>
