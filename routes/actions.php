<?php

use ElSchneider\StatamicAdminBar\Http\Controllers\AdminBarController;
use ElSchneider\StatamicAdminBar\Http\Controllers\EntryController;
use Illuminate\Support\Facades\Route;

Route::name('admin-bar.')->middleware('auth')->group(function () {
    Route::get('/', AdminBarController::class);
    Route::get('entry/{id}', [EntryController::class, 'show'])->name('entry.show');
    Route::put('entry/{id}', [EntryController::class, 'update'])->name('entry.update');
});
