<?php

// Notifications
use App\Http\Controllers\NotificationController;
use App\Livewire\Notifications\NotificationCenter;

Route::get('/notifications', NotificationCenter::class)->middleware([
    'auth',
    'verified'
])->name('notifications');

Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->middleware([
    'auth',
    'verified'
])->name('notifications.show');

Route::patch('/notifications/{notification}', [NotificationController::class, 'markAsRead'])->middleware([
    'auth',
    'verified'
])->name('notifications.mark-as-read');

Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
    ->middleware(['auth', 'verified'])
    ->name('notifications.mark-all-read');

Route::delete('/notifications/{notification}', [NotificationController::class, 'deleteNotification'])
    ->middleware(['auth', 'verified'])
    ->name('notifications.delete');
