<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\CheckoutController;
use App\Models\Address;
use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Nexi XPay OneClick hosted redirect, isolated from the website checkout.
 * IMPORTANT: verify the exact response-MAC specification for your XPay contract
 * with Nexi before enabling this controller in production.
 */
class Farmacia19NexiCheckoutController extends CheckoutController
{
    private function table()
    {
        return DB::table('nexi_checkout_attempts');
    }

    private function attempt(int $userId, string $requestId)
    {
        return $this->table()->where('user_id', $userId)
            ->where('request_id', $requestId)->first();
    }

    private function cartHash($user): string
    {
        $items = $user->cartItems()->with('product')->orderBy('id')->get();
        return hash('sha256', json_encode([
            'items' => $items->map(fn ($i) => [
                'id' => $i->id, 'product_id' => $i->product_id,
                'quantity' => $i->quantity,
                'subtotal' => (string) $i->subtotalnoiva,
                'vat' => (string) $i->totalvat,
            ])->all(),
            'total' => number_format((float) $user->cart_total, 2, '.', ''),
            'shipping' => number_format((float) $user->cart_shipping_cost, 2, '.', ''),
            'discount' => number_format((float) $user->cart_discount, 2, '.', ''),
        ]));
    }

    private function validateShipping($user, int $addressId): void
    {
        $chosen = Address::where('user_id', $user->id)
            ->where('type', 'shipping')->find($addressId);
        $actual = $user->shippingAddresses()->first();
        if (!$chosen || !$actual || (int) $chosen->id !== (int) $actual->id) {
            throw ValidationException::withMessages([
                'shipping_address' => ['Indirizzo non compatibile con il checkout attuale.'],
            ]);
        }
    }

    private function existingOrder($attempt)
    {
        return Order::where('user_id', $attempt->user_id)
            ->where('order_number', $attempt->order_number)
            ->where('payment_method', 'stripe')
            ->where('transaction_id', $attempt->cod_trans)->first();
    }

    private function updateAttempt($attempt, array $values): void
    {
        $this->table()->where('id', $attempt->id)
            ->update(array_merge($values, ['updated_at' => now()]));
    }

    private function state($attempt): array
    {
        $order = $this->existingOrder($attempt);
        return [
            'status' => $order ? 'completed' : $attempt->status,
            'request_id' => $attempt->request_id,
            'order_number' => $order?->order_number,
        ];
    }

