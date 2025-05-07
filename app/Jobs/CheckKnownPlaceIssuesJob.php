<?php

namespace App\Jobs;

use App\Events\NotificationReceivedEvent;
use App\Models\BusinessStructureNode;
use App\Models\KnownPlace;
use App\Models\User;
use App\Notifications\KnownPlaceIssueNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckKnownPlaceIssuesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $knownPlaceId;
    protected ?int $userId; // User who triggered the save

    /**
     * Create a new job instance.
     *
     * @param  int  $knownPlaceId  The ID of the KnownPlace to check.
     * @param  int|null  $userId  The ID of the user associated with the action (optional but recommended).
     */
    public function __construct(int $knownPlaceId, ?int $userId)
    {
        $this->knownPlaceId = $knownPlaceId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * This method will be executed by the queue worker.
     */
    public function handle(): void
    {
        Log::info("CheckKnownPlaceIssues Job: Starting check for KnownPlace ID {$this->knownPlaceId}");

        // Fetch the fresh KnownPlace model *with its nodes* from the database
        // This happens *after* the original request is complete.
        $knownPlace = KnownPlace::with('nodes')->find($this->knownPlaceId);

        // Handle cases where the KnownPlace might have been deleted before the job ran
        if (!$knownPlace) {
            Log::warning("CheckKnownPlaceIssues Job: KnownPlace ID {$this->knownPlaceId} not found. Skipping check.");
            return;
        }

        $conflictDetails = $this->checkForIssues($knownPlace);

        if ($conflictDetails !== false) {
            // Try to find the user who should be notified
            $userToNotify = $this->userId ? User::find($this->userId) : null;

            // Fallback: If the triggering user isn't available, maybe notify an admin?
            // Or use the user associated with the KnownPlace itself if appropriate.
            if (!$userToNotify && $knownPlace->user_id) {
                $userToNotify = User::find($knownPlace->user_id);
                Log::info("CheckKnownPlaceIssues Job: Triggering user ID {$this->userId} not found or not provided. Using KnownPlace owner ID {$knownPlace->user_id} for notification.");
            }


            if ($userToNotify) {
                $userToNotify->notify(new KnownPlaceIssueNotification($knownPlace, $conflictDetails));
                Log::info("CheckKnownPlaceIssues Job: Conflict notification sent for KnownPlace ID {$knownPlace->id} to User ID {$userToNotify->id}. Broadcasting channel: App.Models.User.{$userToNotify->id}");
            } else {
                Log::warning("CheckKnownPlaceIssues Job: Could not determine user to notify for KnownPlace conflict: ID {$knownPlace->id}");
                // Consider logging this failure more permanently or notifying admins
            }
        } else {
            Log::info("CheckKnownPlaceIssues Job: No conflicts found for KnownPlace ID {$knownPlace->id}.");
        }
    }

    /**
     * Checks for hierarchical conflicts in associated Known Places
     *
     * @param  KnownPlace  $knownPlace  The KnownPlace (fresh instance with nodes loaded) to check.
     * @return bool|array False if no conflict, or an array with conflict details.
     * @uses KnownPlace
     */
    private function checkForIssues(KnownPlace $knownPlace): bool|array
    {
        $associatedNodes = $knownPlace->nodes;

        if ($associatedNodes->isEmpty()) {
            Log::info("CheckKnownPlaceIssues Job: KnownPlace ID {$knownPlace->id} has no associated nodes after job execution. Skipping conflict check.");
            return false;
        }

        Log::info("CheckKnownPlaceIssues Job: Checking conflicts for KnownPlace ID {$knownPlace->id} with nodes: ".$associatedNodes->pluck('id')->implode(', '));

        $allDescendantIds = [];
        $allAncestorIds = [];

        foreach ($associatedNodes as $node) {
            if ($node instanceof BusinessStructureNode) {
                $descendantIds = $node->descendants()->pluck('id')->all();
                $allDescendantIds = array_merge($allDescendantIds, $descendantIds);

                $ancestorIds = $node->ancestors()->pluck('id')->all();
                $allAncestorIds = array_merge($allAncestorIds, $ancestorIds);
            }
        }

        $allDescendantIds = array_unique($allDescendantIds);
        $allAncestorIds = array_unique($allAncestorIds);

        $conflictingDescendantKnownPlaceIds = [];
        $conflictingAncestorKnownPlaceIds = [];

        if (!empty($allDescendantIds)) {
            $conflictingDescendantKnownPlaceIds = KnownPlace::where('id', '!=', $knownPlace->id)
                ->whereHas('nodes', function ($query) use ($allDescendantIds) {
                    $query->whereIn('business_structure_node_id', $allDescendantIds);
                })
                ->pluck('id')
                ->all();
        }

        if (!empty($allAncestorIds)) {
            $conflictingAncestorKnownPlaceIds = KnownPlace::where('id', '!=', $knownPlace->id)
                ->whereHas('nodes', function ($query) use ($allAncestorIds) {
                    $query->whereIn('business_structure_node_id', $allAncestorIds);
                })
                ->pluck('id')
                ->all();
        }

        $conflict = !empty($conflictingDescendantKnownPlaceIds) || !empty($conflictingAncestorKnownPlaceIds);

        if ($conflict) {
            // Fetch conflicting Known Places with their nodes to get paths
            $conflictingDescendantPlaces = KnownPlace::with('nodes')
                ->whereIn('id', $conflictingDescendantKnownPlaceIds)
                ->get();

            $conflictingAncestorPlaces = KnownPlace::with('nodes')
                ->whereIn('id', $conflictingAncestorKnownPlaceIds)
                ->get();

            return [
                'status' => 'Possible Conflict',
                'details' => [
                    'triggered_known_place' => [
                        'id' => $knownPlace->id,
                        'name' => $knownPlace->name,
                        'nodes' => $associatedNodes->map(function ($node) {
                            return [
                                'id' => $node->id,
                                'path' => $node->path,
                            ];
                        })->all(),
                    ],
                    'conflicting_descendant_places' => $conflictingDescendantPlaces->map(function ($place) {
                        return [
                            'id' => $place->id,
                            'name' => $place->name,
                            'nodes' => $place->nodes->map(function ($node) {
                                return [
                                    'id' => $node->id,
                                    'path' => $node->path,
                                ];
                            })->all(),
                        ];
                    })->all(),
                    'conflicting_ancestor_places' => $conflictingAncestorPlaces->map(function ($place) {
                        return [
                            'id' => $place->id,
                            'name' => $place->name,
                            'nodes' => $place->nodes->map(function ($node) {
                                return [
                                    'id' => $node->id,
                                    'path' => $node->path,
                                ];
                            })->all(),
                        ];
                    })->all(),
                ]
            ];
        }

        return false; // No conflicts found
    }
}
