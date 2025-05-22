<?php

use App\Http\Controllers\KnownIpAddressController;

Route::get('known-ip-addresses', [KnownIpAddressController::class, 'index'])->middleware([
    'auth', 'verified'
])->name('known-ip-addresses.index');

Route::get('known-ip-addresses/create', [KnownIpAddressController::class, 'create'])->middleware([
    'auth', 'verified'
])->name('known-ip-addresses.create');

Route::delete('known-ip-addresses/{knownIpAddress}', [KnownIpAddressController::class, 'destroy'])->middleware([
    'auth', 'verified'
])->name('known-ip-addresses.destroy');

Route::get('known-ip-addresses/import', [KnownIpAddressController::class, 'import'])->middleware([
    'auth', 'verified'
])->name('known-ip-addresses.import');
