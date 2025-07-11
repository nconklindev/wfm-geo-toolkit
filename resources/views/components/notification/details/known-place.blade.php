{{-- resources/views/components/notification/details/known-place.blade.php --}}

@php
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

    // You could also add a type marker if you want to differentiate them in the UI
    $conflictingPlacesWithType = collect();
    foreach ($conflictingDescendants as $place) {
        $place['relation_type'] = 'descendant';
        $conflictingPlacesWithType->push($place);
    }
    foreach ($conflictingAncestors as $place) {
        $place['relation_type'] = 'ancestor';
        $conflictingPlacesWithType->push($place);
    }

    // If we have a known_place_id but no triggered place, create a simple one
    if (! $triggeredPlace && isset($details['known_place_id']) && isset($details['known_place_name'])) {
        $triggeredPlace = [
            'id' => $details['known_place_id'],
            'name' => $details['known_place_name'],
            'nodes' => [],
        ];
    }
@endphp

@if ($triggeredPlace)
    <div class="mb-4">
        <flux:heading level="3">Triggering Known Place</flux:heading>
        <flux:text>
            {{ $triggeredPlace['name'] ?? 'Unknown' }}
            @if (isset($triggeredPlace['id']))
                <flux:text variant="subtle" class="inline">(ID: {{ $triggeredPlace['id'] }})</flux:text>
            @endif
        </flux:text>
    </div>
    <flux:separator class="my-4" />

    <div class="mb-4 space-y-1 text-sm">
        <flux:heading level="3">Associated Locations</flux:heading>
        <ul class="list-inside list-disc pl-4">
            @forelse ($triggeredPlace['nodes'] ?? [] as $node)
                <li>
                    <flux:text class="inline">
                        {{ $node['path'] ?? 'N/A' }}
                        <flux:text variant="subtle" class="inline">(ID: {{ $node['id'] ?? 'Unknown' }})</flux:text>
                    </flux:text>
                </li>
            @empty
                <li>No associated nodes found.</li>
            @endforelse
        </ul>
    </div>
@else
    <flux:text variant="subtle">Triggering place details not available.</flux:text>
@endif

@if ($conflictingPlaces->isNotEmpty())
    <flux:heading level="3" size="md" class="mt-6 mb-3 border-b pb-2 dark:border-zinc-600">
        Conflicting Places
    </flux:heading>
    <div class="space-y-3">
        @foreach ($conflictingPlacesWithType as $place)
            <div class="rounded border border-zinc-200 p-3 dark:border-zinc-700">
                <div class="mb-1 flex items-center justify-between">
                    <flux:text weight="medium">
                        {{ $place['name'] ?? 'Unknown' }}
                        @if (isset($place['id']))
                            (ID: {{ $place['id'] }})
                        @endif
                    </flux:text>

                    @if (isset($place['relation_type']))
                        <flux:badge
                            size="sm"
                            variant="pill"
                            :color="$place['relation_type'] === 'descendant' ? 'purple' : 'indigo'"
                        >
                            {{ ucfirst($place['relation_type']) }}
                        </flux:badge>
                    @endif
                </div>
                <ul class="mt-1 list-inside list-disc pl-4 text-sm text-zinc-600 dark:text-zinc-400">
                    @forelse ($place['nodes'] ?? [] as $node)
                        <li>{{ $node['path'] ?? 'N/A' }} (Node ID: {{ $node['id'] ?? 'Unknown' }})</li>
                    @empty
                        <li>No associated nodes found.</li>
                    @endforelse
                </ul>
            </div>
        @endforeach
    </div>
@elseif ($details['status'] ?? '' !== 'Notification')
    <flux:text variant="subtle" class="mt-4">No specific conflicting places identified for this status.</flux:text>
@else
    <flux:text variant="subtle" class="mt-4">
        This is an informational notification with no associated conflicts.
    </flux:text>
@endif

{{-- Detail Actions --}}
@if ($triggeredPlace && isset($triggeredPlace['id']))
    <div class="mt-6 border-t pt-4 dark:border-zinc-600">
        <flux:button
            href="{{ route('known-places.edit', $triggeredPlace['id']) }}"
            target="_blank"
            size="xs"
            icon="pencil-square"
        >
            Edit Triggering Place
        </flux:button>
    </div>
@endif
