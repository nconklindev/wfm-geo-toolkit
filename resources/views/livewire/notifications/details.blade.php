{{-- resources/views/components/notification/details.blade.php --}}

@props([
    'details',
])

@php
    // Ensure we're working with an array
    $details = is_array($details) ? $details : [];

    // Get status and message from the top level
    $status = $details['status'] ?? 'Notification';
    $message = $details['message'] ?? 'No additional information available.';

    // Extract place data
    $triggeredPlace = $details['triggered_known_place'] ?? null;
    $conflictingDescendants = $details['conflicting_descendant_places'] ?? [];
    $conflictingAncestors = $details['conflicting_ancestor_places'] ?? [];

    // If these are empty but we have a 'details' key containing this info, use that instead
    if (isset($details['details']) && is_array($details['details'])) {
        $triggeredPlace = $triggeredPlace ?: $details['details']['triggered_known_place'] ?? null;
        $conflictingDescendants = ! empty($conflictingDescendants) ? $conflictingDescendants : $details['details']['conflicting_descendant_places'] ?? [];
        $conflictingAncestors = ! empty($conflictingAncestors) ? $conflictingAncestors : $details['details']['conflicting_ancestor_places'] ?? [];
    }

    // Combine descendant and ancestor places into a single collection
    $conflictingPlaces = collect()
        ->concat($conflictingDescendants)
        ->concat($conflictingAncestors);

    // If we have a known_place_id but no triggered place, create a simple one
    if (! $triggeredPlace && isset($details['known_place_id']) && isset($details['known_place_name'])) {
        $triggeredPlace = [
            'id' => $details['known_place_id'],
            'name' => $details['known_place_name'],
            'nodes' => [],
        ];
    }
@endphp

<div
    class="h-full overflow-y-auto rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800"
>
    {{-- Status and Message --}}
    <div class="mb-4">
        <flux:heading level="3" size="md" class="mb-2">Status: {{ $status }}</flux:heading>
        <flux:text class="text-gray-600 dark:text-gray-300">{{ $message }}</flux:text>
    </div>

    {{-- Divider --}}
    <div class="my-4 border-t dark:border-gray-600"></div>

    @if ($triggeredPlace)
        <flux:heading level="3" size="md" class="mb-3 border-b pb-2 dark:border-gray-600">
            Triggering Known Place: {{ $triggeredPlace['name'] ?? 'Unknown' }}
            @if (isset($triggeredPlace['id']))
                (ID: {{ $triggeredPlace['id'] }})
            @endif
        </flux:heading>
        <div class="mb-4 space-y-1 text-sm">
            <flux:text variant="subtle">Associated Nodes:</flux:text>
            <ul class="list-inside list-disc pl-4">
                @forelse ($triggeredPlace['nodes'] ?? [] as $node)
                    <li>{{ $node['path'] ?? 'N/A' }} (Node ID: {{ $node['id'] ?? 'Unknown' }})</li>
                @empty
                    <li>No associated nodes found.</li>
                @endforelse
            </ul>
        </div>
    @else
        <flux:text variant="subtle">Triggering place details not available.</flux:text>
    @endif

    @if (! empty($conflictingDescendants) | ! empty($conflictingAncestors))
        <flux:heading level="3" size="md" class="mt-6 mb-3 border-b pb-2 dark:border-gray-600">
            Conflicting Places
        </flux:heading>
        <div class="space-y-3">
            @foreach ($conflictingDescendants as $place)
                <div class="rounded border border-gray-200 p-3 dark:border-gray-700">
                    <flux:text weight="medium">
                        {{ $place['name'] ?? 'Unknown' }}
                        @if (isset($place['id']))
                            (ID: {{ $place['id'] }})
                        @endif
                    </flux:text>
                    <ul class="mt-1 list-inside list-disc pl-4 text-sm text-gray-600 dark:text-gray-400">
                        @forelse ($place['nodes'] ?? [] as $node)
                            <li>{{ $node['path'] ?? 'N/A' }} (Node ID: {{ $node['id'] ?? 'Unknown' }})</li>
                        @empty
                            <li>No associated nodes found.</li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
        </div>
    @endif

    @if (! empty($conflictingAncestors))
        <flux:heading level="3" size="md" class="mt-6 mb-3 border-b pb-2 dark:border-gray-600">
            Conflicting Ancestor Places
        </flux:heading>
        <div class="space-y-3">
            @foreach ($conflictingAncestors as $place)
                <div class="rounded border border-gray-200 p-3 dark:border-gray-700">
                    <flux:text weight="medium">
                        {{ $place['name'] ?? 'Unknown' }}
                        @if (isset($place['id']))
                            (ID: {{ $place['id'] }})
                        @endif
                    </flux:text>
                    <ul class="mt-1 list-inside list-disc pl-4 text-sm text-gray-600 dark:text-gray-400">
                        @forelse ($place['nodes'] ?? [] as $node)
                            <li>{{ $node['path'] ?? 'N/A' }} (Node ID: {{ $node['id'] ?? 'Unknown' }})</li>
                        @empty
                            <li>No associated nodes found.</li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
        </div>
    @endif

    @if (empty($conflictingDescendants) && empty($conflictingAncestors) && $triggeredPlace === null)
        <div class="mt-6 rounded-md bg-gray-50 p-4 dark:bg-gray-700">
            <flux:text variant="subtle" class="text-center">
                @if ($status !== 'Notification')
                    No specific conflicting places identified for this status.
                @else
                        This is an informational notification with no associated places or conflicts.
                @endif
            </flux:text>
        </div>
    @endif

    {{-- Add relevant action buttons here if desired, e.g., link to edit the KnownPlace --}}
    @if ($triggeredPlace && isset($triggeredPlace['id']))
        <div class="mt-6 border-t pt-4 dark:border-gray-600">
            <flux:button
                :href="route('known-places.edit', $triggeredPlace['id'])"
                target="_blank"
                variant="outline"
                icon="pencil-square"
            >
                Edit Triggering Place
            </flux:button>
        </div>
    @elseif (isset($details['known_place_id']))
        <div class="mt-6 border-t pt-4 dark:border-gray-600">
            <flux:button
                :href="route('known-places.edit', $details['known_place_id'])"
                target="_blank"
                variant="outline"
                icon="pencil-square"
            >
                Edit Place
            </flux:button>
        </div>
    @endif
</div>
