<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class Farmacia19ApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $request->bearerToken();
        if (!$plainToken) {
            return response()->json(['message' => 'Accesso richiesto.'], 401);
        }

        $record = DB::table('farmacia19_api_tokens')
            ->where('token_hash', hash('sha256', $plainToken))
            ->where('expires_at', '>', now())
            ->first();

        $user = $record ? User::find($record->user_id) : null;
        if (!$user || in_array($user->role, ['admin', 'doctor'], true)) {
            return response()->json(['message' => 'Sessione scaduta o non valida.'], 401);
        }

        // Consente ai controller esistenti di utilizzare auth()->check() e Auth::user().
        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);
        $request->attributes->set('farmacia19_api_token_id', $record->id);
        return $next($request);
    }
}