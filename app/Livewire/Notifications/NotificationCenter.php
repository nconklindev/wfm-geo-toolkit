<?php

namespace App\Livewire\Notifications;

use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

class NotificationCenter extends Component
{
    #[Url(keep: true)]
    public string $filter = 'all';

    #[Url(keep: true)]
    public string $status = 'all';

    public $selectedNotificationId = null;
    public $selectedNotificationData = null;
    public $sortOrder = 'newest';

    // Cache notifications to prevent them from disappearing when marked as read
    public $cachedNotifications = null;

    public function deleteNotification($notificationId): void
    {
        $notification = DatabaseNotification::find($notificationId);

        if (!$notification) {
            session()->flash('error', 'Notification not found.');
            return;
        }

        // Ensure the notification belongs to the authenticated user
        if (auth()->user()->id !== $notification->notifiable_id) {
            session()->flash('error', 'You are not authorized to delete this notification.');
            return;
        }

        try {
            // Remove from cached notifications
            $this->cachedNotifications = $this->cachedNotifications->reject(function ($cachedNotification) use (
                $notificationId
            ) {
                return $cachedNotification->id === $notificationId;
            });

            // Clear selection if the deleted notification was selected
            if ($this->selectedNotificationId === $notificationId) {
                $this->selectedNotificationId = null;
                $this->selectedNotificationData = null;
            }

            $notification->delete();
            session()->flash('success', 'Notification deleted.');

        } catch (Exception $exception) {
            Log::error("NotificationCenter: notification {$notificationId} failed to be deleted: ".$exception->getMessage());
            session()->flash('error', 'Failed to delete notification.');
        }
    }

    public function getNotificationsProperty()
    {
        if ($this->cachedNotifications === null) {
            $this->loadNotifications();
        }

        return $this->cachedNotifications;
    }

    public function loadNotifications(): void
    {
        $query = auth()->user()->notifications();

        // Apply filter for read/unread
        if ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        } elseif ($this->filter === 'unread') {
            $query->whereNull('read_at');
        }

        // Apply filter for notification status (type)
        if ($this->status !== 'all') {
            // Convert lowercase filter to proper case for database comparison
            $statusFilter = match ($this->status) {
                'critical' => 'Critical',
                'warning' => 'Warning',
                'info' => 'Info',
                'notification' => 'Notification',
                default => $this->status
            };

            $query->whereJsonContains('data', ['status' => $statusFilter]);
        }

        // Apply sort order
        if ($this->sortOrder === 'oldest') {
            $query->oldest('created_at');
        } else {
            $query->latest('created_at');
        }

        $this->cachedNotifications = $query->get();
    }

    public function mount(): void
    {
        $this->filter = request()->input('filter', 'all');
        $this->status = request()->input('status', 'all');
        $this->loadNotifications();
    }

    public function refreshNotifications(): void
    {
        $this->cachedNotifications = null;
        $this->selectedNotificationId = null;
        $this->selectedNotificationData = null;
        $this->loadNotifications();
    }

    // Reset cache when filters change

    #[Layout('components.layouts.app')]
    #[Title('Notifications')]
    public function render(): View
    {
        return view('livewire.notifications.notification-center');
    }

    public function selectNotification($notificationId): void
    {
        $this->selectedNotificationId = $notificationId;

        $notification = DatabaseNotification::find($notificationId);

        if ($notification) {
            $wasUnread = $notification->unread();

            // Mark as read when selected
            if ($wasUnread) {
                $notification->markAsRead();

                // If we're viewing unread notifications and this notification was just marked as read,
                // remove it from the cached notifications to make it disappear
                if ($this->filter === 'unread') {
                    $this->cachedNotifications = $this->cachedNotifications->reject(function ($cachedNotification) use (
                        $notificationId
                    ) {
                        return $cachedNotification->id === $notificationId;
                    });

                    // Clear selection if the notification disappears
                    $this->selectedNotificationId = null;
                    $this->selectedNotificationData = null;
                    return;
                }
            }

            // Prepare notification data for details view
            $data = $notification->data;

            // Merge nested details if they exist
            if (isset($data['details']) && is_array($data['details'])) {
                $data = array_merge($data, $data['details']);
            }

            // Set defaults for all notification types
            $defaults = [
                'status' => 'Notification',
                'message' => '',
                'triggered_known_place' => null,
                'conflicting_descendant_places' => [],
                'conflicting_ancestor_places' => [],
                'issues' => [],
                'range_size' => null,
                'detected_at' => null,
            ];

            $this->selectedNotificationData = array_merge($defaults, $data);
        } else {
            $this->selectedNotificationData = null;
        }
    }

    public function updatedFilter(): void
    {
        $this->cachedNotifications = null;
        $this->selectedNotificationId = null;
        $this->selectedNotificationData = null;
        $this->loadNotifications();
    }

    // Manual refresh method

    public function updatedSortOrder(): void
    {
        $this->cachedNotifications = null;
        $this->loadNotifications();
    }

    public function updatedStatus(): void
    {
        $this->cachedNotifications = null;
        $this->selectedNotificationId = null;
        $this->selectedNotificationData = null;
        $this->loadNotifications();
    }
}
