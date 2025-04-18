<x-layouts.app :title="__('Create Types')">
    <div class="overflow-hidden">
        <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-zinc-800">
            <div class="mb-6">
                <flux:heading size="xl">{{ __('Create Business Structure Type') }}</flux:heading>
                <flux:text variant="subtle">
                    {{ __('Use the form below to create new Business Structure Types.') }}
                </flux:text>
            </div>

            <form method="POST" action="{{ route('business-structure.types.store') }}" class="space-y-6">
                @csrf

                <div class="flex flex-col gap-4 md:max-w-lg">
                    <div class="flex flex-col items-start gap-4 md:grid md:grid-cols-2">
                        <!-- Name -->
                        <flux:input
                            type="text"
                            name="name"
                            label="Name"
                            tabindex="0"
                            badge="Required"
                            autocomplete="false"
                            autofocus
                            required
                        />
                        
                        <!-- Order -->
                        <flux:input
                            type="number"
                            name="order"
                            label="Order"
                            id="order"
                            min="1"
                            max="9999"
                            tabindex="0"
                            badge="Required"
                            required
                        />
                    </div>

                    <flux:textarea name="description" label="Description" rows="auto" tabindex="0" badge="Optional" />
                    <div id="color-container" class="flex flex-col">
                        <flux:input
                            name="color"
                            type="text"
                            label="Color"
                            id="color-picker"
                            tabindex="0"
                            badge="Optional"
                            data-color
                        />
                    </div>

                    <flux:button
                        type="submit"
                        variant="primary"
                        class="mt-4 w-full cursor-pointer place-self-end md:w-auto"
                    >
                        Submit
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
