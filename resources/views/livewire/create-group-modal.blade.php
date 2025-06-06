<flux:modal name="create-group-modal" class="absolute! left-0!">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Create Group</flux:heading>
            <flux:text class="mt-2">Create a new group to organize your content.</flux:text>
        </div>

        <form wire:submit="createGroup" class="space-y-4">
            <flux:field>
                <flux:input
                    wire:model="name"
                    label="Group Name"
                    badge="Required"
                    autofocus
                    autocomplete="off"
                    placeholder="Enter group name..."
                    required
                />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:textarea
                    wire:model="description"
                    label="Description"
                    badge="Optional"
                    placeholder="Enter description..."
                    rows="3"
                />
                <flux:error name="description" />
            </flux:field>

            <div class="flex">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button type="button" variant="ghost" @disabled="isCreating">Cancel</flux:button>
                </flux:modal.close>
                <flux:button
                    type="submit"
                    variant="primary"
                    @disabled="isCreating"
                    wire:loading.attr="disabled"
                    wire:target="createGroup"
                >
                    <span wire:loading.remove wire:target="createGroup">Create Group</span>
                    <span wire:loading wire:target="createGroup">Creating...</span>
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>
