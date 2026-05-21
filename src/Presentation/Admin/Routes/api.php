<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Cpanel API Routes
|--------------------------------------------------------------------------
| Loaded by AdminRouteServiceProvider under the "api" middleware group and
| the "/api" prefix. Domain endpoints are added in later stages.
*/

Route::get('health', fn () => response()->json([
    'status' => 'ok',
    'service' => 'winch',
]));
