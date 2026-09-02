<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
        $middleware->alias([
            'throttle.connector.test' => \App\Http\Middleware\ThrottleConnectorTest::class,
            'api.key'                 => \App\Http\Middleware\AuthenticateApiKey::class,
            'scope'                   => \App\Http\Middleware\EnsureApiScope::class,
            'webhook.verify'          => \App\Http\Middleware\VerifyWebhookSignature::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();