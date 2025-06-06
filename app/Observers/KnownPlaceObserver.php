<?php

namespace App\Observers;

use App\Models\KnownPlace;
use App\Services\KnownPlaceValidationService;
use Illuminate\Support\Facades\Log;

class KnownPlaceObserver
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
        // Check if the relationships that might affect conflicts have changed
        // For now, let's assume any update might affect conflicts
        Log::info("KnownPlaceObserver: 'updated' event triggered for ID {$knownPlace->id}. Running validation.");
        $this->validationService->validateAndNotify($knownPlace);
    }
}
