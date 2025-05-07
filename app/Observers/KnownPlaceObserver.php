<?php

namespace App\Observers;

use App\Jobs\CheckKnownPlaceIssuesJob;
use App\Models\KnownPlace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class KnownPlaceObserver
{
    /**
     * Handle the KnownPlace "created" event.
     *
     * Dispatches a job to check for conflicts after the request lifecycle completes.
     *
     * @param  KnownPlace  $knownPlace
     * @return void
     */
    public function created(KnownPlace $knownPlace): void
    {
        Log::info("KnownPlaceObserver: 'created' event triggered for ID {$knownPlace->id}. Dispatching CheckKnownPlaceConflicts job.");
        $userId = Auth::id(); // Get the ID of the user performing the action
        CheckKnownPlaceIssuesJob::dispatch($knownPlace->id, $userId);
        // Optional delay: ->delay(now()->addSeconds(10));
    }

    /**
     * Handle the KnownPlace "updated" event.
     *
     * Dispatches a job to check for conflicts after the request lifecycle completes.
     *
     * @param  KnownPlace  $knownPlace
     * @return void
     */
    public function updated(KnownPlace $knownPlace): void
    {
        // Check if the node relationship (or fields affecting the conflict check) actually changed.
        // This prevents dispatching the job unnecessarily on every minor update.
        // For now, let's assume any update *might* affect conflicts.
        Log::info("KnownPlaceObserver: 'updated' event triggered for ID {$knownPlace->id}. Dispatching CheckKnownPlaceConflicts job.");
        $userId = Auth::id(); // Get the ID of the user performing the action
        CheckKnownPlaceIssuesJob::dispatch($knownPlace->id, $userId);
        // Optional delay: ->delay(now()->addSeconds(10));
    }
}
