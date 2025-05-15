<?php

namespace App\Livewire\Notifications;

use Illuminate\Notifications\DatabaseNotification;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

class NotificationCenter extends Component
{
    #[Url(keep: true)]
    public string $filter; // Ensure string type hint for consistency
    #[Url(keep: true)]
    public string $status; // Ensure string type hint for consistency
    public $selectedNotificationId = null;
    public $selectedNotificationData = null;
    public $sortOrder = 'newest';

    public function mount(): void
    {
        $this->filter = request()->input('filter', 'all');
        $this->status = request()->input('status', 'all');
    }

    #[Layout('components.layouts.app')]
    #[Title('Notifications')]
    public function render()
    {
        return view('livewire.notifications.notification-center');
    }

    public function selectNotification($notificationId)
    {
        $this->selectedNotificationId = $notificationId;

        $notification = DatabaseNotification::find($notificationId);

        if ($notification) {
            // Mark as read when selected
            if ($notification->unread()) {
                $notification->markAsRead();
            }

            // Get notification data
            $data = $notification->data;

            // If we have a nested 'details' structure, merge its properties into the main data
            if (isset($data['details']) && is_array($data['details'])) {
                $data = array_merge($data, $data['details']);
            }

            // Ensure all expected keys exist
            $this->selectedNotificationData = array_merge([
                'status' => 'Notification', // Default status if not present in data
                'message' => '',
                'triggered_known_place' => null,
                'conflicting_descendant_places' => [],
                'conflicting_ancestor_places' => [],
            ], $data);

//            Log::debug('Selected notification data', $this->selectedNotificationData);
        } else {
            $this->selectedNotificationData = null;
        }
    }

    #[Computed]
    public function notifications()
    {
        $query = auth()->user()->notifications();

        // Apply filter for read/unread
        if ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        } elseif ($this->filter === 'unread') {
            $query->whereNull('read_at');
        }
        // No specific read/unread filter for 'all'

        // Apply filter for notification status (type)
        if ($this->status !== 'all') {
            // Assuming 'status' is a top-level key in the 'data' JSON column
            // Change this line to use whereJsonContains
            $query->whereJsonContains('data', ['status' => $this->status]);
        }

        // Apply sort order
        if ($this->sortOrder === 'oldest') {
            $query->oldest('created_at'); // Explicitly sort by creation date
        } else {
            $query->latest('created_at'); // Explicitly sort by creation date
        }

        return $query->get();
    }
}
