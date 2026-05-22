<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Presentation\Admin\Controllers\DriverController;
use Src\Presentation\Admin\Controllers\DriverOrderController;
use Src\Presentation\Admin\Controllers\OrderAssignmentController;
use Src\Presentation\Admin\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| Admin Cpanel API Routes
|--------------------------------------------------------------------------
| Loaded by AdminRouteServiceProvider under the "api" middleware group and
| the "/api" prefix.
*/

Route::get('orders', OrderController::class)->name('orders.index');

Route::post('orders/{order}/assign', OrderAssignmentController::class)
    ->whereNumber('order')
    ->name('orders.assign');

// Search drivers by name or phone (driver picker).
Route::get('drivers', DriverController::class)->name('drivers.index');

Route::get('drivers/{driver}/orders', DriverOrderController::class)
    ->whereNumber('driver')
    ->name('drivers.orders');
