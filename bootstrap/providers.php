<?php

use App\Providers\AppServiceProvider;
use Src\Domain\Drivers\Providers\DriversServiceProvider;
use Src\Domain\Orders\Providers\OrdersServiceProvider;
use Src\Presentation\Admin\Providers\AdminRouteServiceProvider;

return [
    AppServiceProvider::class,

    // Domain bounded contexts
    OrdersServiceProvider::class,
    DriversServiceProvider::class,

    // Presentation cpanels
    AdminRouteServiceProvider::class,
];
