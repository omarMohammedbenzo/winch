<?php

declare(strict_types=1);

namespace Src\Domain\Orders\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Domain\Orders\Actions\AssignOrder;
use Src\Domain\Orders\Contracts\OrderAssigner;

/**
 * Wires the Orders bounded context.
 *
 * Contract -> implementation bindings for this domain are registered here,
 * so the rest of the app depends only on the domain's public Contracts.
 */
class OrdersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderAssigner::class, AssignOrder::class);
    }

    public function boot(): void
    {
        //
    }
}
