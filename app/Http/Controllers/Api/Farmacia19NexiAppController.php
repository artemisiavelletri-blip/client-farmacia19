<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            'importo' => 0,
            'divisa' => 'EUR',
            'state' => 'pending',
        ], now()->addMinutes(30));

        $params = [
            'alias' => $alias,
            'importo' => 0,
            'divisa' => 'EUR',
            'codTrans' => $codTrans,
            'url' => route('farmacia19.nexi.app.callback'),
            'url_back' => route('farmacia19.nexi.app.complete'),
            'mac' => sha1('codTrans=' . $codTrans . 'divisa=EURimporto=0' . $secret),
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
        $codTrans = (string) $request->input('codTrans', '');
        if (!preg_match('/^PS[A-Za-z0-9]{12,80}$/D', $codTrans)) {
            return $this->completeWithMessage('Operazione non valida.', 400);
        }

        $cacheKey = 'nexi_payment_' . $codTrans;
        $payment = Cache::get($cacheKey);
        if (!$payment || ($payment['tipo_richiesta'] ?? null) !== 'PP'
            || ($payment['state'] ?? null) !== 'pending') {
            return $this->completeWithMessage('Richiesta scaduta o già elaborata.', 409);
        }

        // IMPORTANTE: la formula esatta va confermata sulla documentazione
        // Nexi XPay OneClick relativa al contratto in uso prima del rilascio.
        // Non sostituire questo controllo con il solo codiceEsito == 0.
        if (!$this->verifyNexiResponseMac($request, $payment)) {
            Log::warning('MAC Nexi PP non valido', ['codTrans' => $codTrans]);
            return $this->completeWithMessage('Impossibile verificare la risposta Nexi.', 403);
        }

        if (!hash_equals((string) ($payment['num_contratto'] ?? ''),
            (string) $request->input('num_contratto', ''))) {
            return $this->completeWithMessage('Contratto Nexi non valido.', 400);
        }

        if (in_array(strtoupper((string) $request->input('tipoTransazione', '')),
            ['APPLEPAY', 'GOOGLEPAY'], true)) {
            return $this->completeWithMessage('Tipo di carta non supportato.', 400);
        }

        $codiceEsito = filter_var($request->input('codiceEsito'), FILTER_VALIDATE_INT);
        if ($codiceEsito === false || $codiceEsito !== 0
            || strtoupper((string) $request->input('esito', '')) !== 'OK') {
            $erroriUtente = [
                116 => 'Operazione annullata.', 121 => 'Sessione scaduta. Riprova.',
                400 => 'Operazione non autorizzata.', 401 => 'Carta scaduta o data non corretta.',
                402 => 'Carta non valida.', 404 => 'Operazione rifiutata dalla banca.',
                405 => 'Fondi insufficienti.', 407 => 'Impossibile contattare la banca.',
                408 => 'È richiesta una nuova autenticazione.',
                409 => 'Operazione rifiutata per sicurezza.',
                410 => 'Troppi tentativi non riusciti.', 411 => 'Contatta la tua banca.',
                412 => 'Carta bloccata o non utilizzabile.', 414 => 'Limite di spesa raggiunto.',
            ];
            return $this->completeWithMessage(
                $erroriUtente[$codiceEsito] ?? 'Impossibile registrare la carta.', 400
            );
        }

        $scadenza = (string) $request->input('scadenza_pan', '');
        if (!preg_match('/^\d{6}$/D', $scadenza)
            || (int) substr($scadenza, 4, 2) < 1
            || (int) substr($scadenza, 4, 2) > 12) {
            return $this->completeWithMessage('Scadenza carta non valida.', 400);
        }

        try {
            // Il lock evita elaborazioni concorrenti del medesimo callback.
            $lock = Cache::lock('nexi_pp_callback_' . hash('sha256', $codTrans), 20);
            if (!$lock->get()) {
                return $this->completeWithMessage('Registrazione già in elaborazione.', 409);
            }
            try {
                if (!Cache::has($cacheKey)) {
                    return $this->completeWithMessage('Richiesta già elaborata.', 409);
                }
                DB::transaction(function () use ($request, $payment, $scadenza) {
                    $user = User::whereKey((int) $payment['user_id'])->lockForUpdate()->firstOrFail();
                    $contract = (string) $payment['num_contratto'];
                    $existing = PaymentMethod::withTrashed()
                        ->where('user_id', $user->id)
                        ->where('stripe_payment_method_id', $contract)->first();
                    if ($existing) {
                        return;
                    }
                    PaymentMethod::where('user_id', $user->id)
                        ->update(['default' => 0, 'is_default' => 0]);
                    $pan = (string) $request->input('pan', '');
                    PaymentMethod::create([
                        'holder_name' => trim((string) $request->input('nome', '') . ' ' .
                            (string) $request->input('cognome', '')),
                        'user_id' => $user->id,
                        'stripe_payment_method_id' => $contract,
                        'brand' => $request->input('brand'),
                        'last4' => $pan === '' ? null : substr($pan, -4),
                        'exp_month' => substr($scadenza, 4, 2),
                        'exp_year' => substr($scadenza, 0, 4),
                        'is_default' => 1,
                        'default' => 1,
                    ]);
                });
                Cache::forget($cacheKey);
            } finally {
                $lock->release();
            }
            return $this->completeWithMessage('Carta registrata correttamente. Puoi tornare all’app.');
        } catch (\Throwable $e) {
            Log::error('Errore registrazione carta Nexi app', [
                'codTrans' => $codTrans, 'error' => $e->getMessage(),
            ]);
            return $this->completeWithMessage('Impossibile registrare la carta.', 500);
        }
    }

    private function verifyNexiResponseMac(Request $request, array $payment): bool
    {
        $secret = (string) config('services.nexi.secret', '');
        $receivedMac = strtolower((string) $request->input('mac', ''));
        if ($secret === '' || !preg_match('/^[a-f0-9]{40}$/D', $receivedMac)) {
            return false;
        }
        // Formula di risposta XPay classico da CONFERMARE con la specifica Nexi
        // del proprio profilo OneClick prima di abilitare in produzione.
        $expected = sha1('codTrans=' . (string) $request->input('codTrans', '')
            . 'esito=' . (string) $request->input('esito', '')
            . 'importo=' . (string) $request->input('importo', '')
            . 'divisa=' . (string) $request->input('divisa', '') . $secret);
        if ((string) $request->input('importo', '') !== (string) ($payment['importo'] ?? '')
            || (string) $request->input('divisa', '') !== (string) ($payment['divisa'] ?? '')) {
            return false;
        }
        return hash_equals($expected, $receivedMac);
    }

    private function completeWithMessage(string $message, int $status = 200)
    {
        return response('<!doctype html><html lang="it"><head><meta charset="utf-8"></head><body><p>'
            . e($message) . '</p></body></html>', $status)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'no-store');
    }

    public function complete()
    {
        return $this->completeWithMessage('Puoi tornare all’app Farmacia19.');
    }
}
