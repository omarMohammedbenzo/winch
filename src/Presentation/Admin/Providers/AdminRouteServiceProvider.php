<?php

declare(strict_types=1);

namespace Src\Presentation\Admin\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Loads the Admin control-panel API routes.
 *
 * Keeping route registration inside the Presentation layer means each cpanel
 * owns its own routes file, instead of dumping everything in routes/api.php.
 */
class AdminRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('src/Presentation/Admin/Routes/api.php'));
    }
}
