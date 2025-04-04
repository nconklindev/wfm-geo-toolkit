<?php

use App\Http\Controllers\BusinessStructureNode;
use App\Http\Controllers\BusinessStructureNodeController;
use App\Http\Controllers\BusinessStructureTypeController;
use App\Http\Controllers\KnownPlaceController;
use App\Http\Controllers\TestController;
use App\Livewire\ExportKnownPlaces;
use App\Livewire\ImportKnownPlaces;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', function () {
    return view('welcome');
})->name('home');

// About
Route::get('/about', function () {
    return view('about');
})->name('about');

// Dashboard
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Known Places Import & Export
Route::get('known-places/import', ImportKnownPlaces::class)
    ->middleware(['auth', 'verified'])
    ->name('known-places.import');

Route::get('known-places/export', ExportKnownPlaces::class)
    ->middleware(['auth', 'verified'])
    ->name('known-places.export');

// Known Places Resources
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('known-places', KnownPlaceController::class)->except(['destroy', 'edit']);

    Route::get('known-places/{knownPlace}/edit',
        [KnownPlaceController::class, 'edit'])->name('known-places.edit')->can('update', ['knownPlace', 'user']);

    Route::delete('known-places/{knownPlace}',
        [KnownPlaceController::class, 'destroy'])->name('known-places.destroy')->can('delete', ['knownPlace', 'user']);
});

Route::get('downloads/sample-known-places',
    [KnownPlaceController::class, 'downloadSample'])->middleware([
    'auth',
    'verified'
])->name('downloads.sample-known-places');

// Locations
Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('business-structure')->name('business-structure.')->group(function () {
        // Business Node Types
        Route::get('/types', [BusinessStructureTypeController::class, 'index'])->name('types.index');
        Route::get('/types/import', [BusinessStructureTypeController::class, 'import'])->name('types.import');

        // Locations
        Route::get('/locations', [BusinessStructureNodeController::class, 'index'])->name('locations.index');
        Route::get('/locations/import', [BusinessStructureNodeController::class, 'import'])->name('locations.import');
    });
});

// User Settings
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('test', [TestController::class, 'index'])->name('test');
});

require __DIR__.'/auth.php';
