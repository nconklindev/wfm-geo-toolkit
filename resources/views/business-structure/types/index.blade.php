<x-layouts.app :title="__('Manage Types')">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="mb-6">
            <flux:heading level="1" size="xl">
                {{ __('Manage Your Types') }}
            </flux:heading>
            <flux:text>Easily manage all of your created Business Structure Types here.</flux:text>
        </div>
        @if (session('customError'))
            <flux:callout
                variant="danger"
                inline
                x-data="{ visible: true }"
                x-show="visible"
                class="mb-4"
                x-transition:enter="transition duration-150 ease-out"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition duration-150 ease-in"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <flux:callout.heading icon="x-circle">{{ __('Something went wrong.') }}</flux:callout.heading>

                <flux:callout.text>{{ session('customError') }}</flux:callout.text>
                <x-slot name="controls">
                    <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
                </x-slot>
            </flux:callout>
        @endif

        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-zinc-800">
            <div class="bg-white p-6 dark:bg-zinc-800">
                <div class="mb-6 flex items-center justify-between">
                    <flux:heading level="2" size="lg">Types List</flux:heading>
                    <flux:button
                        type="button"
                        variant="primary"
                        icon="plus"
                        href="{{ route('business-structure.types.create') }}"
                        class="cursor-pointer text-xs font-semibold tracking-widest uppercase transition-colors duration-150 ease-in-out"
                    >
                        {{ __('Create Type') }}
                    </flux:button>
                </div>

                @if ($types->isEmpty())
                    <div class="py-8 text-center">
                        <p class="text-zinc-500">{{ __('You haven\'t created any types yet.') }}</p>
                        <p class="mt-2 text-zinc-500">{{ __('Get started by creating your first type.') }}</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                    >
                                        {{ __('Name') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                    >
                                        {{ __('Description') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                    >
                                        {{ __('Order') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                    >
                                        {{ __('Color') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                    >
                                        {{ __('Created At') }}
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                    >
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 bg-white dark:bg-zinc-800">
                                @foreach ($types as $type)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ $type->name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-zinc-400 dark:text-zinc-400">
                                                {{ $type->description }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-zinc-400 dark:text-zinc-400">
                                                {{ $type->order }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-zinc-400 dark:text-zinc-400">
                                                @if ($type->color)
                                                    <div
                                                        class="size-6 rounded-full border border-zinc-400"
                                                        style="background: {{ $type->color }}"
                                                    ></div>
                                                @else
                                                    <span>N/A</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-zinc-400">
                                                {{ $type->created_at->format('M d, Y') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                            <div class="flex space-x-3">
                                                <x-table-actions
                                                    :model="$type"
                                                    :show-view="false"
                                                    resource-name="business-structure.types"
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
