<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentMethod as StripePaymentMethod;
use App\Models\PaymentMethod;

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
        //dd($request->all());

        $user = auth()->user();

        try {

            $anno = substr($request->scadenza_pan, 0, 4);
            $mese = substr($request->scadenza_pan, 4, 2);

            PaymentMethod::where('user_id', $user->id)->update(['default' => null]);


            /** 4️⃣ SALVA NEL DB */
            PaymentMethod::create([
                'holder_name' => $request->nome . ' ' . $request->cognome,
                'user_id' => $user->id,
                'stripe_payment_method_id' => $request->num_contratto,
                'brand' => $request->brand ?? null,
                'last4' => substr($request->pan, -4) ?? null,
                'exp_month' => $mese ?? null,
                'exp_year' => $anno ?? null,
                'is_default' => 1,
                'default' => 1
            ]);

            return redirect()->route('settingspayment_method');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
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
