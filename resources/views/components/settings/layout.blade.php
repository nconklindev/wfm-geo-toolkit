<div class="flex items-start max-md:flex-col">
    <div class="mr-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item
                :href="route('settings.profile')"
                wire:navigate
                :wire:current="request()->routeIs('settings.profile')"
            >
                {{ __('Profile') }}
            </flux:navlist.item>
            <flux:navlist.item
                :href="route('settings.password')"
                wire:navigate
                :wire:current="request()->routeIs('settings.password')"
            >
                {{ __('Password') }}
            </flux:navlist.item>
            <flux:navlist.item
                :href="route('settings.appearance')"
                wire:navigate
                :wire:current="request()->routeIs('settings.appearance')"
            >
                {{ __('Appearance') }}
            </flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
