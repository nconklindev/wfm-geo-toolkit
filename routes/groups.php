<?php

use App\Http\Controllers\GroupController;

Route::get('/groups/{group}', [GroupController::class, 'show'])->middleware([
    'auth', 'verified'
])->name('groups.show');

Route::get('/groups/{group}/edit', [GroupController::class, 'edit'])->middleware([
    'auth', 'verified'
])->name('groups.edit');

Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->middleware([
    'auth', 'verified'
])->name('groups.destroy');

Route::get('/groups', [GroupController::class, 'index'])->middleware(['auth', 'verified'])->name('groups.index');

Route::get('/groups/create', [GroupController::class, 'create'])->middleware([
    'auth', 'verified'
])->name('groups.create');
