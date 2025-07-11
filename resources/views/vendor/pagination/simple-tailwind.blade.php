@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex justify-between">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span
                class="relative inline-flex cursor-default items-center rounded-md border border-zinc-300 bg-white px-4 py-2 text-sm leading-5 font-medium text-zinc-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-600"
            >
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a
                href="{{ $paginator->previousPageUrl() }}"
                rel="prev"
                class="relative inline-flex items-center rounded-md border border-zinc-300 bg-white px-4 py-2 text-sm leading-5 font-medium text-zinc-700 ring-zinc-300 transition duration-150 ease-in-out hover:text-zinc-500 focus:border-blue-300 focus:ring focus:outline-none active:bg-zinc-100 active:text-zinc-700 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:focus:border-blue-700 dark:active:bg-zinc-700 dark:active:text-zinc-300"
            >
                {!! __('pagination.previous') !!}
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a
                href="{{ $paginator->nextPageUrl() }}"
                rel="next"
                class="relative inline-flex items-center rounded-md border border-zinc-300 bg-white px-4 py-2 text-sm leading-5 font-medium text-zinc-700 ring-zinc-300 transition duration-150 ease-in-out hover:text-zinc-500 focus:border-blue-300 focus:ring focus:outline-none active:bg-zinc-100 active:text-zinc-700 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:focus:border-blue-700 dark:active:bg-zinc-700 dark:active:text-zinc-300"
            >
                {!! __('pagination.next') !!}
            </a>
        @else
            <span
                class="relative inline-flex cursor-default items-center rounded-md border border-zinc-300 bg-white px-4 py-2 text-sm leading-5 font-medium text-zinc-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-600"
            >
                {!! __('pagination.next') !!}
            </span>
        @endif
    </nav>
@endif
