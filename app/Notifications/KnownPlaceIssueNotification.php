<?php

namespace App\Notifications;

use App\Models\KnownPlace;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

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

    /**
     * Get the notification's broadcast type for real-time updates.
     *
     * @return string
     */
    public function broadcastType(): string
    {
        return 'known-place.notification';
    }

    /**
     * Get the array representation of the notification (for database storage).
     *
     * @param  object  $notifiable
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'known_place_id' => $this->knownPlace->id,
            'known_place_name' => $this->knownPlace->name,
            'message' => $this->issueDetails['message'] ?? null,
            'status' => $this->issueDetails['status'] ?? 'Notification',
            'details' => $this->issueDetails['details'] ?? [],
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  object  $notifiable
     *
     * @return BroadcastMessage
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        Log::info("KnownPlaceIssueNotification: Broadcasting notification for KnownPlace ID {$this->knownPlace->id}");

        return new BroadcastMessage([
            'known_place_id' => $this->knownPlace->id,
            'known_place_name' => $this->knownPlace->name,
            'message' => $this->issueDetails['message'] ?? 'Known Place notification',
            'count' => $notifiable->unreadNotifications()->count(),
        ]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  object  $notifiable
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Determine which queues should be used for each notification channel.
     *
     * @return array<string, string>
     */
    public function viaQueues(): array
    {
        return [
            'database' => 'default',
            'broadcast' => 'notifications',
        ];
    }
}
