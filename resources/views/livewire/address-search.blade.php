{{-- The Leaflet map is set with a really high z-index so we need to be above it, hence the arbitrary number --}}
<div class="absolute top-4 right-0 left-0 z-[400] block rounded-md px-3">
    <flux:field>
        <flux:input
            id="address_search"
            name="address"
            wire:model="address"
            type="text"
            class="rounded-md"
            clearable
            wire:keyup.debounce.300ms.prevent="search($wire.address)"
            wire:keydown.enter.prevent="search($wire.address)"
            placeholder="1 Apple Park Way, Cupertino, CA 95014"
        />
    </flux:field>
</div>
