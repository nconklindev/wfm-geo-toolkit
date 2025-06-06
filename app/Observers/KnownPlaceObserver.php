<?php

namespace App\Observers;

use App\Models\KnownPlace;
use App\Services\KnownPlaceValidationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Log;

class KnownPlaceObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly KnownPlaceValidationService $validationService
    ) {
    }

    /**
     * Handle the KnownPlace "created" event.
     */
    public function created(KnownPlace $knownPlace): void
    {
        Log::info("KnownPlaceObserver: 'created' event triggered for ID {$knownPlace->id}. Running validation.");
        $this->validationService->validateAndNotify($knownPlace);
    }

    /**
     * Handle the KnownPlace "updated" event.
     */
    public function updated(KnownPlace $knownPlace): void
    {
        // Add debugging to see what changed
        $dirty = $knownPlace->getDirty();
        $original = $knownPlace->getOriginal();

        Log::info("KnownPlaceObserver: 'updated' event triggered for ID {$knownPlace->id}.", [
            'dirty_fields' => array_keys($dirty),
            'changes' => $dirty
        ]);

        // Only run validation if meaningful fields changed (not timestamps)
        $meaningfulFields = [
            'name', 'description', 'latitude', 'longitude', 'radius', 'locations', 'wifi_networks', 'validation_order',
            'group_id'
        ];
        $meaningfulChanges = array_intersect_key($dirty, array_flip($meaningfulFields));

        if (!empty($meaningfulChanges)) {
            Log::info("KnownPlaceObserver: Meaningful changes detected, running validation.");
            $this->validationService->validateAndNotify($knownPlace);
        } else {
            Log::info("KnownPlaceObserver: No meaningful changes detected, skipping validation.");
        }
    }
}
