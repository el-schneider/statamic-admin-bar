<?php

use ElSchneider\StatamicAdminBar\Http\Controllers\AdminBarController;
use Illuminate\Support\Facades\Route;

Route::get('/', AdminBarController::class);
