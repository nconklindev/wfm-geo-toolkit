<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Log;
use LogicException;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = $user->notifications();

        // Apply filters
        $filter = request('filter');
        if ($filter === 'read') {
            $query->whereNotNull('read_at');
        } elseif ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        $status = request('status');
        if ($status === "Possible Conflict") {
            $query->whereRaw("data::jsonb->>'status' = ?", [$status]);
        } // TODO: Add more statuses here

        $notifications = $query->latest()->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }

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
