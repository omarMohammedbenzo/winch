<?php

use App\Http\Middleware\SetLocaleFromHeader;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Src\Domain\Orders\Exceptions\NoAvailableDriver;
use Src\Domain\Orders\Exceptions\OrderAlreadyAssigned;
use Src\Domain\Orders\Exceptions\OrderException;
use Src\Domain\Orders\Exceptions\OrderNotFound;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('api', SetLocaleFromHeader::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (OrderException $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            $status = match (true) {
                $e instanceof OrderNotFound => 404,
                $e instanceof OrderAlreadyAssigned => 409,
                $e instanceof NoAvailableDriver => 422,
                default => 422,
            };

            return response()->json([
                'error' => [
                    'code' => $e->errorCode(),
                    'message' => $e->translate(),
                ],
            ], $status);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'error' => [
                    'code' => 'not_found',
                    'message' => __('messages.not_found'),
                ],
            ], 404);
        });
    })->create();
