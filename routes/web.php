<?php

use App\Http\Controllers\DashboardController;
use App\Livewire\IpAddressChecker;
use App\Livewire\IpAddressImport;
use App\Livewire\Tools\ApiExplorer\ApiExplorer;
use App\Livewire\Tools\ApiExplorer\Docs\ApiExplorerDocs;
use App\Livewire\Tools\HarAnalyzer\HarAnalyzer;
use App\Livewire\Tools\HarAnalyzer\HarAnalyzerResults;
use App\Livewire\Tools\Plotter;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', function () {
    return view('home');
})->name('home');

Route::prefix('tools')->name('tools.')->group(function () {
    Route::get('/plotter', Plotter::class)->name('plotter');
    Route::get('/har-analyzer', HarAnalyzer::class)->name('har-analyzer');
    Route::get('/ip-checker/import', IpAddressImport::class)->name('ip-checker-import');
    Route::get('/ip-checker', IpAddressChecker::class)->name('ip-checker');
    Route::get('/har-analyzer/results', HarAnalyzerResults::class)->name('har-analyzer-results');
    Route::get('/api-explorer', ApiExplorer::class)->name('api-explorer');
    Route::get('/api-explorer/docs', ApiExplorerDocs::class)->name('api-explorer-docs');
});

// Welcome
Route::get('/welcome', function () {
    return view('welcome');
})->middleware(['auth', 'verified'])->name('welcome');

// Dashboard
Route::get('dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
require __DIR__.'/known_places.php';
require __DIR__.'/settings.php';
require __DIR__.'/locations.php';
require __DIR__.'/notifications.php';
require __DIR__.'/known_ips.php';
require __DIR__.'/groups.php';
