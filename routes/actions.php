<?php

use Illuminate\Support\Facades\Route;
use ElSchneider\StatamicAdminBar\Http\Controllers\AdminBarController;

Route::get('/', [AdminBarController::class, 'index']);
