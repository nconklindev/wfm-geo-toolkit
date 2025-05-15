<?php

namespace App\Http\Controllers;

use Illuminate\Notifications\DatabaseNotification;
use Log;
use LogicException;

class NotificationController extends Controller
{


    public function markAsRead(DatabaseNotification $notification)
    {
        // Ensure the notification belongs to the authenticated user
        if (auth()->user()->id === $notification->notifiable_id) {
            $notification->markAsRead();
        }

        return back()->with('success', 'Notification marked as read.'); // Redirect back with flash
    }

    public function deleteNotification(DatabaseNotification $notification)
    {
        // Ensure the notification belongs to the authenticated user
        if (auth()->user()->id === $notification->notifiable_id) {
            try {
                $notification->delete();
            } catch (LogicException $exception) {
                Log::error("NotificationController: 'notification $notification' failed to be deleted: ".$exception);
            }

            return back()->with('success', 'Notification deleted.');
        }
        return back()->with('error', 'You are not authorized to delete this notification.');
    }
}
