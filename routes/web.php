<?php

use App\Http\Controllers\BusinessStructureNode;
use App\Http\Controllers\BusinessStructureNodeController;
use App\Http\Controllers\BusinessStructureTypeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KnownPlaceController;
use App\Http\Controllers\TestController;
use App\Livewire\ExportKnownPlaces;
use App\Livewire\ImportBusinessStructure;
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
    Route::prefix('business-structure')->name('business-structure.')->group(function () {
        // Business Node Types
        Route::get('/types/import', [BusinessStructureTypeController::class, 'import'])->name('types.import');

        // Index
        Route::get('/types', [BusinessStructureTypeController::class, 'index'])->name('types.index');

        // Create
        Route::get('/types/create', [BusinessStructureTypeController::class, 'create'])->name('types.create');
        Route::post('/types', [BusinessStructureTypeController::class, 'store'])->name('types.store');

        // Update
        Route::get('/types/{type}/edit', [BusinessStructureTypeController::class, 'edit'])->name('types.edit');
        Route::patch('/types/{type}', [BusinessStructureTypeController::class, 'update'])->name('types.update');

        // Delete
        Route::delete('/types/{type}', [BusinessStructureTypeController::class, 'destroy'])->name('types.destroy');

        // Locations
        Route::get('/locations', [BusinessStructureNodeController::class, 'index'])->name('locations.index');
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
