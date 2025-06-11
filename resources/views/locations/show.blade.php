<x-layouts.app :title="$node->name">
    <div class="container mx-auto">
        <h1 class="mb-6 text-3xl font-bold">{{ $node->name }}</h1>

        {{-- Display node details if needed --}}
        <div class="mb-8 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-semibold text-gray-800 dark:text-gray-200">Location Details</h2>
            <p class="text-gray-600 dark:text-gray-400">
                <span class="font-medium text-gray-700 dark:text-gray-300">Full Path:</span>
                {{ $node->path }}
            </p>
            {{-- Add more node details here as required --}}
        </div>

        {{-- Section for Known Places --}}
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-semibold text-gray-800 dark:text-gray-200">Associated Known Places</h2>

            @if ($node->knownPlaces->isNotEmpty())
                <ul class="list-inside list-disc space-y-2 text-gray-600 dark:text-gray-400">
                    @foreach ($node->knownPlaces as $knownPlace)
                        <li>
                            {{-- Link to the Known Place's show page --}}
                            <a
                                href="{{ route('known-places.show', $knownPlace) }}"
                                class="text-blue-600 underline hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                {{ $knownPlace->name }}
                            </a>
                            {{-- Display other KnownPlace details if needed --}}
                            {{-- e.g., <span class="text-sm text-gray-500">({{ $knownPlace->description }})</span> --}}
                        </li>
                    @endforeach
                </ul>
                {{ $knownPlaces->links() }}
            @else
                <p class="text-gray-500 dark:text-gray-400">
                    No Known Places are currently associated with this location.
                </p>
            @endif
        </div>

        {{-- Optional: Add a link back to the locations index --}}
        <div class="mt-8">
            <a
                href="{{ route('locations.index') }}"
                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
            >
                &larr; Back to Locations
            </a>
        </div>
    </div>
</x-layouts.app>
