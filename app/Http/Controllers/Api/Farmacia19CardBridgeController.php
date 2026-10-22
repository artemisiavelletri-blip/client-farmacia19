<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Farmacia19CardBridgeController extends Controller
{
    public function enter(Request $request, string $ticket)
    {
        // Route web con sessione e cookie. Il ticket e' monouso.
        if (!preg_match('/^[A-Za-z0-9]{64}$/', $ticket)) abort(404);
        $key = 'farmacia19_card_bridge_' . hash('sha256', $ticket);
        $userId = Cache::pull($key);
        if (!$userId) abort(403, 'Collegamento scaduto o gia utilizzato.');

        Auth::loginUsingId((int) $userId);
        $request->session()->regenerate();

        return redirect()->route('settingspayment_method')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('Cache-Control', 'no-store');
    }
}
