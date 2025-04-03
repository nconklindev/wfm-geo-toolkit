@php
    //    dd($node);
    $beginningOfTime = $node->start_date->year() === 1900 ?? 'Beginning of Time';
@endphp

<tr
    class="{{ ! $node->isRoot() ? 'child-of-' . $node->parent_id . ' hidden' : '' }} ml-3 pl-3"
    data-node-id="{{ $node->id }}"
    data-parent-id="{{ $node->parent_id }}"
    data-node-type-id="{{ $node->business_structure_type_id }}"
    data-node-name="{{ $node->name }}"
    data-node-description="{{ $node->description }}"
>
    <td class="px-1.5 py-1.5 text-sm whitespace-nowrap text-zinc-900 dark:text-zinc-100">
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
                <div class="w-7"></div>
            @endif
            {{ $node->name }}
        </div>
    </td>
    <!-- Node type name -->
    <td class="px-6 py-4 text-sm whitespace-nowrap text-zinc-500 dark:text-zinc-400">
        <x-color-badge :color="$node->type->hex_color">
            {{ $node->type->name }}
        </x-color-badge>
    </td>
    <!-- Node description -->
    <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
        {{ Str::limit($node->description) }}
    </td>
    <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
        {{ $node->start_date->year === 1970 ? 'Beginning of Time' : $node->start_date->format('m/d/Y') }}
    </td>
    <td class="px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
        {{ $node->end_date->year === 9999 ? 'Forever' : $node->end_date->format('m/d/Y') }}
    </td>
</tr>

{{-- Recursively render children --}}
<!-- Entrypoint for row recursion -->
@if (! $node->isLeaf())
    @foreach ($node->children as $child)
        @include('partials.node-row', ['node' => $child])
    @endforeach
@endif
