<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TestController;
use App\Livewire\Tools\Plotter;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', function () {
    return view('home');
})->name('home');

Route::prefix('tools')->name('tools.')->group(function () {
    Route::get('/plotter', Plotter::class)->name('plotter');
});

// Welcome
Route::get('/welcome', function () {
    return view('welcome');
})->middleware(['auth', 'verified'])->name('welcome');

// Dashboard
Route::get('dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');


// Test
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('test', [TestController::class, 'index'])->name('test');
});

require __DIR__.'/auth.php';
require __DIR__.'/known_places.php';
require __DIR__.'/settings.php';
require __DIR__.'/locations.php';
require __DIR__.'/notifications.php';
require __DIR__.'/known_ips.php';
require __DIR__.'/groups.php';
