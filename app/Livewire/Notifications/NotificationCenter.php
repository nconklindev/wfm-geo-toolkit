<?php

namespace App\Livewire\Notifications;

use Exception;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Log;

class NotificationCenter extends Component
{
    public $selectedNotificationId = null;
    public $selectedNotificationData = null;
    public $sortOrder = 'newest';

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
                'status' => 'Notification',
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

    #[On('delete-all-notifications')]
    public function deleteAll(): void
    {
        try {
            auth()->user()->notifications()->delete();
        } catch (Exception $e) {
            Log::error('[NotificationCenter] Failed to delete all notifications: '.$e->getMessage());
            flash()->error('Failed to delete all notifications.');
        }
    }

    #[Computed]
    public function notifications()
    {
        $query = auth()->user()->notifications();

        // Apply sort order
        if ($this->sortOrder === 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        return $query->get();
    }
}
