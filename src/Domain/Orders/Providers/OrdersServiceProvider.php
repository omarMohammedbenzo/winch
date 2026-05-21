<?php

declare(strict_types=1);

namespace Src\Domain\Orders\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Contract bindings are added in later stages, e.g.:
        // $this->app->bind(OrderAssignerContract::class, AssignOrderAction::class);
    }

    public function boot(): void
    {
        //
    }
}
