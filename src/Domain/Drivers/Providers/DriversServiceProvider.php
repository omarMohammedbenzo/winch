<?php

declare(strict_types=1);

namespace Src\Domain\Drivers\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Wires the Drivers bounded context.
 *
 * The Drivers domain is "upstream": it exposes a Contract (e.g. a driver
 * finder) that other domains consume without touching its internals.
 */
class DriversServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // e.g. $this->app->bind(DriverFinderContract::class, NearestAvailableDriverFinder::class);
    }

    public function boot(): void
    {
        //
    }
}
