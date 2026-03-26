<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;

class TrackPrescriber
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Se c'è il parametro ?id
        if ($request->has('id')) {

            $prescriberId = $request->id;

            // Salva in sessione
            if (!session()->has('prescriber_id')) {
                session(['prescriber_id' => $prescriberId]);
            }

            // Se l'utente è loggato, salva nel DB
            if (Auth::check()) {
                $user = Auth::user();

                // Salva solo se non è già presente
                if (!$user->prescriber_id) {
                    $user->prescriber_id = $prescriberId;
                    $user->save();
                }
            }
        }

        return $next($request);
    }
}
