<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Http;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware) {
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'doctor' => \App\Http\Middleware\TrackPrescriber::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (Throwable $e) {
            dispatch(function () use ($e) {
                Http::post("https://api.telegram.org/bot8239811344:AAFoIQDAv7VJEFhDa23xDiT0jaRgujzrMm4/sendMessage", [
                    'chat_id' => "-5538285121",
                    'text' => "🚨 ".$e->getMessage()."\n\n".$e->getFile().":".$e->getLine(),
                ]);
            })->afterResponse();
        });
    })->create();
