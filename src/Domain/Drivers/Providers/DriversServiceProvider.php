<?php

declare(strict_types=1);

namespace Src\Domain\Drivers\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Domain\Drivers\Contracts\DriverFinder;
use Src\Domain\Drivers\Services\NearestAvailableDriverFinder;

/**
 * Wires the Drivers bounded context.
 *
 * The Drivers domain is "upstream": it exposes a Contract (the driver finder)
 * that other domains consume without touching its internals.
 */
class DriversServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DriverFinder::class, NearestAvailableDriverFinder::class);
    }

    public function boot(): void
    {
        //
    }
}
