<?php

namespace App\Livewire\Notifications;

use App\Http\Controllers\NotificationController;
use Exception;
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

    public function markAllAsRead()
    {
        try {
            auth()->user()->unreadNotifications->markAsRead();
        } catch (LogicException $exception) {
            Log::error("NotificationController: 'markAllAsRead' failed: ".$exception);
            return back()->with('error', 'Failed to mark all notifications as read.');
        }

        $this->redirect(NotificationController::class)->with('success', 'All notifications marked as read.');
    }

    public function render()
    {
        return view('livewire.notifications.actions');
    }
}
