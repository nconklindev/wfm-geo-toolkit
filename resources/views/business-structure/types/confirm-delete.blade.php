<x-layouts.app :title="__('Confirm Deletion') . ' ' . $type->name">
    <div class="overflow-hidden">
        <div class="bg-white p-6 shadow-sm sm:rounded-lg dark:bg-zinc-800">
            <flux:heading size="xl">{{ __('Confirm Deletion and Reassignment') }}</flux:heading>

            {{-- Warning Callout --}}
            <flux:callout class="mt-4" variant="warning">
                <flux:callout.heading size="sm" class="mb-1" icon="exclamation-triangle">
                    {{ __('Warning: Type In Use') }}
                </flux:callout.heading>
                <flux:callout.text>
                    {{ __('The Business Structure Type') }}
                    <strong>"{{ $type->name }}"</strong>
                    {{ __('is currently assigned to the following') }}
                    <strong>{{ $usageCount }}</strong>
                    {{ trans_choice('location|locations', $usageCount) }} {{ __('you own.') }}
                    {{ __('To delete this type, you must first reassign these locations to another type.') }}
                </flux:callout.text>
            </flux:callout>

            {{-- List of affected locations --}}
            <div class="mt-6">
                <flux:heading size="md">{{ __('Locations Currently Using This Type') }}</flux:heading>
                @if ($nodesUsingType->isNotEmpty())
                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
                        @foreach ($nodesUsingType as $node)
                            <li>
                                <a
                                    href="{{ route('business-structure.locations.show', $node) }}"
                                    class="text-primary-600 dark:text-primary-400 hover:underline"
                                >
                                    {{ $node->name ?? __('Unnamed Location') }} (ID: {{ $node->id }})
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    {{-- This shouldn't normally be reached if usageCount > 0 --}}
                    <p class="mt-2 text-sm text-zinc-500">{{ __('No specific locations found using this type.') }}</p>
                @endif
            </div>

            {{-- Reassignment Form --}}
            <form
                method="POST"
                action="{{ route('business-structure.types.reassign-and-delete', $type) }}"
                class="mt-6 space-y-6"
            >
                {{-- Corrected Action --}}
                @csrf
                @method('DELETE')

                {{-- Constrain width of the select dropdown container --}}
                <div class="max-w-md">
                    {{-- Added container with max-width --}}
                    <flux:select
                        id="replacement_type_id"
                        name="replacement_type_id"
                        label="{{ __('Reassign the locations listed above to') }}"
                        {{-- Updated Label --}}
                        badge="{{ __('Required') }}"
                        required
                    >
                        <option value="" disabled selected>{{ __('Select a replacement type...') }}</option>
                        @forelse ($replacementTypes as $replacementType)
                            <option
                                value="{{ $replacementType->id }}"
                                @selected(old('replacement_type_id') == $replacementType->id)
                            >
                                {{ $replacementType->name }} (Order: {{ $replacementType->order }})
                            </option>
                        @empty
                            <option value="" disabled>{{ __('No other types available for reassignment.') }}</option>
                        @endforelse
                    </flux:select>
                    @error('replacement_type_id')
                        {{-- Assuming flux:text exists for errors, otherwise use a simple <p> tag --}}
                        <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                    @enderror
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-x-4 border-t border-gray-200 pt-6 dark:border-gray-700">
                    <flux:button tag="a" href="{{ route('business-structure.types.index') }}" variant="filled">
                        {{ __('Cancel') }}
                    </flux:button>

                    <flux:button
                        type="submit"
                        variant="danger"
                        class="cursor-pointer"
                        {{-- Use Blade's @disabled directive --}}
                        :disabled="$replacementTypes->isEmpty()"
                        {{-- Corrected :disabled syntax --}}
                    >
                        {{ __('Reassign and Delete') }} "{{ $type->name }}"
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
