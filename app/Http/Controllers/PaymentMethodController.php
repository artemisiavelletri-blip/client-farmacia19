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
    public function store(Request $request)
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

    public function destroy(Request $request, $pm)
    {
        $user = auth()->user();

        $paymentMethod = PaymentMethod::where(
            'stripe_payment_method_id',
            $pm
        )->firstOrFail();

        if ($paymentMethod->user_id !== $user->id) {
            abort(403);
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $stripePm = \Stripe\PaymentMethod::retrieve(
                $paymentMethod->stripe_payment_method_id
            );

            // 1️⃣ Se è default, tolgo PRIMA il default su Stripe
            if ($paymentMethod->is_default) {

                $newDefault = $user->paymentMethods()
                    ->where('id', '!=', $paymentMethod->id)
                    ->first();

                if ($newDefault) {
                    // Caso A: esiste un altro metodo
                    $newDefault->update(['is_default' => true]);

                    \Stripe\Customer::update(
                        $user->stripe_customer_id,
                        [
                            'invoice_settings' => [
                                'default_payment_method' =>
                                    $newDefault->stripe_payment_method_id
                            ]
                        ]
                    );
                } else {
                    // Caso B: è l’unico metodo
                    \Stripe\Customer::update(
                        $user->stripe_customer_id,
                        [
                            'invoice_settings' => [
                                'default_payment_method' => null
                            ]
                        ]
                    );
                }
            }

            $stripePm->detach();

            $paymentMethod->delete();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
