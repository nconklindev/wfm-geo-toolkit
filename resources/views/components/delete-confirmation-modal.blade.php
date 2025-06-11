@php
    use App\Models\KnownPlace;
@endphp

@props([
    'name' => null,
    'itemToDelete' => 'this item',
    'deleteRoute' => null,
    'heading' => 'Confirm Delete',
    'model' => null,
    'deleteRouteName' => null,
    //Addanewpropforthecustomdeleteroutename,
])

{{-- Generate defaults based on model if not provided --}}
@php
    $modelBaseName = $model ? class_basename($model) : null;
    $generatedName = $model ? 'delete-' . Str::plural(Str::kebab($modelBaseName)) . '-' . $model->id : null;
    $generatedItemToDelete = $model ? 'this ' . Str::headline($modelBaseName) . ' (' . ($model->name ?? $model->id) . ')' : 'this item';

    // Determine the delete route
    $actionRoute = $deleteRoute; // Use provided deleteRoute if available

    if (! $actionRoute && $model) {
        // If no deleteRoute is provided, try to generate one
        if ($deleteRouteName) {
            // Use the custom delete route name if provided
            $actionRoute = route($deleteRouteName, $model);
        } else {
            // Fallback to default resource route naming
            $actionRoute = route(Str::plural(Str::kebab($modelBaseName)) . '.destroy', $model);
        }
    }

    // Use provided prop value for name if available, otherwise use generated default
    $modalName = $name ?? $generatedName;
    $displayItemToDelete = $itemToDelete ?? $generatedItemToDelete;
@endphp

{{-- Ensure essential props are available --}}
@if (! $modalName)
    @php
        Log::warning('Delete Confirmation Modal: The "name" prop is required or a "model" prop must be provided to generate a default name.');
    @endphp

    <div>Error: Modal name is missing.</div>
@elseif (! $actionRoute)
    @php
        Log::warning('Delete Confirmation Modal: The "deleteRoute" prop is required or a "model" prop with a standard resource route or a "deleteRouteName" must be provided to generate a default route.');
    @endphp

    <div>Error: Delete route is missing.</div>
@else
    <flux:modal name="{{ $modalName }}" class="min-w-[22rem]">
        <form method="POST" action="{{ $actionRoute }}">
            @csrf
            @method('DELETE')

            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $heading }}</flux:heading>

                    <flux:text class="mt-2 leading-loose">
                        <flux:text>You are about to delete {{ $displayItemToDelete }}.</flux:text>
                        <flux:text>This action cannot be reversed.</flux:text>
                        @if ($model instanceof KnownPlace)
                            <flux:text class="mt-1" variant="strong">
                                Deleting this Known Place might affect related records.
                            </flux:text>
                        @endif
                    </flux:text>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost" class="cursor-pointer">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" variant="danger" class="cursor-pointer">Delete</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
@endif
