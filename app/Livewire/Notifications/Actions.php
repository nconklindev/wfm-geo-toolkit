<?php

namespace App\Livewire\Notifications;

use Exception;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Log;
use LogicException;

class Actions extends Component
{
    public $selectedNotificationId = null;
    public $selectedNotificationData = null;

    public function deleteAllNotifications(): void
    {
        try {
            auth()->user()->notifications()->delete();
            $this->selectedNotificationId = null; // Clear selection
            $this->selectedNotificationData = null; // Clear details
            // Optionally, dispatch an event or flash message
            session()->flash('success', 'All notifications have been deleted.');
            $this->redirect(NotificationCenter::class);
        } catch (Exception $e) {
            Log::error('[NotificationCenter] Failed to delete all notifications: '.$e->getMessage());
            flash()->error('Failed to delete all notifications.');
        }
    }

    public function markAllAsRead(): void
    {
        try {
            auth()->user()->unreadNotifications->markAsRead();
        } catch (LogicException $exception) {
            Log::error("NotificationController: 'markAllAsRead' failed: ".$exception);
            $this->redirect(NotificationCenter::class);
        }

        session()->flash('success', 'All notifications have been marked as read.');
        $this->redirect(NotificationCenter::class);
    }

    public function render(): View
    {
        return view('livewire.notifications.actions');
    }
}
