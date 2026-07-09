<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
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
            // Ignora gli errori di validazione
            if ($e instanceof ValidationException) {
                return;
            }

            // Se è un HttpException ma NON è 500, ignora
            if ($e instanceof HttpExceptionInterface && $e->getStatusCode() !== 500) {
                return;
            }

            try {

                $request = request();
                $user = auth()->user();

                $text = "🚨 ERRORE 500 LARAVEL\n\n";
                $text .= "🌐 Ambiente: ".app()->environment()."\n";
                $text .= "🕒 Data: ".now()->format('d/m/Y H:i:s')."\n\n";

                if ($request) {
                    $text .= "🔗 URL: ".$request->fullUrl()."\n";
                    $text .= "📌 Metodo: ".$request->method()."\n";
                    $text .= "🌍 IP: ".$request->ip()."\n";
                    $text .= "🖥 Browser: ".$request->userAgent()."\n";

                    if ($request->route()) {
                        $text .= "🛣 Route: ".$request->route()->getName()."\n";
                        $text .= "🎯 Action: ".$request->route()->getActionName()."\n";
                    }

                    $input = $request->except([
                        '_token',
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]);

                    if (!empty($input)) {
                        $text .= "\n📦 Input:\n";
                        $text .= json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    }
                }

                $text .= "\n\n";

                if ($user) {
                    $text .= "👤 Utente: {$user->id} - {$user->name} - {$user->email}\n\n";
                } else {
                    $text .= "👤 Utente: Guest\n\n";
                }

                $text .= "❌ Errore:\n";
                $text .= $e->getMessage()."\n\n";

                $text .= "📄 File:\n";
                $text .= $e->getFile().":".$e->getLine()."\n\n";

                $trace = substr($e->getTraceAsString(), 0, 1500);

                $text .= "📚 Trace:\n";
                $text .= $trace;

                Http::timeout(5)->post(
                    "https://api.telegram.org/bot8239811344:AAFoIQDAv7VJEFhDa23xDiT0jaRgujzrMm4/sendMessage",
                    [
                        'chat_id' => "-5538285121",
                        'text' => substr($text, 0, 4000),
                    ]
                );

            } catch (\Throwable $ignore) {
                // Evita loop infiniti
            }
            /*Http::post("https://api.telegram.org/bot8239811344:AAFoIQDAv7VJEFhDa23xDiT0jaRgujzrMm4/sendMessage", [
                'chat_id' => "-5538285121",
                'text' => "🚨 ".$e->getMessage()."\n\n".$e->getFile().":".$e->getLine(),
            ]);*/

        });
    })->create();
