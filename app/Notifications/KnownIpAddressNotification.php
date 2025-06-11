<?php

namespace App\Notifications;

use App\Models\KnownIpAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class KnownIpAddressNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public KnownIpAddress $knownIpAddress;
    public array $issueDetails;

    public function __construct(KnownIpAddress $knownIpAddress, array $issueDetails)
    {
        $this->knownIpAddress = $knownIpAddress;
        $this->issueDetails = $issueDetails;
    }

    /**
     * Get the notification's broadcast type for real-time updates.
     *
     * @return string
     */
    public function broadcastType(): string
    {
        return 'known-ip-address.notification';
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
            'known_ip_address_id' => $this->knownIpAddress->id,
            'known_ip_address_name' => $this->knownIpAddress->name,
            'start' => $this->knownIpAddress->start,
            'end' => $this->knownIpAddress->end,
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
        Log::info("KnownIpAddressNotification: Broadcasting notification for KnownIpAddress ID {$this->knownIpAddress->id}");

        return new BroadcastMessage([
            'known_ip_address_id' => $this->knownIpAddress->id,
            'known_ip_address_name' => $this->knownIpAddress->name,
            'message' => $this->issueDetails['message'] ?? 'IP Address notification',
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
}
