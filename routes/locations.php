<?php

// Locations
use App\Http\Controllers\BusinessStructureNodeController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('locations')->name('locations.')->group(function () {
        // Locations
        Route::get('/', [BusinessStructureNodeController::class, 'index'])->name('index');

        Route::get('/{node}',
            [BusinessStructureNodeController::class, 'show'])->name('show');
    });
});
