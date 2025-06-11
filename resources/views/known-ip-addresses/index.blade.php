<x-layouts.app :title="__('Known IP Addresses')">
    <flux:heading level="1" size="xl">Known IP Addresses</flux:heading>
    <flux:text variant="subtle">Your IP Address: {{ request()->ip() }}</flux:text>

    <div class="flex flex-row items-center justify-end space-x-8">
        {{-- Add Action --}}
        <div
            class="group flex cursor-pointer flex-col items-center space-y-1 align-middle transition-colors duration-150"
        >
            <flux:modal.trigger class="flex cursor-pointer flex-col items-center" name="create-known-ip-address">
                <flux:button variant="primary" icon="plus-circle" class="cursor-pointer">Create</flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    <div
        class="w-rounded-md mt-10 overflow-auto rounded-md border border-zinc-400 bg-zinc-900 p-4 dark:border-zinc-700"
    >
        <div class="mb-4 flex flex-col items-end gap-4 md:flex-row">
            <div class="flex-grow">
                {{-- TODO: Make a Livewire search --}}
                <flux:input
                    type="search"
                    name="search"
                    placeholder="Search by name, description or IP address..."
                    wire:model.live.debounce.300ms="search"
                />
            </div>
            <div class="flex gap-2">
                <flux:button variant="primary" type="button">Search</flux:button>
                <flux:button variant="filled" type="button" @click="search = ''">Clear</flux:button>
            </div>
        </div>

        <table class="min-w-full divide-y divide-zinc-400 overflow-y-scroll dark:divide-zinc-700">
            <thead class="bg-white dark:bg-zinc-800">
                <tr>
                    <th
                        scope="col"
                        class="overflow-hidden px-6 py-3 text-left text-xs font-medium tracking-wider text-ellipsis text-zinc-500 uppercase dark:text-zinc-300"
                    >
                        Name
                    </th>
                    <th
                        scope="col"
                        class="overflow-hidden px-6 py-3 text-left text-xs font-medium tracking-wider text-ellipsis text-zinc-500 uppercase dark:text-zinc-300"
                    >
                        Description
                    </th>
                    <th
                        scope="col"
                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                    >
                        Start
                    </th>
                    <th
                        scope="col"
                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                    >
                        End
                    </th>
                    <th
                        scope="col"
                        class="px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                    ></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-400 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                @forelse ($ipAddresses as $ipAddress)
                    <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-800/50">
                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-zinc-800 dark:text-white">
                            {{ $ipAddress->name }}
                        </td>
                        <td class="px-6 py-4 text-sm whitespace-nowrap text-zinc-400">
                            {{ $ipAddress->description ?? 'No description' }}
                        </td>
                        <td class="px-6 py-4 text-sm whitespace-nowrap text-zinc-400">
                            {{ $ipAddress->start }}
                        </td>
                        <td class="px-6 py-4 text-sm whitespace-nowrap text-zinc-400">
                            {{ $ipAddress->end }}
                        </td>
                        <td class="px-6 py-4 text-sm whitespace-nowrap text-zinc-400">
                            <div x-data="{ open: false }" class="relative">
                                <flux:button
                                    icon="ellipsis-horizontal"
                                    class="cursor-pointer"
                                    size="sm"
                                    variant="ghost"
                                    @click="open = !open"
                                    aria-haspopup="true"
                                    aria-expanded="open"
                                />

                                <!-- Dropdown menu -->
                                <div
                                    x-show="open"
                                    @click.away="open = false"
                                    x-transition:enter="transition duration-100 ease-out"
                                    x-transition:enter-start="scale-95 transform opacity-0"
                                    x-transition:enter-end="scale-100 transform opacity-100"
                                    x-transition:leave="transition duration-75 ease-in"
                                    x-transition:leave-start="scale-100 transform opacity-100"
                                    x-transition:leave-end="scale-95 transform opacity-0"
                                    class="ring-opacity-5 absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black focus:outline-none dark:bg-zinc-800 dark:ring-zinc-700"
                                    role="menu"
                                    aria-orientation="vertical"
                                    style="display: none"
                                >
                                    <div class="py-1" role="none">
                                        <!-- Edit option -->
                                        <button
                                            class="flex w-full cursor-pointer items-center px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-700"
                                            role="menuitem"
                                            @click="$dispatch('edit-known-ip-address', [{{ $ipAddress->id }}]); open = false"
                                        >
                                            <flux:icon.pencil class="mr-2 h-4 w-4 text-blue-500" />
                                            Edit
                                        </button>

                                        <!-- Delete option -->
                                        <form
                                            action="{{ route('known-ip-addresses.destroy', $ipAddress) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                class="flex w-full cursor-pointer items-center px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-700"
                                                role="menuitem"
                                            >
                                                <flux:icon.trash class="mr-2 h-4 w-4 text-red-500" />
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="bg-zinc-900 p-4 text-center" colspan="5">
                            <flux:text variant="subtle">No IP addresses created</flux:text>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <!-- Edit Modals for each IP Address -->
        <livewire:edit-known-ip-address-modal />
    </div>

    <div class="mt-8">
        {{ $ipAddresses->links() }}
    </div>

    <!-- Create Modal -->
    <livewire:known-ip-address-modal />
</x-layouts.app>
