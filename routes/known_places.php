<?php

// Known Places Import & Export
use App\Http\Controllers\KnownPlaceController;
use App\Livewire\ExportKnownPlaces;
use App\Livewire\ImportKnownPlaces;

Route::get('known-places/import', ImportKnownPlaces::class)
    ->middleware(['auth', 'verified'])
    ->name('known-places.import');

Route::get('known-places/export', ExportKnownPlaces::class)
    ->middleware(['auth', 'verified'])
    ->name('known-places.export');

Route::get('known-places/wfm-import', [KnownPlaceController::class, 'wfmImport'])->middleware([
    'auth', 'verified'
])->name('known-places.wfm-import');
Route::post('known-places/wfm-import', [KnownPlaceController::class, 'storeWfm'])->middleware([
    'auth', 'verified'
])->name('known-places.storeWfm');

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
