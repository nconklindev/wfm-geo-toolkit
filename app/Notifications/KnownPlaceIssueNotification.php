<?php

namespace App\Notifications;

use App\Models\KnownPlace;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Log;

class KnownPlaceIssueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public KnownPlace $knownPlace;
    public array $issueDetails;

    public function __construct(KnownPlace $knownPlace, array $issueDetails)
    {
        $this->knownPlace = $knownPlace;
        $this->issueDetails = $issueDetails;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification (for database storage).
     *
     * @param  object  $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $details = $this->issueDetails['details'] ?? [];
        $triggeredKnownPlaceData = $details['triggered_known_place'] ?? null;

        // Access the full conflicting places data directly
        $conflictingDescendantPlaces = $details['conflicting_descendant_places'] ?? [];
        $conflictingAncestorPlaces = $details['conflicting_ancestor_places'] ?? [];

        // --- Removed the plucking of IDs ---

        $message = 'A potential hierarchy conflict has been detected for a Known Place. Please review the details to resolve the issue.';

        // Check if the arrays themselves are empty, not just the IDs
        if (!empty($conflictingDescendantPlaces) && !empty($conflictingAncestorPlaces)) {
            $message = "A Known Place is linked to one or more locations that are both ancestors of other Known Places' nodes and descendants of other Known Places' nodes. Review is needed.";
        } elseif (!empty($conflictingDescendantPlaces)) {
            $message = "A Known Place is linked to a broader location, but other Known Places are linked to more specific location. Review is needed.";
        } elseif (!empty($conflictingAncestorPlaces)) {
            $message = "A Known Place is linked to a specific location, but another Known Place is linked to a broader location that includes it. Review is needed.";
        }

        return [
            'known_place_id' => $this->knownPlace->id,
            'known_place_name' => $this->knownPlace->name,
            'status' => $this->issueDetails['status'] ?? 'Notification',
            'message' => $message,
            'details' => [
                'triggered_known_place' => $triggeredKnownPlaceData,
                // Use the full data arrays and update the keys
                'conflicting_descendant_places' => $conflictingDescendantPlaces,
                'conflicting_ancestor_places' => $conflictingAncestorPlaces,
            ],
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        Log::info("KnownPlaceIssueNotification: Broadcasting notification for KnownPlace ID {$this->knownPlace->id}");
        return new BroadcastMessage([
            'known_place_id' => $this->knownPlace->id,
            'count' => $notifiable->unreadNotifications->count(),
            'message' => $this->issueDetails['details'] ?? [],
        ]);
    }
}
