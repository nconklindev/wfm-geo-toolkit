<?php

namespace App\Http\ViewComposers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HeaderViewComposer
{
    public function compose(View $view): void
    {
        $unreadNotificationsCount = 0;

        if (Auth::check()) {
            $unreadNotificationsCount = Auth::user()->unreadNotifications->count();
        }
        // $view->with('foo', 'bar');
        $view->with('unreadNotificationsCount', $unreadNotificationsCount);
    }
}
