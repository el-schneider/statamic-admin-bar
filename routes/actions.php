<?php

use ElSchneider\StatamicAdminBar\Http\Controllers\AdminBarController;
use ElSchneider\StatamicAdminBar\Http\Controllers\CacheController;
use ElSchneider\StatamicAdminBar\Http\Controllers\EntryController;
use Illuminate\Support\Facades\Route;

// Unprotected route for fetching admin bar data
Route::get('/', AdminBarController::class);

// Protected routes for entry actions
Route::name('admin-bar.')->middleware('auth')->group(function () {
    Route::get('entry/{id}', [EntryController::class, 'show'])->name('entry.show');
    Route::put('entry/{id}', [EntryController::class, 'update'])->name('entry.update');

    // Cache routes
    Route::get('cache/stats', [CacheController::class, 'stats'])->name('cache.stats');
    Route::post('cache/{type}', [CacheController::class, 'clear'])->name('cache.clear');
});
