<?php

use App\Http\Controllers\BusinessStructureNodeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KnownPlaceController;
use App\Http\Controllers\TestController;
use App\Livewire\ExportKnownPlaces;
use App\Livewire\ImportKnownPlaces;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
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

// Known Places Import & Export
Route::get('known-places/import', ImportKnownPlaces::class)
    ->middleware(['auth', 'verified'])
    ->name('known-places.import');

Route::get('known-places/export', ExportKnownPlaces::class)
    ->middleware(['auth', 'verified'])
    ->name('known-places.export');

// Known Places Resources
Route::middleware(['auth', 'verified'])->group(function () {
    // Index
    Route::get('known-places', [KnownPlaceController::class, 'index'])->name('known-places.index');

    // Create
    Route::get('known-places/create', [KnownPlaceController::class, 'create'])->name('known-places.create');
    Route::post('known-places', [KnownPlaceController::class, 'store'])->name('known-places.store');

    // Read
    Route::get('known-places/{knownPlace}', [KnownPlaceController::class, 'show'])->can('view',
        ['knownPlace', auth()->user()])->name('known-places.show');

    // Update
    Route::get('known-places/{knownPlace}/edit',
        [KnownPlaceController::class, 'edit'])->name('known-places.edit')->can('update',
        ['knownPlace', auth()->user()]);
    Route::patch('known-places/{knownPlace}',
        [KnownPlaceController::class, 'update'])->name('known-places.update')->middleware('can:update,knownPlace');

    // Delete
    Route::delete('known-places/{knownPlace}',
        [KnownPlaceController::class, 'destroy'])->name('known-places.destroy')->middleware('can:delete,knownPlace');
});

Route::get('downloads/sample-known-places',
    [KnownPlaceController::class, 'downloadSample'])->middleware([
    'auth',
    'verified'
])->name('downloads.sample-known-places');

// Locations
Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('locations')->name('locations.')->group(function () {
        // Locations
        Route::get('/', [BusinessStructureNodeController::class, 'index'])->name('index');

        Route::get('/{node}',
            [BusinessStructureNodeController::class, 'show'])->name('show');

        Route::get('/{node}/edit',
            [BusinessStructureNodeController::class, 'edit'])->name('edit'); // New: Edit node name/details
        Route::patch('/{node}',
            [BusinessStructureNodeController::class, 'update'])->name('update'); // New: Handle node update

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
