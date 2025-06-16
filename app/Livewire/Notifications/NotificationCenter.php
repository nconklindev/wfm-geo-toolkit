<?php

namespace App\Livewire\Notifications;

use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationCenter extends Component
{
    use WithPagination;

    #[Url]
    public string $filter = 'all';

    #[Url]
    public string $status = 'all';

    // Lock the notification Id property so that it cannot be changed from the frontend
    // https://livewire.laravel.com/docs/properties#locking-the-property
    #[Locked]
    public $selectedNotificationId = null;
    public $selectedNotificationData = null;

    #[Url(as: 'sort')]
    public $sortOrder = 'newest';

    // Number of notifications per page
    public int $perPage = 15;

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
            // Clear selection if the deleted notification was selected
            if ($this->selectedNotificationId === $notificationId) {
                $this->selectedNotificationId = null;
                $this->selectedNotificationData = null;
            }

            $notification->delete();
            session()->flash('success', 'Notification deleted.');

            // Reset to first page if current page becomes empty
            $this->resetPage();

        } catch (Exception $exception) {
            Log::error("NotificationCenter: notification {$notificationId} failed to be deleted: ".$exception->getMessage());
            session()->flash('error', 'Failed to delete notification.');
        }
    }

    public function getNotificationsProperty()
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
        $query->reorder();
        if ($this->sortOrder === 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        return $query->paginate($this->perPage);
    }

    public function mount(): void
    {
        $this->filter = request()->input('filter', 'all');
        $this->status = request()->input('status', 'all');
        $this->sortOrder = request()->input('sort', 'newest');
        $this->perPage = request()->input('perPage', $this->perPage);
    }

    public function refreshNotifications(): void
    {
        $this->selectedNotificationId = null;
        $this->selectedNotificationData = null;
        $this->resetPage();
    }

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
        $this->selectedNotificationId = null;
        $this->selectedNotificationData = null;
        $this->resetPage();
    }

    public function updatedSortOrder(): void
    {
        $this->selectedNotificationId = null;
        $this->selectedNotificationData = null;
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->selectedNotificationId = null;
        $this->selectedNotificationData = null;
        $this->resetPage();
    }

    // Method to change items per page
    public function updatedPerPage(): void
    {
        $this->resetPage();
    }
}
