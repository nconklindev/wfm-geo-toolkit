@php
    //    dd($nodes);
@endphp

<tr
    class="{{ ! $node->isRoot() ? 'child-of-' . $node->parent_id . ' hidden' : '' }} ml-3 pl-3"
    data-node-id="{{ $node->id }}"
    data-parent-id="{{ $node->parent_id }}"
    data-node-type-id="{{ $node->business_structure_type_id }}"
    data-node-name="{{ $node->name }}"
>
    <td class="px-1.5 py-1.5 text-sm whitespace-nowrap text-zinc-900 dark:text-zinc-100">
        <!-- Node Name -->
        <div class="flex items-center">
            @for ($i = 0; $i < $node->depth; $i++)
                <div class="mr-1.5 h-6 w-5 flex-none"></div>
            @endfor

            @if (! $node->isLeaf())
                <button
                    class="node-toggle mr-2 cursor-pointer focus:outline-none"
                    data-node-id="{{ $node->id }}"
                    x-data
                    @click="Alpine.store('businessStructure').toggleNode('{{ $node->id }}')"
                >
                    <!-- Chevron icon -->
                    <svg
                        class="h-5 w-5 text-zinc-500 transition-transform duration-200"
                        :class="$store.businessStructure.nodeStates['{{ $node->id }}'] ? 'rotate-90' : 'rotate-0'"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            @else
                <div class="w-5"></div>
            @endif
            {{ $node->name }}
        </div>
    </td>
    {{-- TODO: Enhance logic to display something that indicates something further down the node tree has something assigned --}}
    <td class="px-6 py-4 text-sm whitespace-nowrap text-zinc-500 dark:text-zinc-400">
        @if ($node->isLeaf())
            {{ $leafNodes->firstWhere('id', $node->id)->known_places_count ?? 0 }}
        @else
            <div class="flex items-center">
                <span>0</span>
                @if (isset($nodesWithAssignedDescendants[$node->id]))
                    <span class="ml-2 text-emerald-500" title="Contains assigned places in descendants">
                        <flux:tooltip content="Contains assigned places in descendants" position="right">
                            <flux:icon.check class="size-5 cursor-pointer stroke-2 text-green-500" />
                        </flux:tooltip>
                    </span>
                @endif
            </div>
        @endif
    </td>
</tr>

{{-- Recursively render children --}}
<!-- Entrypoint for row recursion -->
@if (! $node->isLeaf())
    @foreach ($node->children as $child)
        @include('partials.node-row', ['node' => $child])
    @endforeach
@endif
