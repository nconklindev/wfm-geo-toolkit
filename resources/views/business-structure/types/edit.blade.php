<x-layouts.app :title="__('Edit Business Structure Type')">
    <div class="overflow-hidden">
        <div class="bg-white p-6 shadow-sm sm:rounded-lg dark:bg-zinc-800">
            <div class="mb-6">
                <flux:heading size="xl">{{ __('Edit Business Structure Type') }}</flux:heading>
                <flux:text variant="subtle">
                    {{ __('Use the form below to edit the specified Business Structure Type. Once submitted, the Business Structure Type will be updated.') }}
                </flux:text>
            </div>

            <form method="POST" action="{{ route('business-structure.types.update', $type) }}" class="space-y-8">
                @csrf
                @method('PATCH')

                <!-- Basic Information Section -->
                <div class="space-y-6">
                    <div class="border-b border-gray-200 pb-2 dark:border-gray-700">
                        <flux:heading size="sm">{{ __('Basic Information') }}</flux:heading>
                    </div>

                    <div class="max-w-sm">
                        <flux:input
                            id="name"
                            name="name"
                            label="{{ __('Name') }}"
                            badge="{{ __('Required') }}"
                            value="{{ old('name', $type->name) }}"
                            required
                        />
                    </div>

                    <div class="max-w-sm">
                        <flux:textarea
                            id="description"
                            name="description"
                            rows="3"
                            badge="{{ __('Optional') }}"
                            label="{{ __('Description') }}"
                        >
                            {{ old('description', $type->pivot->description ?? '') }}
                        </flux:textarea>
                    </div>
                </div>

                <!-- Display Settings Section -->
                <div class="space-y-6">
                    <div class="border-b border-gray-200 pb-2 dark:border-gray-700">
                        <flux:heading size="sm">{{ __('Display Settings') }}</flux:heading>
                    </div>

                    <div class="flex flex-col gap-6 sm:flex-row sm:items-end">
                        <div class="w-full sm:w-1/3">
                            <flux:input
                                id="hierarchy_order"
                                name="hierarchy_order"
                                type="number"
                                badge="Required"
                                label="{{ __('Hierarchy Order') }}"
                                value="{{ old('hierarchy_order', $type->hierarchy_order) }}"
                                min="1"
                                max="9999"
                                required
                            />
                        </div>

                        <div id="color-container" class="w-full sm:w-1/3">
                            <flux:input
                                name="color"
                                type="text"
                                label="{{ __('Color') }}"
                                id="color-picker"
                                tabindex="0"
                                badge="Optional"
                                data-color
                            />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-x-4 pt-4">
                    <flux:button tag="a" href="{{ route('business-structure.types.index') }}" variant="filled">
                        {{ __('Cancel') }}
                    </flux:button>

                    <flux:button type="submit" variant="primary" class="cursor-pointer">
                        {{ __('Update') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
