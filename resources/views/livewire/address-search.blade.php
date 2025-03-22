<div class="absolute rounded-md top-4 bottom-0 left-0 right-0 px-3 z-[1000]">
    <flux:field wire:ignore>
        <flux:input id="address_search" name="address" wire:model="address" type="text"
                    class="rounded-md"
                    wire:keyup.debounce.500ms="search($event.target.value)"
                    placeholder="1 Apple Park Way, Cupertino, CA 95014"/>
    </flux:field>
</div>
