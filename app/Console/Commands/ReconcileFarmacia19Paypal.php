<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReconcileFarmacia19Paypal extends Command
{
    protected $signature = 'farmacia19:paypal:reconcile {--limit=100}';
    protected $description = 'Riconcilia gli ordini PayPal senza catturare pagamenti né creare ordini';

    public function handle(): int
    {
        $limit = max(1, min(500, (int) $this->option('limit')));
        $attempts = DB::table('paypal_checkout_attempts')
            ->whereIn('status', [
                'creating', 'creation_uncertain', 'created', 'approved',
                'capture_uncertain', 'captured', 'captured_pending_order',
            ])
            ->where('updated_at', '<=', now()->subMinutes(2))
            ->orderBy('updated_at')
            ->limit($limit)
            ->get();

        foreach ($attempts as $item) {
            $lock = Cache::lock('farmacia19:checkout:user:' . $item->user_id, 120);
            if (!$lock->get()) continue;
            try {
                $attempt = DB::table('paypal_checkout_attempts')->where('id', $item->id)->first();
                if (!$attempt) continue;

                $order = Order::where('user_id', $attempt->user_id)
                    ->where('order_number', $attempt->order_number)
                    ->where('payment_method', 'paypal')
                    ->where('transaction_id', $attempt->paypal_order_id)
                    ->first();
                if ($order) {
                    DB::table('paypal_checkout_attempts')->where('id', $attempt->id)
                        ->update(['status' => 'completed', 'updated_at' => now()]);
                    continue;
                }

                if (!$attempt->paypal_order_id) {
                    Log::warning('PayPal riconciliazione: ID ordine remoto assente; verifica manuale', [
                        'attempt_id' => $attempt->id, 'status' => $attempt->status,
                    ]);
                    continue;
                }

                $clientId = config('services.paypal_checkout.client_id');
                $secret = config('services.paypal_checkout.client_secret');
                $merchantId = config('services.paypal_checkout.merchant_id');
                if (!$clientId || !$secret || !$merchantId) {
                    throw new \RuntimeException('Configurazione PayPal incompleta');
                }
                $base = config('services.paypal_checkout.mode') === 'live'
                    ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
                $auth = Http::asForm()->withBasicAuth($clientId, $secret)->timeout(15)
                    ->post($base . '/v1/oauth2/token', ['grant_type' => 'client_credentials']);
                if (!$auth->successful() || !$auth->json('access_token')) {
                    throw new \RuntimeException('Autenticazione PayPal fallita');
                }
                $response = Http::withToken($auth->json('access_token'))->acceptJson()->timeout(15)
                    ->get($base . '/v2/checkout/orders/' . rawurlencode($attempt->paypal_order_id));
                if ($response->status() === 404 && $response->json('name') === 'RESOURCE_NOT_FOUND') {
                    $issue = $response->json('details.0.issue');
                    if ($issue === 'INVALID_RESOURCE_ID') {
                        // Un 404 NON dimostra che il pagamento non sia stato acquisito.
                        // Non sblocchiamo il checkout e non modifichiamo lo stato.
                        // Riduciamo il rumore nei log, mantenendo la verifica nei cicli successivi.
                        $logKey = 'farmacia19:paypal:missing-resource:'.$attempt->id;
                        if (Cache::add($logKey, true, now()->addHours(24))) {
                            Log::warning('PayPal RESOURCE_NOT_FOUND: verifica manuale richiesta', [
                                'attempt_id' => $attempt->id,
                                'paypal_order_id' => $attempt->paypal_order_id,
                                'local_status' => $attempt->status,
                                'paypal_debug_id' => $response->json('debug_id'),
                            ]);
                        }
                        continue;
                    }
                }
                if (!$response->successful()) {
                    throw new \RuntimeException('GET PayPal HTTP '.$response->status());
                }
                $data = $response->json();
                $unit = $data['purchase_units'][0] ?? [];
                $expected = number_format((float) $attempt->total, 2, '.', '');
                $valid = ($data['id'] ?? null) === $attempt->paypal_order_id
                    && ($data['intent'] ?? null) === 'CAPTURE'
                    && ($unit['custom_id'] ?? null) === $attempt->order_number
                    && ($unit['payee']['merchant_id'] ?? null) === $merchantId
                    && ($unit['amount']['currency_code'] ?? null) === 'EUR'
                    && number_format((float) ($unit['amount']['value'] ?? 0), 2, '.', '') === $expected;
                if (!$valid) {
                    Log::critical('PayPal riconciliazione: dati ordine remoto non validi', ['attempt_id' => $attempt->id]);
                    continue;
                }
                $captures = $unit['payments']['captures'] ?? [];
                $capture = count($captures) === 1 ? $captures[0] : null;
                $captured = ($data['status'] ?? null) === 'COMPLETED'
                    && ($capture['status'] ?? null) === 'COMPLETED'
                    && !empty($capture['id'])
                    && ($capture['amount']['currency_code'] ?? null) === 'EUR'
                    && number_format((float) ($capture['amount']['value'] ?? 0), 2, '.', '') === $expected;
                if ($captured) {
                    DB::table('paypal_checkout_attempts')->where('id', $attempt->id)
                        ->update([
                            'status' => 'captured_pending_order',
                            'capture_id' => $capture['id'],
                            'updated_at' => now(),
                        ]);
                    Log::critical('PAYPAL INCASSATO SENZA ORDINE: intervento richiesto', [
                        'attempt_id' => $attempt->id,
                        'user_id' => $attempt->user_id,
                        'paypal_order_id' => $attempt->paypal_order_id,
                        'capture_id' => $capture['id'],
                    ]);
                } elseif (in_array($attempt->status, ['captured', 'captured_pending_order'], true)) {
                    Log::critical('PayPal stato locale captured non confermato da PayPal', [
                        'attempt_id' => $attempt->id,
                        'remote_status' => $data['status'] ?? null,
                    ]);
                }
            } catch (Throwable $e) {
                Log::error('PayPal riconciliazione fallita', [
                    'attempt_id' => $item->id,
                    'error' => $e->getMessage(),
                ]);
            } finally {
                $lock->release();
            }
        }
        $this->info('Riconciliazione completata. Verificare i log per le anomalie.');
        return self::SUCCESS;
    }
}
