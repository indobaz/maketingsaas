<?php

use App\Http\Middleware\CheckCompanySetup;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'company.setup' => CheckCompanySetup::class,
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (HttpExceptionInterface $e, $request) {
            if ($request->expectsJson()) {
                return null;
            }

            $status = $e->getStatusCode();

            if (! in_array($status, [403, 404], true)) {
                return null;
            }

            // Dot notation: works before the `errors::` namespace is registered for HTTP exceptions.
            $view = 'errors.'.$status;

            if (! view()->exists($view)) {
                return null;
            }

            return response()->view($view, [
                'errors' => new ViewErrorBag,
                'exception' => $e,
            ], $status, $e->getHeaders());
        });
    })->create();
