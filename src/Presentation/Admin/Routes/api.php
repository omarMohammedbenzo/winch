<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Presentation\Admin\Controllers\DriverOrderController;
use Src\Presentation\Admin\Controllers\OrderAssignmentController;

/*
|--------------------------------------------------------------------------
| Admin Cpanel API Routes
|--------------------------------------------------------------------------
| Loaded by AdminRouteServiceProvider under the "api" middleware group and
| the "/api" prefix.
*/

Route::post('orders/{order}/assign', OrderAssignmentController::class)
    ->whereNumber('order')
    ->name('orders.assign');

Route::get('drivers/{driver}/orders', DriverOrderController::class)
    ->name('drivers.orders');
