<?php

use App\Http\Controllers\KnownIpAddressController;
use App\Livewire\ImportKnownIpAddresses;

Route::get('known-ip-addresses', [KnownIpAddressController::class, 'index'])->middleware([
    'auth', 'verified'
])->name('known-ip-addresses.index');

Route::get('known-ip-addresses/{knownIpAddress}/edit', [KnownIpAddressController::class, 'edit'])->middleware([
    'auth', 'verified'
])->name('known-ip-addresses.edit');

Route::delete('known-ip-addresses/{knownIpAddress}', [KnownIpAddressController::class, 'destroy'])->middleware([
    'auth', 'verified'
])->name('known-ip-addresses.destroy');

Route::get('known-ip-addresses/import', ImportKnownIpAddresses::class)->middleware([
    'auth', 'verified'
])->name('known-ip-addresses.import');
