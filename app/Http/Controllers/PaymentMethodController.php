<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentMethod as StripePaymentMethod;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends Controller
{
    public function store_stripe(Request $request)
    {

        $request->validate([
            'payment_method_id' => 'required|string',
            'holderName' => 'required|string'
        ]);

        $user = auth()->user();
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            /** 1️⃣ CREA CUSTOMER STRIPE SE NON ESISTE */
            if (!$user->stripe_customer_id) {
                $customer = Customer::create([
                    'email' => $user->email,
                    'name'  => $user->name,
                ]);

                $user->stripe_customer_id = $customer->id;
                $user->save();
            }

            /** 2️⃣ RECUPERA E ATTACCA PAYMENT METHOD */
            $stripePm = StripePaymentMethod::retrieve($request->payment_method_id);

            $stripePm->attach([
                'customer' => $user->stripe_customer_id
            ]);

            /** 3️⃣ SE È IL PRIMO → DEFAULT */
            $isDefault = !$user->paymentMethods()->exists();

            if ($isDefault) {
                Customer::update($user->stripe_customer_id, [
                    'invoice_settings' => [
                        'default_payment_method' => $stripePm->id
                    ]
                ]);
            }

            /** 4️⃣ SALVA NEL DB */
            PaymentMethod::create([
                'holder_name' => $request->holderName,
                'user_id' => $user->id,
                'stripe_payment_method_id' => $stripePm->id,
                'brand' => $stripePm->card->brand ?? null,
                'last4' => $stripePm->card->last4 ?? null,
                'exp_month' => $stripePm->card->exp_month ?? null,
                'exp_year' => $stripePm->card->exp_year ?? null,
                'is_default' => $isDefault,
            ]);

            return response()->json([
                'success' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            abort(401);
        }

        $codTrans = $request->input('codTrans');

        $payment = Cache::get('nexi_payment_' . $codTrans);

        if (
            !$payment ||
            ($payment['tipo_richiesta'] ?? null) !== 'PP' ||
            (int) ($payment['user_id'] ?? 0) !== (int) $user->id ||
            !hash_equals(
                (string) ($payment['num_contratto'] ?? ''),
                (string) $request->input('num_contratto', '')
            )
        ) {
            return redirect()
                ->route('settingspayment_method')
                ->with('error', 'Registrazione carta non valida o scaduta.');
        }

        $codiceEsito = (int) $request->input('codiceEsito', -1);

        if ($codiceEsito !== 0) {
            $erroriUtente = [
                116 => 'Operazione annullata.',
                121 => 'Sessione scaduta. Riprova.',
                400 => 'Operazione non autorizzata.',
                401 => 'Carta scaduta o data non corretta.',
                402 => 'Carta non valida.',
                404 => 'Operazione rifiutata dalla banca.',
                405 => 'Fondi insufficienti.',
                407 => 'Impossibile contattare la banca.',
                408 => 'È richiesta una nuova autenticazione.',
                409 => 'Operazione rifiutata per sicurezza.',
                410 => 'Troppi tentativi non riusciti.',
                411 => 'Contatta la tua banca.',
                412 => 'Carta bloccata o non utilizzabile.',
                414 => 'Limite di spesa raggiunto.',
            ];

            return redirect()
                ->route('settingspayment_method')
                ->with(
                    'error',
                    $erroriUtente[$codiceEsito]
                        ?? 'Impossibile registrare la carta.'
                );
        }

        try {
            DB::transaction(function () use ($request, $user, $payment) {

                $numContratto = $payment['num_contratto'];

                // Impedisce registrazioni duplicate.
                $existing = PaymentMethod::withTrashed()
                    ->where('user_id', $user->id)
                    ->where('stripe_payment_method_id', $numContratto)
                    ->first();

                if ($existing) {
                    return;
                }

                $scadenza = (string) $request->input('scadenza_pan', '');

                if (!preg_match('/^\d{6}$/', $scadenza)) {
                    throw new \RuntimeException(
                        'Scadenza carta non valida.'
                    );
                }

                $anno = substr($scadenza, 0, 4);
                $mese = substr($scadenza, 4, 2);

                if ((int) $mese < 1 || (int) $mese > 12) {
                    throw new \RuntimeException(
                        'Mese di scadenza non valido.'
                    );
                }

                PaymentMethod::where('user_id', $user->id)
                    ->update([
                        'default' => 0,
                        'is_default' => 0,
                    ]);

                PaymentMethod::create([
                    'holder_name' => trim(
                        $request->input('nome', '') . ' ' .
                        $request->input('cognome', '')
                    ),
                    'user_id' => $user->id,
                    'stripe_payment_method_id' => $numContratto,
                    'brand' => $request->input('brand'),
                    'last4' => substr(
                        (string) $request->input('pan', ''),
                        -4
                    ),
                    'exp_month' => $mese,
                    'exp_year' => $anno,
                    'is_default' => 1,
                    'default' => 1,
                ]);
            });

            Cache::forget('nexi_payment_' . $codTrans);

            return redirect()
                ->route('settingspayment_method')
                ->with('success', 'Carta registrata correttamente.');

        } catch (\Throwable $e) {

            Log::error('Errore registrazione carta Nexi', [
                'user_id' => $user->id,
                'codTrans' => $codTrans,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('settingspayment_method')
                ->with('error', 'Impossibile registrare la carta.');
        }
    }

    
    public function destroy(Request $request, $pm)
    {
        $user = auth()->user();

        $paymentMethod = PaymentMethod::where(
            'stripe_payment_method_id',
            $pm
        )
        ->where('user_id', $user->id)
        ->firstOrFail();

        try {

            $eraPredefinita = (bool) $paymentMethod->default;

            // Nasconde la carta senza eliminarla fisicamente.
            $paymentMethod->default = 0;
            $paymentMethod->is_default = 0;
            $paymentMethod->save();

            $paymentMethod->delete();

            // Se era la carta predefinita,
            // imposta come predefinita l'ultima carta attiva.
            if ($eraPredefinita) {

                $previous = PaymentMethod::where(
                    'user_id',
                    $user->id
                )
                ->orderByDesc('id')
                ->first();

                if ($previous) {
                    PaymentMethod::where('user_id', $user->id)
                        ->update([
                            'default' => 0,
                            'is_default' => 0,
                        ]);

                    $previous->default = 1;
                    $previous->is_default = 1;
                    $previous->save();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Metodo di pagamento rimosso.'
            ]);

        } catch (\Exception $e) {

            Log::error('Errore rimozione carta', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Impossibile rimuovere il metodo di pagamento.'
            ], 500);
        }
    }


}
