<x-layouts.app :title="__('Business Structure')">
    <div class="mx-auto max-w-7xl pb-12" x-data="{}">
        <h2 class="text-xl leading-tight font-semibold text-zinc-800 dark:text-zinc-200">
            {{ __('Business Structure') }}
        </h2>
        <flux:navbar name="header">
            <div class="flex items-center justify-between">
                <div class="flex space-x-4">
                    <flux:navbar.item href="{{ route('business-structure.locations.import') }}">
                        {{ __('Add Node') }}
                    </flux:navbar.item>
                    <flux:navbar.item href="{{ route('business-structure.types.index') }}">
                        {{ __('Manage Node Types') }}
                    </flux:navbar.item>
                </div>
            </div>
        </flux:navbar>
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-zinc-800">
            <div class="p-6" x-data>
                <!-- Filter controls -->
                <div class="mb-6 rounded-md bg-zinc-50 p-4 dark:bg-zinc-700">
                    <h3 class="mb-2 text-lg font-medium text-zinc-900 dark:text-zinc-100">{{ __('Filters') }}</h3>
                    <div class="flex flex-wrap gap-4">
                        <flux:select label="{{ __('Node Type') }}">
                            <option value="">{{ __('All Types') }}</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:input
                            type="text"
                            label="Search"
                            name="search"
                            placeholder="{{ __('Search nodes...') }}"
                            clearable
                        />
                        <div class="self-end">
                            <flux:button x-on:click="$dispatch('expand-all')" class="cursor-pointer">
                                {{ __('Expand All') }}
                            </flux:button>
                            <flux:button x-on:click="$dispatch('collapse-all')" class="cursor-pointer">
                                {{ __('Collapse All') }}
                            </flux:button>
                        </div>
                    </div>
                </div>

                <!-- Business Structure Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full table-fixed divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-700">
                            <tr>
                                <th
                                    scope="col"
                                    class="w-1/2 px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                >
                                    {{ __('Node Name') }}
                                </th>
                                <th
                                    scope="col"
                                    class="w-1/4 px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                >
                                    {{ __('Type') }}
                                </th>
                                <th
                                    scope="col"
                                    class="w-1/4 px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                >
                                    {{ __('Description') }}
                                </th>
                                <th
                                    scope="col"
                                    class="w-1/4 px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                >
                                    {{ __('Start Date') }}
                                </th>
                                <th
                                    scope="col"
                                    class="w-1/4 px-6 py-3 text-left text-xs font-medium tracking-wider text-zinc-500 uppercase dark:text-zinc-300"
                                >
                                    {{ __('End Date') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                            @if (! $nodes->isEmpty())
                                @foreach ($nodes as $node)
                                    @include('partials.node-row', ['node' => $node, 'level' => 0])
                                @endforeach
                            @else
                                <tr class="w-full items-center justify-center text-center">
                                    <td class="py-6 text-center" colspan="100%">
                                        <flux:text variant="subtle">
                                            {{ __('No Business Structure found. Try importing.') }}
                                        </flux:text>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('businessStructure', {
                nodeStates: {},

                toggleNode(nodeId) {
                    // Toggle the state of this node
                    this.nodeStates[nodeId] = !this.nodeStates[nodeId];

                    // Show/hide direct children
                    const childElements = document.querySelectorAll(`.child-of-${nodeId}`);
                    childElements.forEach((el) => {
                        if (this.nodeStates[nodeId]) {
                            el.classList.remove('hidden');
                        } else {
                            el.classList.add('hidden');

                            // Recursively collapse all descendants
                            const childNodeId = el.dataset.nodeId;
                            if (childNodeId && this.nodeStates[childNodeId]) {
                                this.collapseSubtree(childNodeId);
                            }
                        }
                    });
                },

                // Recursively collapse a subtree
                collapseSubtree(nodeId) {
                    // Set state to collapsed
                    this.nodeStates[nodeId] = false;

                    // Hide all direct children and recursively collapse their children
                    const childElements = document.querySelectorAll(`.child-of-${nodeId}`);
                    childElements.forEach((el) => {
                        el.classList.add('hidden');

                        const childNodeId = el.dataset.nodeId;
                        if (childNodeId && this.nodeStates[childNodeId]) {
                            this.collapseSubtree(childNodeId);
                        }
                    });
                },

                expandAll() {
                    // Show all child nodes
                    document.querySelectorAll('[class*="child-of-"]').forEach((node) => {
                        node.classList.remove('hidden');
                    });

                    // Set all toggle states to open
                    document.querySelectorAll('.node-toggle').forEach((toggle) => {
                        const nodeId = toggle.getAttribute('data-node-id');
                        this.nodeStates[nodeId] = true;
                    });
                },

                collapseAll() {
                    // Hide all child nodes
                    document.querySelectorAll('[class*="child-of-"]').forEach((node) => {
                        node.classList.add('hidden');
                    });

                    // Set all toggle states to closed
                    document.querySelectorAll('.node-toggle').forEach((toggle) => {
                        const nodeId = toggle.getAttribute('data-node-id');
                        this.nodeStates[nodeId] = false;
                    });
                },
            });

            // Set up event listeners
            window.addEventListener('expand-all', () => {
                Alpine.store('businessStructure').expandAll();
            });

            window.addEventListener('collapse-all', () => {
                Alpine.store('businessStructure').collapseAll();
            });

            window.addEventListener('toggle-node', (event) => {
                if (event.detail && event.detail.nodeId) {
                    Alpine.store('businessStructure').toggleNode(event.detail.nodeId);
                }
            });
        });
    </script>
</x-layouts.app>