    public function start(Request $request)
    {
        $data = $request->validate([
            'request_id' => ['required', 'uuid'],
            'shipping_address' => ['required', 'integer'],
            'card_id' => ['nullable', 'integer'],
            'new_card' => ['nullable', 'boolean'],
        ]);
        $newCard = (bool) ($data['new_card'] ?? false);
        if ($newCard === isset($data['card_id'])) {
            throw ValidationException::withMessages([
                'card_id' => ['Seleziona una carta salvata oppure una nuova carta.'],
            ]);
        }
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Accesso richiesto.'], 401);

        $lock = Cache::lock('farmacia19:checkout:user:' . $user->id, 120);
        if (!$lock->get()) return response()->json(['message' => 'Checkout occupato.'], 409);
        try {
            $previous = $this->attempt((int) $user->id, $data['request_id']);
            if ($previous) {
                $result = $this->state($previous);
                if ($previous->status === 'created' && now()->lt($previous->expires_at)) {
                    $result['payment_url'] = route('farmacia19.nexi.app.pay', ['codTrans' => $previous->cod_trans]);
                }
                return response()->json($result);
            }
            $active = $this->table()->where('user_id', $user->id)
                ->whereIn('status', ['created', 'pending', 'paid_pending_order'])
                ->exists();
            if ($active) return response()->json([
                'message' => 'Un pagamento Nexi è ancora da verificare.'], 409);

            if (!$user->cartItems()->exists()) {
                return response()->json(['message' => 'Carrello vuoto.'], 422);
            }
            $this->validateShipping($user, (int) $data['shipping_address']);
            $amount = (int) round((float) $user->cart_total * 100);
            if ($amount <= 0) return response()->json(['message' => 'Totale non valido.'], 422);

            $card = null;
            if (!$newCard) {
                $card = PaymentMethod::where('user_id', $user->id)
                    ->whereKey($data['card_id'])->firstOrFail();
                if (!$card->stripe_payment_method_id) {
                    return response()->json(['message' => 'Contratto Nexi mancante.'], 422);
                }
            }
            $codTrans = 'PS' . strtoupper(bin2hex(random_bytes(10)));
            $orderNumber = strtoupper(Str::random(10));
            while (Order::where('order_number', $orderNumber)->exists()
                || $this->table()->where('order_number', $orderNumber)->exists()) {
                $orderNumber = strtoupper(Str::random(10));
            }
            $contract = $newCard
                ? 'NC_' . strtoupper(bin2hex(random_bytes(12)))
                : (string) $card->stripe_payment_method_id;

            $this->table()->insert([
                'user_id' => $user->id,
                'request_id' => $data['request_id'],
                'cod_trans' => $codTrans,
                'order_number' => $orderNumber,
                'shipping_address_id' => $data['shipping_address'],
                'payment_method_id' => $card?->id,
                'tipo_richiesta' => $newCard ? 'PP' : 'PR',
                'num_contratto' => $contract,
                'amount_cents' => $amount,
                'currency' => 'EUR',
                'cart_hash' => $this->cartHash($user),
                'status' => 'created',
                'expires_at' => now()->addMinutes(30),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            return response()->json([
                'status' => 'created',
                'request_id' => $data['request_id'],
                'payment_url' => route('farmacia19.nexi.app.pay', ['codTrans' => $codTrans]),
            ], 201);
        } finally {
            $lock->release();
        }
    }

    /** Hosted form endpoint: only an opaque transaction ID, never an API token. */
    public function pay(Request $request, string $codTrans)
    {
        $attempt = $this->table()->where('cod_trans', $codTrans)->firstOrFail();
        abort_unless($attempt->status === 'created' && now()->lt($attempt->expires_at), 410);
        $secret = (string) config('services.nexi.secret');
        $alias = (string) config('services.nexi.alias');
        $endpoint = (string) config('services.nexi.base_url');
        abort_unless($secret && $alias && filter_var($endpoint, FILTER_VALIDATE_URL)
            && parse_url($endpoint, PHP_URL_SCHEME) === 'https', 503);

        $params = [
            'alias' => $alias,
            'importo' => $attempt->amount_cents,
            'divisa' => $attempt->currency,
            'codTrans' => $attempt->cod_trans,
            'url' => route('farmacia19.nexi.checkout.callback'),
            'url_back' => route('farmacia19.nexi.app.return', ['codTrans' => $attempt->cod_trans]),
            'num_contratto' => $attempt->num_contratto,
            'tipo_servizio' => 'paga_oc3d',
            'tipo_richiesta' => $attempt->tipo_richiesta,
        ];
        $params['mac'] = sha1('codTrans=' . $params['codTrans']
            . 'divisa=' . $params['divisa'] . 'importo=' . $params['importo'] . $secret);

        // Do not generate payment fields from browser input.
        $html = '<!doctype html><html lang="it"><head><meta charset="utf-8">'
            . '<meta name="referrer" content="no-referrer"><title>Pagamento Nexi</title></head>'
            . '<body><p>Collegamento sicuro a Nexi…</p><form id="nexi" method="post" action="'
            . e($endpoint) . '">';
        foreach ($params as $name => $value) {
            $html .= '<input type="hidden" name="' . e($name) . '" value="' . e((string) $value) . '">';
        }
        $html .= '</form><script>document.getElementById("nexi").submit()</script></body></html>';
        return response($html)->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'no-store')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('Content-Security-Policy', "default-src 'none'; script-src 'unsafe-inline'; form-action " . $endpoint);
    }

    /**
     * Browser return is not proof of payment. The MAC formula below MUST be
     * checked against the signed-response spec of the deployed Nexi XPay product.
     */
    public function callback(Request $request)
    {
        $codTrans = (string) $request->input('codTrans', '');
        $attempt = $this->table()->where('cod_trans', $codTrans)->first();
        if (!$attempt) abort(404);
        $lock = Cache::lock('farmacia19:checkout:user:' . $attempt->user_id, 120);
        if (!$lock->get()) return response('Verifica pagamento in corso.', 409);
        try {
            if ($this->existingOrder($attempt) || in_array($attempt->status, ['completed', 'paid_pending_order'], true)) {
                return redirect()->route('farmacia19.nexi.app.return', ['codTrans' => $codTrans]);
            }
            $secret = (string) config('services.nexi.secret');
            $mac = (string) $request->input('mac', '');
            $esito = (string) $request->input('esito', '');
            $importo = (string) $request->input('importo', '');
            $divisa = (string) $request->input('divisa', '');
            $expected = sha1('codTrans=' . $codTrans . 'esito=' . $esito
                . 'importo=' . $importo . 'divisa=' . $divisa . $secret);
            if (!$secret || !$mac || !hash_equals(strtolower($expected), strtolower($mac))
                || !ctype_digit($importo) || (int) $importo !== (int) $attempt->amount_cents
                || $divisa !== 'EUR') {
                Log::warning('Nexi app callback rejected', ['attempt_id' => $attempt->id]);
                abort(403, 'Verifica Nexi non riuscita.');
            }
            if ($esito !== 'OK' || (string) $request->input('codiceEsito', '') !== '0') {
                $this->updateAttempt($attempt, ['status' => 'failed', 'gateway_result' => $esito]);
                return redirect()->route('farmacia19.nexi.app.return', ['codTrans' => $codTrans]);
            }
            if ($attempt->tipo_richiesta === 'PP'
                && (string) $request->input('num_contratto') !== $attempt->num_contratto) {
                abort(403, 'Contratto Nexi non valido.');
            }
            $this->updateAttempt($attempt, [
                'status' => 'paid_pending_order', 'gateway_result' => $esito,
                'verified_at' => now(),
            ]);
            // Order finalization occurs in authenticated status(), not browser callback.
            return redirect()->route('farmacia19.nexi.app.return', ['codTrans' => $codTrans]);
        } finally {
            $lock->release();
        }
    }

    public function returned(Request $request, string $codTrans)
    {
        // No success assertion in the browser page: status API verifies it.
        abort_unless($this->table()->where('cod_trans', $codTrans)->exists(), 404);
        return response('<!doctype html><html lang="it"><meta charset="utf-8"><body>'
            . '<h3>Torna all’app Farmacia19</h3><p>Il pagamento è in verifica. '
            . 'Attendi la conferma nell’app prima di effettuare altri tentativi.</p></body></html>')
            ->header('Cache-Control', 'no-store');
    }

    public function recover(Request $request)
    {
        $data = $request->validate(['request_id' => ['nullable', 'uuid']]);
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Accesso richiesto.'], 401);
        $query = $this->table()->where('user_id', $user->id);
        if (!empty($data['request_id'])) $query->where('request_id', $data['request_id']);
        else $query->whereIn('status', ['created', 'pending', 'paid_pending_order']);
        $attempt = $query->orderByDesc('id')->first();
        return response()->json($attempt ? $this->state($attempt) : ['status' => 'none']);
    }

    public function status(Request $request)
    {
        $data = $request->validate(['request_id' => ['required', 'uuid']]);
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Accesso richiesto.'], 401);
        $lock = Cache::lock('farmacia19:checkout:user:' . $user->id, 120);
        if (!$lock->get()) return response()->json(['status' => 'pending'], 409);
        try {
            $attempt = $this->attempt((int) $user->id, $data['request_id']);
            if (!$attempt) return response()->json(['message' => 'Tentativo inesistente.'], 404);
            if ($this->existingOrder($attempt)) return response()->json($this->state($attempt));
            if ($attempt->status !== 'paid_pending_order' || !$attempt->verified_at) {
                return response()->json($this->state($attempt));
            }
            if ($this->cartHash($user) !== $attempt->cart_hash) {
                return response()->json(['status' => 'paid_pending_order',
                    'message' => 'Pagamento ricevuto, ma il carrello è cambiato. Contatta assistenza.']);
            }
            try {
                $this->validateShipping($user, (int) $attempt->shipping_address_id);
            } catch (Throwable $e) {
                return response()->json(['status' => 'paid_pending_order',
                    'message' => 'Pagamento ricevuto, indirizzo da verificare.']);
            }
            try {
                Auth::setUser($user);
                if ($attempt->tipo_richiesta === 'PP') {
                    $card = PaymentMethod::withTrashed()
                        ->where('user_id', $user->id)
                        ->where('stripe_payment_method_id', $attempt->num_contratto)->first();
                    if (!$card) {
                        PaymentMethod::create([
                            'user_id' => $user->id,
                            'stripe_payment_method_id' => $attempt->num_contratto,
                            'holder_name' => trim($user->name . ' ' . $user->surname),
                            'default' => 0, 'is_default' => 0,
                        ]);
                    } elseif (method_exists($card, 'trashed') && $card->trashed()) {
                        $card->restore();
                    }
                }
                $number = $this->createOrder($user, $attempt->amount_cents / 100,
                    'stripe', $attempt->cod_trans, $attempt->order_number,
                    $attempt->num_contratto);
                $this->updateAttempt($attempt, ['status' => 'completed']);
                return response()->json(['status' => 'completed', 'order_number' => $number]);
            } catch (Throwable $e) {
                Log::critical('Nexi paid, order pending', [
                    'attempt_id' => $attempt->id, 'error' => $e->getMessage(),
                ]);
                if ($order = $this->existingOrder($attempt)) {
                    $this->updateAttempt($attempt, ['status' => 'completed']);
                    return response()->json(['status' => 'completed', 'order_number' => $order->order_number]);
                }
                return response()->json(['status' => 'paid_pending_order']);
            }
        } finally {
            $lock->release();
        }
    }
}
