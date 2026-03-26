<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Telegram\Bot\Laravel\Facades\Telegram;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

use App\Models\Order;
use App\Models\OrderItem;

use App\Mail\OrderMail;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'payment_method' => 'required|in:stripe,paypal,bank_transfer,cod',
            'selected_card_id' => 'nullable|exists:payment_methods,id',
            'new_card_token' => 'nullable|string',
        ],
        [
            'payment_method.required' => 'Devi selezionare un metodo di pagamento.',
            'payment_method.in' => 'Il metodo di pagamento scelto non è valido.',
            'selected_card_id.exists' => 'La carta selezionata non esiste.'
        ]);

        $paymentMethod = $request->payment_method;
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Il carrello è vuoto.');
        }

        // Calcola totale
        $total = $cartItems->sum(function($item){
            return $item->subtotal;
        });

        switch ($paymentMethod) {
            case 'stripe':
                return $this->processStripe($user, $total, $request);
            case 'paypal':
                return $this->processPaypal($user, $total);
            case 'bank_transfer':
                return $this->processBankTransfer($user, $total);
            case 'cod':
                return $this->processCOD($user, $total);
        }
    }

    protected function processStripe($user, $total, $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        if ($request->selected_card_id) {
            // Usa carta salvata
            $card = $user->paymentMethods()->findOrFail($request->selected_card_id);
            $paymentMethodId = $card->stripe_payment_method_id;
        } elseif ($request->new_card_token) {
            // Usa nuova carta
            $paymentMethodId = $request->new_card_token;
        } else {
            return back()->with('error', 'Nessuna carta selezionata.');
        }

        // Crea PaymentIntent
        try {
            $intent = PaymentIntent::create([
                'amount' => intval($total * 100), // in centesimi
                'currency' => 'eur',
                'customer' => $user->stripe_customer_id,
                'payment_method' => $paymentMethodId,
                'off_session' => true,
                'confirm' => true,
            ]);

            // Segna ordine come pagato
            $order_number = $this->createOrder($user, $total, 'stripe', $intent->id);

            return redirect()->route('cart.shop_checkout_complete', ['order_number' => $order_number,'success' => true,'payment_method' => 'stripe']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    protected function processPaypal($user, $total)
    {
        $provider = new \Srmklive\PayPal\Services\PayPal;
        $provider->setApiCredentials(config('paypal'));
        //dd($provider);
        $provider->getAccessToken();

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "EUR",
                        "value" => number_format($total, 2, '.', '')
                    ]
                ]
            ],
            "application_context" => [
                "cancel_url" => route('paypal.cancel'),
                "return_url" => route('paypal.success')
            ]
        ]);

        if (!isset($response['links'])) {
            return back()->with('error', 'Errore PayPal: ' . json_encode($response));
        }

        foreach ($response['links'] as $link) {
            if ($link['rel'] === 'approve') {
                return redirect()->away($link['href']);
            }
        }

        return back()->with('error', 'Link PayPal non trovato');
    }

    public function success(Request $request, $orderId)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $response = $provider->capturePaymentOrder($request->token);

        if(isset($response['status']) && $response['status'] == 'COMPLETED'){

            $order = Order::findOrFail($orderId);

            $order->payment_status = 'paid';
            $order->paypal_capture_id = $response['purchase_units'][0]['payments']['captures'][0]['id'];
            $order->save();

            return redirect()->route('order.success');
        }

        return redirect()->route('order.failed');
    }

    protected function processBankTransfer($user, $total)
    {
        // Segna ordine come in attesa di pagamento
        $order_number = $this->createOrder($user, $total, 'bank_transfer');
        return redirect()->route('cart.shop_checkout_complete', ['order_number' => $order_number,'success' => true,'payment_method' => 'bank_transfer']);
    }

    protected function processCOD($user, $total)
    {
        // Segna ordine come contrassegno
        $order_number = $this->createOrder($user, $total, 'cod');
        return redirect()->route('cart.shop_checkout_complete', ['order_number' => $order_number,'success' => true,'payment_method' => 'cash_on_delivery']);
    }

    protected function createOrder($user, $total, $method, $transactionId = null)
    {
        $prescriberId = session('prescriber_id') ?? $user->prescriber_id ?? null;

        $shipping_cost = 0.00;
        if($method == 'cod'){
            $total = $user->cartItems()->with('product')->get()->sum->subtotal + 2.00;
        }
        if(number_format((float)$user->cartItems()->with('product')->get()->sum->subtotal, 2, '.', '') <= 49.90){
            $shipping_cost = 5.90;
            $total += $shipping_cost;
        }

        switch ($method) {
            case 'stripe':
                $payment_method = 'stripe';
                $payment_status = 'paid';
                break;
            case 'paypal':
                $payment_method = 'paypal';
                $payment_status = 'paid';
                break;
            case 'bank_transfer':
                $payment_method = 'bank_transfer';
                $payment_status = 'pending';
                break;
            case 'cod':
                $payment_method = 'cash_on_delivery';
                $payment_status = 'pending';
                break;
        }


        // Crea l'ordine
        $order = Order::create([
            'user_id' => $user->id,
            'prescriber_id' => $prescriberId,
            'order_number' => strtoupper(Str::random(10)),
            'subtotal' => $user->cartItems()->with('product')->get()->sum->subtotalnoiva,
            'total_vat' => $user->cartItems()->with('product')->get()->sum->totalvat,
            'shipping_cost' => $shipping_cost,
            'total' => $total,
            'payment_method' => $payment_method,
            'payment_status' => $payment_status,
            'transaction_id' => $transactionId, 
            'status' => 'pending',
            'address' => $user->shippingAddresses()->first()->address,
            'cap' => $user->shippingAddresses()->first()->cap,
            'city_id' => $user->shippingAddresses()->first()->city_id,
            'phone' => $user->shippingAddresses()->first()->phone,
            'recipient_name' => $user->shippingAddresses()->first()->recipient_name,
            'note' => $user->shippingAddresses()->first()->note
        ]);

        // Aggiungi items
        foreach($user->cartItems as $item) {
            $product = $item->product()->first();
            $price = $product->price;
            if($product->discountPrice){
                $price = $product->discountPrice;
            }
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $price,
                'vat_percentage' => $item->product()->first()->iva()->first()->percentage,
                'quantity' => $item->quantity,
                'subtotal' => $item->subtotalnoiva,
                'vat_amount' => $item->totalvat,
            ]);
        }

        $user->cartItems()->delete();
        session()->forget('prescriber_id');

        $user = auth()->user();
        $user->prescriber_id = null;
        $user->save();

        switch ($method) {
            case 'stripe':
                $payment_method_telegram = 'Carta';
                $payment_status_telegram = 'Pagato';
                break;
            case 'paypal':
                $payment_method_telegram = 'PayPal';
                $payment_status_telegram = 'Pagato';
                break;
            case 'bank_transfer':
                $payment_method_telegram = 'Bonifico';
                $payment_status_telegram = 'In Sospeso';
                break;
            case 'cod':
                $payment_method_telegram = 'Contrassegno';
                $payment_status_telegram = 'In Sospeso';
                break;
        }

        $chatId = '-5268274429';
        $text = "✅ <b>Complimenti! Hai venduto un prodotto.</b>\n<b>Ordine Numero:</b> " . $order->order_number .  "\n<b>Totale:</b> €" . number_format((float)$order->total, 2, '.', '') .  "\n<b>Medoto Pagamento:</b> " . $payment_method_telegram . "\n<b>Stato Pagamento:</b> " . $payment_status_telegram;

        $response = Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);

        $orderItems = $order->items;
        Mail::to($user->email)->send(new OrderMail($order, $orderItems));

        return $order->order_number;
    }
}
