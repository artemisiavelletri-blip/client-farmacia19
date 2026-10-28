<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\CheckoutController;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class Farmacia19PaypalCheckoutController extends CheckoutController
{
    private function paypalUrl(): string
    {
        return config('services.paypal_checkout.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    private function money($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    private function accessToken(): string
    {
        $clientId = config('services.paypal_checkout.client_id');
        $secret = config('services.paypal_checkout.client_secret');

        if (!$clientId || !$secret) {
            throw new \RuntimeException('Credenziali PayPal mancanti.');
        }

        $response = Http::asForm()
            ->withBasicAuth($clientId, $secret)
            ->timeout(15)
            ->post($this->paypalUrl() . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (!$response->successful()) {
            Log::error('Farmacia19 PayPal autenticazione', [
                'http_status' => $response->status(),
            ]);

            throw new \RuntimeException(
                'Autenticazione PayPal non riuscita.'
            );
        }

        $token = $response->json('access_token');

        if (!$token) {
            throw new \RuntimeException(
                'Token PayPal non disponibile.'
            );
        }

        return $token;
    }

    private function paypalGet(string $orderId): array
    {
        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->timeout(15)
            ->get(
                $this->paypalUrl()
                . '/v2/checkout/orders/'
                . rawurlencode($orderId)
            );

        if (!$response->successful()) {
            throw new \RuntimeException(
                'Impossibile verificare il pagamento PayPal.'
            );
        }

        return $response->json();
    }

    private function cartHash($user): string
    {
        $items = $user->cartItems()
            ->with('product')
            ->orderBy('id')
            ->get();

        return hash('sha256', json_encode([
            'items' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'subtotal' => (string) $item->subtotalnoiva,
                    'vat' => (string) $item->totalvat,
                ];
            })->all(),

            'total' => $this->money($user->cart_total),

            'shipping' => $this->money(
                $user->cart_shipping_cost
            ),

            'discount' => $this->money(
                $user->cart_discount
            ),
        ]));
    }

    private function validateShipping($user, int $addressId): void
    {
        $chosen = Address::where('user_id', $user->id)
            ->where('type', 'shipping')
            ->find($addressId);

        if (!$chosen) {
            throw ValidationException::withMessages([
                'shipping_address' => [
                    'Indirizzo di spedizione non valido.'
                ],
            ]);
        }

        // createOrder() usa shippingAddresses()->first().
        $actual = $user->shippingAddresses()->first();

        if (!$actual || (int) $actual->id !== (int) $chosen->id) {
            throw ValidationException::withMessages([
                'shipping_address' => [
                    'Per questo indirizzo completa l’ordine dal sito.'
                ],
            ]);
        }
    }

    private function attempt($userId, string $requestId)
    {
        return DB::table('paypal_checkout_attempts')
            ->where('user_id', $userId)
            ->where('request_id', $requestId)
            ->first();
    }

    private function updateAttempt($attempt, array $values): void
    {
        DB::table('paypal_checkout_attempts')
            ->where('id', $attempt->id)
            ->update(array_merge($values, [
                'updated_at' => now(),
            ]));
    }

    private function result(string $status, array $extra = [])
    {
        return response()->json(array_merge([
            'status' => $status,
        ], $extra));
    }

    private function validatePaypalOrder(
        array $data,
        $attempt
    ): bool {
        $unit = $data['purchase_units'][0] ?? null;

        if (!is_array($unit)) {
            return false;
        }

        $amount = $unit['amount'] ?? [];

        $merchantId = config(
            'services.paypal_checkout.merchant_id'
        );

        if (!$merchantId) {
            return false;
        }

        return
            ($data['id'] ?? null) === $attempt->paypal_order_id

            && ($data['intent'] ?? null) === 'CAPTURE'

            && ($unit['custom_id'] ?? null)
                === $attempt->order_number

            && ($amount['currency_code'] ?? null) === 'EUR'

            && $this->money($amount['value'] ?? 0)
                === $this->money($attempt->total)

            && ($unit['payee']['merchant_id'] ?? null)
                === $merchantId;
    }

    private function completedCapture(array $data, $attempt): ?array
    {
        if (($data['status'] ?? null) !== 'COMPLETED') {
            return null;
        }

        $captures = $data['purchase_units'][0]
            ['payments']['captures'] ?? [];

        if (count($captures) !== 1) {
            return null;
        }

        $capture = $captures[0];

        if (($capture['status'] ?? null) !== 'COMPLETED') {
            return null;
        }

        if (empty($capture['id'])) {
            return null;
        }

        if (($capture['amount']['currency_code'] ?? null) !== 'EUR') {
            return null;
        }

        if (
            $this->money($capture['amount']['value'] ?? 0)
            !== $this->money($attempt->total)
        ) {
            return null;
        }

        return $capture;
    }

    private function existingOrder($attempt)
    {
        return Order::where('user_id', $attempt->user_id)
            ->where('order_number', $attempt->order_number)
            ->where('payment_method', 'paypal')
            ->where('transaction_id', $attempt->paypal_order_id)
            ->first();
    }

    // Con request_id restituisce solo il tentativo specifico, anche se concluso.
    // Senza request_id restituisce soltanto un tentativo attivo.
    // Non esegue catture e non crea ordini.
    public function recover(Request $request)
    {
        $input = $request->validate([
            'request_id' => ['nullable', 'uuid'],
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Accesso richiesto.'], 401);
        }

        $query = DB::table('paypal_checkout_attempts')
            ->where('user_id', $user->id);

        if (!empty($input['request_id'])) {
            $query->where('request_id', $input['request_id']);
        } else {
            $query->whereIn('status', [
                'creating', 'creation_uncertain', 'created', 'approved',
                'capture_uncertain', 'captured', 'captured_pending_order',
            ]);
        }

        $attempt = $query->orderByDesc('id')->first();
        if (!$attempt) {
            return $this->result('none');
        }

        // Se l'ordine locale esiste, il risultato e' confermato anche
        // quando l'ultimo aggiornamento del tentativo non e' riuscito.
        $existing = $this->existingOrder($attempt);
        if ($existing) {
            return $this->result('completed', [
                'request_id' => $attempt->request_id,
                'order_number' => $existing->order_number,
                'total' => $this->money($attempt->total),
            ]);
        }

        // Non dichiarare completato un tentativo soltanto per il suo stato.
        if ($attempt->status === 'completed') {
            Log::critical('PayPal completed senza ordine locale', [
                'attempt_id' => $attempt->id,
            ]);
            return $this->result('captured_pending_order', [
                'request_id' => $attempt->request_id,
            ]);
        }

        return $this->result($attempt->status, [
            'request_id' => $attempt->request_id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | AVVIO PAYPAL
    |--------------------------------------------------------------------------
    */

    public function start(Request $request)
    {
        $input = $request->validate([
            'shipping_address' => ['required', 'integer'],
            'request_id' => ['required', 'uuid'],
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Accesso richiesto.'
            ], 401);
        }

        $lock = Cache::lock(
            'farmacia19:checkout:user:' . $user->id,
            120
        );

        if (!$lock->get()) {
            return response()->json([
                'message' => 'Checkout già in elaborazione.'
            ], 409);
        }

        try {
            $previous = $this->attempt(
                $user->id,
                $input['request_id']
            );

            if ($previous) {
                if (
                    $previous->status === 'created'
                    && $previous->approval_url
                ) {
                    return response()->json([
                        'approval_url' => $previous->approval_url,
                        'request_id' => $previous->request_id,
                    ]);
                }

                return response()->json([
                    'message' => 'Richiesta già esistente. Verifica il pagamento prima di riprovare.'
                ], 409);
            }

            // Evita più checkout PayPal contemporanei.
            $active = DB::table('paypal_checkout_attempts')
                ->where('user_id', $user->id)
                ->whereIn('status', [
                    'creating',
                    'creation_uncertain',
                    'created',
                    'approved',
                    'capture_uncertain',
                    'captured',
                    'captured_pending_order',
                ])
                ->exists();

            if ($active) {
                return response()->json([
                    'message' => 'Esiste già un pagamento PayPal da verificare.'
                ], 409);
            }

            $cart = $user->cartItems()
                ->with('product')
                ->get();

            if ($cart->isEmpty()) {
                return response()->json([
                    'message' => 'Carrello vuoto.'
                ], 422);
            }

            $this->validateShipping(
                $user,
                (int) $input['shipping_address']
            );

            $total = $this->money($user->cart_total);

            if ((float) $total <= 0) {
                return response()->json([
                    'message' => 'Totale non valido.'
                ], 422);
            }

            do {
                $orderNumber = strtoupper(Str::random(10));
            } while (
                Order::where(
                    'order_number',
                    $orderNumber
                )->exists()
                ||
                DB::table('paypal_checkout_attempts')
                    ->where('order_number', $orderNumber)
                    ->exists()
            );

            DB::table('paypal_checkout_attempts')->insert([
                'user_id' => $user->id,
                'request_id' => $input['request_id'],
                'paypal_order_id' => null,
                'order_number' => $orderNumber,
                'shipping_address_id' => $input['shipping_address'],
                'total' => $total,
                'currency' => 'EUR',
                'cart_hash' => $this->cartHash($user),
                'status' => 'creating',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $response = Http::withToken($this->accessToken())
                ->acceptJson()
                ->withHeaders([
                    'PayPal-Request-Id' => $input['request_id'],
                    'Prefer' => 'return=representation',
                ])
                ->timeout(25)
                ->post(
                    $this->paypalUrl() . '/v2/checkout/orders',
                    [
                        'intent' => 'CAPTURE',

                        'purchase_units' => [[
                            'reference_id' => 'default',
                            'description' => 'Farmacia19 #' . $orderNumber,
                            'custom_id' => $orderNumber,

                            'payee' => [
                                'merchant_id' => config(
                                    'services.paypal_checkout.merchant_id'
                                ),
                            ],

                            'amount' => [
                                'currency_code' => 'EUR',
                                'value' => $total,
                            ],
                        ]],

                        'payment_source' => [
                            'paypal' => [
                                'experience_context' => [
                                    'brand_name' => 'Farmacia19',
                                    'user_action' => 'PAY_NOW',
                                    'shipping_preference' => 'NO_SHIPPING',
                                    'return_url' => url('/paypal/app/return'),
                                    'cancel_url' => url('/paypal/app/cancel'),
                                ],
                            ],
                        ],
                    ]
                );

            if (!$response->successful()) {
                $attempt = $this->attempt(
                    $user->id,
                    $input['request_id']
                );

                $this->updateAttempt($attempt, [
                    'status' => 'creation_uncertain',
                ]);

                Log::error('PayPal create order failed', [
                    'request_id' => $input['request_id'],
                    'http_status' => $response->status(),
                ]);

                return response()->json([
                    'message' => 'Avvio PayPal non riuscito. Non ripetere il pagamento finché la richiesta non è stata verificata.'
                ], 502);
            }

            $data = $response->json();

            $approvalUrl = null;

            foreach ($data['links'] ?? [] as $link) {
                if (
                    in_array(
                        $link['rel'] ?? '',
                        ['payer-action', 'approve'],
                        true
                    )
                ) {
                    $approvalUrl = $link['href'];
                    break;
                }
            }

            $parsedUrl = $approvalUrl
                ? parse_url($approvalUrl)
                : false;

            $host = is_array($parsedUrl)
                ? strtolower($parsedUrl['host'] ?? '')
                : '';

            if (
                empty($data['id'])
                || !$approvalUrl
                || ($parsedUrl['scheme'] ?? null) !== 'https'
                || !(
                    $host === 'paypal.com'
                    || str_ends_with($host, '.paypal.com')
                )
            ) {
                Log::error('Risposta PayPal create incompleta', [
                    'request_id' => $input['request_id'],
                    'paypal_order_id' => $data['id'] ?? null,
                ]);

                $attempt = $this->attempt(
                    $user->id,
                    $input['request_id']
                );

                $this->updateAttempt($attempt, [
                    'paypal_order_id' => $data['id'] ?? null,
                    'status' => 'creation_uncertain',
                ]);

                return response()->json([
                    'message' => 'Risposta PayPal incompleta.'
                ], 502);
            }

            $attempt = $this->attempt(
                $user->id,
                $input['request_id']
            );

            $this->updateAttempt($attempt, [
                'paypal_order_id' => $data['id'],
                'approval_url' => $approvalUrl,
                'status' => 'created',
            ]);

            return response()->json([
                'approval_url' => $approvalUrl,
                'request_id' => $input['request_id'],
            ], 201);

        } catch (Throwable $e) {
            Log::error('Farmacia19 PayPal start exception', [
                'user_id' => $user->id,
                'request_id' => $input['request_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Impossibile avviare PayPal. Verifica lo stato della richiesta prima di riprovare.'
            ], 503);

        } finally {
            $lock->release();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFICA PAYPAL E CATTURA
    |--------------------------------------------------------------------------
    */

    public function status(Request $request)
    {
        $input = $request->validate([
            'request_id' => ['required', 'uuid'],
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Accesso richiesto.'
            ], 401);
        }

        $lock = Cache::lock(
            'farmacia19:checkout:user:' . $user->id,
            120
        );

        if (!$lock->get()) {
            return response()->json([
                'message' => 'Pagamento già in elaborazione.'
            ], 409);
        }

        try {
            $attempt = $this->attempt(
                $user->id,
                $input['request_id']
            );

            if (!$attempt) {
                return response()->json([
                    'message' => 'Pagamento non trovato.'
                ], 404);
            }

            // Controllo persistente: createOrder() potrebbe aver
            // salvato l'ordine prima di un errore nelle notifiche.
            $existing = $this->existingOrder($attempt);

            if ($existing) {
                $this->updateAttempt($attempt, [
                    'status' => 'completed',
                ]);

                return $this->result('completed', [
                    'order_number' => $existing->order_number,
                ]);
            }

            if ($attempt->status === 'cancelled') {
                return $this->result('cancelled');
            }

            if (!$attempt->paypal_order_id) {
                return $this->result('pending', [
                    'message' => 'Creazione PayPal da verificare.'
                ]);
            }

            $data = $this->paypalGet(
                $attempt->paypal_order_id
            );

            if (!$this->validatePaypalOrder($data, $attempt)) {
                Log::critical('PayPal order verification failed', [
                    'attempt_id' => $attempt->id,
                    'paypal_order_id' => $attempt->paypal_order_id,
                ]);

                return $this->result('pending', [
                    'message' => 'Verifica dati PayPal non riuscita.'
                ]);
            }

            $capture = $this->completedCapture(
                $data,
                $attempt
            );

            // Se la cattura era già riuscita, non ripeterla.
            if (!$capture) {
                $paypalStatus = $data['status'] ?? null;

                if ($paypalStatus !== 'APPROVED') {
                    return $this->result('pending', [
                        'paypal_status' => $paypalStatus,
                    ]);
                }

                // Il carrello non deve essere cambiato
                // dopo la creazione dell'ordine PayPal.
                if (
                    $this->cartHash($user)
                    !== $attempt->cart_hash
                ) {
                    return $this->result('pending', [
                        'message' => 'Il carrello è cambiato. Pagamento non acquisito.'
                    ]);
                }

                $this->validateShipping(
                    $user,
                    (int) $attempt->shipping_address_id
                );

                $this->updateAttempt($attempt, [
                    'status' => 'capture_uncertain',
                ]);

                // Chiave stabile per tutti i tentativi di cattura
                // relativi allo stesso ordine.
                $captureRequestId = 'f19-capture-' . $attempt->id;

                $response = Http::withToken(
                    $this->accessToken()
                )
                    ->acceptJson()
                    ->withHeaders([
                        'PayPal-Request-Id' => $captureRequestId,
                        'Prefer' => 'return=representation',
                    ])
                    ->withBody('{}', 'application/json')
                    ->timeout(25)
                    ->post(
                        $this->paypalUrl()
                        . '/v2/checkout/orders/'
                        . rawurlencode($attempt->paypal_order_id)
                        . '/capture'
                    );

                if (!$response->successful()) {
                    // Non sappiamo ancora se PayPal abbia
                    // acquisito il pagamento.
                    Log::error('PayPal capture uncertain', [
                        'attempt_id' => $attempt->id,
                        'http_status' => $response->status(),
                    ]);

                    return $this->result('pending', [
                        'message' => 'Esito PayPal in verifica. Non effettuare un secondo pagamento.'
                    ]);
                }

                // Rileggiamo PayPal per verificare il risultato.
                $data = $this->paypalGet(
                    $attempt->paypal_order_id
                );

                if (!$this->validatePaypalOrder($data, $attempt)) {
                    return $this->result('pending', [
                        'message' => 'Verifica PayPal in corso.'
                    ]);
                }

                $capture = $this->completedCapture(
                    $data,
                    $attempt
                );

                if (!$capture) {
                    return $this->result('pending', [
                        'message' => 'Pagamento non ancora confermato.'
                    ]);
                }
            }

            // Da qui PayPal ha confermato l'incasso.
            $this->updateAttempt($attempt, [
                'status' => 'captured',
                'capture_id' => $capture['id'],
            ]);

            // Non registriamo un ordine con carrello differente.
            if (
                $this->cartHash($user)
                !== $attempt->cart_hash
            ) {
                $this->updateAttempt($attempt, [
                    'status' => 'captured_pending_order',
                ]);

                Log::critical('PayPal captured but cart changed', [
                    'attempt_id' => $attempt->id,
                    'capture_id' => $capture['id'],
                ]);

                return $this->result('captured_pending_order');
            }

            try {
                $this->validateShipping(
                    $user,
                    (int) $attempt->shipping_address_id
                );
            } catch (Throwable $e) {
                $this->updateAttempt($attempt, [
                    'status' => 'captured_pending_order',
                ]);

                return $this->result('captured_pending_order');
            }

            // Controllo aggiuntivo prima di createOrder().
            $existing = $this->existingOrder($attempt);

            if ($existing) {
                $this->updateAttempt($attempt, [
                    'status' => 'completed',
                ]);

                return $this->result('completed', [
                    'order_number' => $existing->order_number,
                ]);
            }

            try {
                /*
                 * createOrder() resta INVARIATA.
                 *
                 * transaction_id = ID ordine PayPal,
                 * coerente con il checkout web esistente.
                 */
                $orderNumber = $this->createOrder(
                    $user,
                    (float) $attempt->total,
                    'paypal',
                    $attempt->paypal_order_id,
                    $attempt->order_number
                );

                $this->updateAttempt($attempt, [
                    'status' => 'completed',
                ]);

                return $this->result('completed', [
                    'order_number' => $orderNumber,
                ]);

            } catch (Throwable $e) {
                Log::critical('PayPal captured, local order error', [
                    'attempt_id' => $attempt->id,
                    'paypal_order_id' => $attempt->paypal_order_id,
                    'capture_id' => $capture['id'],
                    'error' => $e->getMessage(),
                ]);

                // createOrder() potrebbe avere già creato
                // l'ordine prima dell'errore Telegram/Gmail.
                $existing = $this->existingOrder($attempt);

                if ($existing) {
                    $this->updateAttempt($attempt, [
                        'status' => 'completed',
                    ]);

                    return $this->result('completed', [
                        'order_number' => $existing->order_number,
                    ]);
                }

                $this->updateAttempt($attempt, [
                    'status' => 'captured_pending_order',
                ]);

                return $this->result('captured_pending_order');
            }

        } catch (Throwable $e) {
            Log::error('Farmacia19 PayPal status exception', [
                'user_id' => $user->id,
                'request_id' => $input['request_id'],
                'error' => $e->getMessage(),
            ]);

            $attempt = $this->attempt(
                $user->id,
                $input['request_id']
            );

            if (
                $attempt
                && in_array($attempt->status, [
                    'captured',
                    'captured_pending_order',
                ], true)
            ) {
                return $this->result('captured_pending_order');
            }

            return response()->json([
                'message' => 'Verifica PayPal temporaneamente non disponibile. Non ripetere il pagamento.'
            ], 503);

        } finally {
            $lock->release();
        }
    }
}