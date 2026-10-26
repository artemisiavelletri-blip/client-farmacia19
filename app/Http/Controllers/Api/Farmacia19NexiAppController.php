<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Farmacia19NexiAppController extends Controller
{
    public function start(Request $request)
    {
        $ticket = (string) $request->input('ticket', '');
        abort_unless(preg_match('/^[A-Za-z0-9]{64}$/D', $ticket), 403);
        $userId = Cache::pull('farmacia19_card_bridge_' . hash('sha256', $ticket));
        abort_unless($userId, 403, 'Richiesta scaduta.');

        $alias = config('services.nexi.alias');
        $secret = config('services.nexi.secret');
        $endpoint = config('services.nexi.base_url');
        abort_unless($alias && $secret && $endpoint && str_starts_with($endpoint, 'https://'), 503);

        $codTrans = 'PS' . now()->format('YmdHis') . strtoupper(Str::random(12));
        $contract = 'NC_' . now()->format('YmdHis') . strtoupper(Str::random(12));
        Cache::put('nexi_payment_' . $codTrans, [
            'user_id' => (int) $userId,
            'tipo_richiesta' => 'PP',
            'num_contratto' => $contract,
            'state' => 'pending',
        ], now()->addMinutes(30));

        $amount = 0;
        $currency = 'EUR';
        $params = [
            'alias' => $alias,
            'importo' => $amount,
            'divisa' => $currency,
            'codTrans' => $codTrans,
            'url' => route('farmacia19.nexi.app.callback'),
            'url_back' => route('farmacia19.nexi.app.complete'),
            'mac' => sha1('codTrans=' . $codTrans . 'divisa=' . $currency . 'importo=' . $amount . $secret),
            'num_contratto' => $contract,
            'tipo_servizio' => 'paga_oc3d',
            'tipo_richiesta' => 'PP',
        ];

        return response()->view('app.nexi-auto-post', compact('endpoint', 'params'))
            ->header('Cache-Control', 'no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function callback(Request $request)
    {
        // SICUREZZA: non salvare alcuna carta finché il MAC di risposta
        // non è verificato con la specifica ufficiale Nexi del proprio contratto.
        // Il callback del sito esistente NON deve essere riutilizzato senza verifica.
        \Log::warning('Callback PP app non attivato: verifica MAC Nexi da implementare', [
            'codTrans' => $request->input('codTrans'),
        ]);
        return response('Registrazione non completata: verifica Nexi da configurare.', 503);
    }

    public function complete()
    {
        return response('<!doctype html><html><body>Puoi tornare all’app Farmacia19.</body></html>')
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'no-store');
    }
}
