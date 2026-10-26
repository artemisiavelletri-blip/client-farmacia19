<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\CheckoutController;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * API checkout per i soli metodi offline.
 * Riusa i metodi esistenti del CheckoutController senza cambiare createOrder().
 * Nexi e PayPal richiedono flussi separati con verifica callback server-side.
 */
class Farmacia19CheckoutApiController extends CheckoutController
{
    public function confirm(Request $request)
    {
        $input = $request->validate([
            'shipping_address' => ['required', 'integer'],
            'payment_method' => ['required', 'in:bank_transfer,cod'],
            'request_id' => ['required', 'uuid'],
        ]);

        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Accesso richiesto.'], 401);

        // Un doppio tap non deve creare due ordini. Mantieni il lock anche
        // durante l'invio delle notifiche da createOrder().
        $lock = Cache::lock('farmacia19:checkout:user:' . $user->id, 120);
        if (!$lock->get()) {
            return response()->json(['message' => 'Un ordine è già in elaborazione. Riprova tra poco.'], 409);
        }

        try {
            $key = 'farmacia19:checkout:result:' . $user->id . ':' . $input['request_id'];
            if ($previous = Cache::get($key)) return response()->json($previous);

            $cart = $user->cartItems()->with('product')->get();
            if ($cart->isEmpty()) {
                return response()->json(['message' => 'Il carrello è vuoto.'], 422);
            }

            $chosen = Address::where('user_id', $user->id)
                ->where('type', 'shipping')->find($input['shipping_address']);
            if (!$chosen) throw ValidationException::withMessages([
                'shipping_address' => ['Indirizzo di spedizione non valido.'],
            ]);

            // IMPORTANTE: createOrder() usa shippingAddresses()->first().
            // Finché quel metodo deve restare invariato, impediamo che un
            // ordine sia registrato con un indirizzo diverso dal prescelto.
            $actual = $user->shippingAddresses()->first();
            if (!$actual || (int) $actual->id !== (int) $chosen->id) {
                throw ValidationException::withMessages([
                    'shipping_address' => [
                        'Per questo indirizzo è necessario completare l’ordine dal sito. '
                        . 'L’API non può ancora garantirne il corretto salvataggio.'
                    ],
                ]);
            }

            // Nessun totale arriva dal client.
            $total = (float) $user->cart_total;
            if ($total <= 0) throw ValidationException::withMessages([
                'cart' => ['Totale del carrello non valido.'],
            ]);

            // Riutilizza il processo Laravel originale: notifiche, coupon,
            // stato pagamento, cancellazione del carrello.
            $number = $input['payment_method'] === 'cod'
                ? $this->createOrder($user, $total, 'cod')
                : $this->createOrder($user, $total, 'bank_transfer');

            $result = [
                'success' => true,
                'order_number' => $number,
                'payment_method' => $input['payment_method'],
                'payment_status' => 'pending',
                'total' => round($total + ($input['payment_method'] === 'cod' ? 2.00 : 0.00), 2),
            ];
            Cache::put($key, $result, now()->addDay());
            return response()->json($result, 201);
        } finally {
            $lock->release();
        }
    }
}
