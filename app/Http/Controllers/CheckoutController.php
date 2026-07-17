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
use App\Models\Address;
use App\Models\OrderItem;
use App\Models\PaymentMethod;

use App\Mail\OrderMail;

use App\Services\Google\GmailService;

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
        $total = auth()->user()->cart_total;


        Address::where('user_id', Auth::id())
            ->where('type', 'shipping')
            ->update(['default' => null]);

        Address::where('user_id', Auth::id())
            ->where('type', 'shipping')
            ->whereKey($request->shipping_address)
            ->update(['default' => 1]);

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
        /*Stripe::setApiKey(config('services.stripe.secret'));

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
        }*/

        $paymentMethod = PaymentMethod::where(
            'id',
            $request['selected_card_id']
        )->firstOrFail();

        if ($paymentMethod->user_id !== $user->id) {
            abort(403);
        }

        PaymentMethod::where('user_id',$user->id)->update(['default' => null]);
        $paymentMethod->default = 1;
        $paymentMethod->save();

        $importo = intval($total * 100);

        // Pagamento OneClik - Pagamenti successivi - Tramite redirezione - Avvio pagamento

        $requestUrl = env('NEXI_BASE_URL');
        $merchantServerUrl = "http://" . $_SERVER['HTTP_HOST'] . "/cart/pagamento";
        $merchantServerUrlBack = "http://" . $_SERVER['HTTP_HOST'] . "/shop-checkout";

        //PARAMETRI PER CALCOLO MAC
        $codTrans = "PS" . date('YmdHis');
        $chiaveSegreta = env('XPAY_SECRET');
        $divisa = "EUR"; /* <-- EUR oppure 978 */

        //CALCOLO MAC
        $mac = sha1('codTrans=' . $codTrans . 'divisa=' . $divisa . 'importo=' . $importo . $chiaveSegreta);

        //Param Obbligatori
        $params = array(
            'importo' => $importo,
            'alias' => env('XPAY_ALIAS'),
            'divisa' => $divisa,
            'codTrans' => $codTrans,
            'mac' => $mac,
            'url' => $merchantServerUrl, //necessita HTTP:// oppure HTTPS://
            'url_back' => $merchantServerUrlBack, //necessita HTTP:// oppure HTTPS://
            'num_contratto' => $paymentMethod->stripe_payment_method_id,
            'tipo_servizio' => 'paga_oc3d',
            'tipo_richiesta' => 'PR', /* <-- PR = Pagamento Ricorrente */
        );

        return view('cart.pagamento', compact('requestUrl', 'params'));

        $connection = curl_init();

        if ($connection) {

            $requestURL = "https://int-ecommerce.nexi.it/"; // URL
            $requestURI = "ecomm/api/recurring/creaNonceRico3DS"; // URI

            $apiKey = env('XPAY_ALIAS'); // Sostituire con il valore fornito da Nexi
            $chiaveSegreta = env('XPAY_SECRET'); // Sostituire con il valore fornito da Nexi


            $timeStamp = (time()) * 1000;
            $numeroContratto = $paymentMethod->stripe_payment_method_id;
            $codiceTransazione = "XPAY" . time();
            $importo = intval($total * 100);
            $divisa = 978;
            $urlRisposta = "https://" . $_SERVER['HTTP_HOST'] . "/pagamento.php";
            $scadenza = date('Y') . '12';
            
            // Calcolo MAC
            $mac = sha1('apiKey=' . $apiKey
                    . 'numeroContratto=' . $numeroContratto
                    . 'codiceTransazione=' . $codiceTransazione
                    . 'importo=' . $importo
                    . 'divisa=' . $divisa
                    . 'codiceGruppo=' . ''
                    . 'timeStamp=' . $timeStamp
                    . $chiaveSegreta);
            //dd($mac);

            // Parametri
            $parametri = array(
                'apiKey' => $apiKey,
                'numeroContratto' => $numeroContratto,
                'codiceTransazione' => $codiceTransazione,
                'importo' => $importo,
                'divisa' => $divisa,
                'urlRisposta' => $urlRisposta,
                'timeStamp' => $timeStamp,
                'mac' => $mac,
            );

            curl_setopt_array($connection, array(
                CURLOPT_URL => $requestURL . $requestURI,
                CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => json_encode($parametri),
                CURLOPT_RETURNTRANSFER => 1,
                CURLINFO_HEADER_OUT => true,
                CURLOPT_SSL_VERIFYPEER => 0
            ));

            $risposta = curl_exec($connection);

            curl_close($connection);

            // Decodifico risposta
            $json = json_decode($risposta, true);
            //dd($risposta);

            // Controllo JSON di risposta
            if (json_last_error() === JSON_ERROR_NONE) {

                $MACrisposta = sha1('esito=' . $json['esito'] . 'idOperazione=' . $json['idOperazione'] . 'timeStamp=' . $json['timeStamp'] . $chiaveSegreta);

                // Controllo MAC di risposta
                if ($json['mac'] == $MACrisposta) {

                    // Controllo esito
                    if ($json['esito'] == 'OK') {
                        //$order_number = $this->createOrder($user, $total, 'stripe', $codiceTransazione);
                        //return redirect()->route('cart.shop_checkout_complete', ['order_number' => $order_number,'success' => true,'payment_method' => 'stripe']);
                        echo $json['html'];
                    } else {
                        return back()->with('error', 'Si è verificato un errore nel pagamento! Si prega di riprovare o cambiare metodo di pagamento');
                    }
                } else {
                    return back()->with('error', 'Si è verificato un errore nel pagamento! Si prega di riprovare o cambiare metodo di pagamento');
                }
            } else {
                return back()->with('error', 'Si è verificato un errore nel pagamento! Si prega di riprovare o cambiare metodo di pagamento');
            }
        } else {
            return back()->with('error', 'Si è verificato un errore nel pagamento! Si prega di riprovare o cambiare metodo di pagamento');
        }
    }

    public function pagamento(Request $request)
    {
        $total = (float)$request['importo']/100;
        $user = auth()->user();

        $erroriUtente = [
            116 => 'Pagamento annullato.',
            121 => 'La sessione di pagamento è scaduta. Riprova.',

            400 => 'Pagamento non autorizzato. Verifica i dati della carta o contatta la tua banca.',
            401 => 'La carta risulta scaduta o la data di scadenza non è corretta.',
            402 => 'La carta non è valida. Utilizza un altro metodo di pagamento.',
            404 => 'La banca ha rifiutato il pagamento. Utilizza un altro metodo di pagamento.',
            405 => 'Fondi insufficienti sulla carta.',
            407 => 'Non è stato possibile contattare la banca. Riprova tra qualche minuto.',
            408 => 'La banca richiede una nuova autenticazione. Riprova il pagamento.',
            409 => 'Il pagamento è stato rifiutato per motivi di sicurezza. Contatta la tua banca.',
            410 => 'Troppi tentativi di autenticazione non riusciti. Riprova più tardi.',
            411 => 'Contatta la tua banca per autorizzare il pagamento.',
            412 => 'La carta risulta bloccata, smarrita o non utilizzabile.',
            414 => 'È stato raggiunto il limite di spesa della carta.',
        ];

        $codiceEsito = (int) request('codiceEsito');       


        if(!$codiceEsito){
            try {

                // Segna ordine come pagato
                $order_number = $this->createOrder($user, $total, 'stripe', $request['codTrans'],$request['num_contratto']);

                return redirect()->route('cart.shop_checkout_complete', ['order_number' => $order_number,'success' => true,'payment_method' => 'stripe']);
            } catch (\Exception $e) {
                dd($e);
                return back()->with('error', $e->getMessage());
            }
        } else {
            $messaggio = $erroriUtente[$codiceEsito] 
                            ?? 'Si è verificato un problema durante il pagamento. Riprova più tardi.';
            return redirect()
                ->route('cart.shop_checkout')
                ->with('error', $messaggio);
        }

    }

    protected function processPaypal($user, $total)
    {
        $provider = new PayPalClient;
       $provider->setApiCredentials(config('paypal'));
       $paypalToken = $provider->getAccessToken();

       $response = $provider->createOrder([
           "intent" => "CAPTURE",
           "purchase_units" => [
               [
                   "amount" => [
                       "currency_code" => "USD",
                       "value" => "100.00"
                   ]
               ]
           ],
           "application_context" => [
               "cancel_url" => route('paypal.cancel'),
               "return_url" => route('paypal.success'),
           ]
       ]);

       if (isset($response['id']) && $response['id'] != null) {
           foreach ($response['links'] as $link) {
               if ($link['rel'] === 'approve') {
                   return redirect()->away($link['href']);
               }
           }
       }


       return redirect()->route('paypal.cancel');
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
        dd($order_number);
        return redirect()->route('cart.shop_checkout_complete', ['order_number' => $order_number,'success' => true,'payment_method' => 'bank_transfer']);
    }

    protected function processCOD($user, $total)
    {
        // Segna ordine come contrassegno
        $order_number = $this->createOrder($user, $total, 'cod');
        return redirect()->route('cart.shop_checkout_complete', ['order_number' => $order_number,'success' => true,'payment_method' => 'cash_on_delivery']);
    }

    protected function createOrder($user, $total, $method, $transactionId = null, $paymentGateway = null)
    {
        $prescriberId = session('prescriber_id') ?? $user->prescriber_id ?? null;

        $shipping_cost = auth()->user()->cart_shipping_cost;
        if($method == 'cod'){
            $total = auth()->user()->cart_total + 2.00;
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

        $cartItems = $user->cartItems()->with('discounts')->get();

        $coupon = $cartItems
            ->pluck('discounts')
            ->flatten()
            ->first();

        $couponDiscount = (float)$user->cart_discount;

        if ($couponDiscount <= 0) {
            $coupon = null;
            $couponDiscount = null;
        } else {
            $coupon = $coupon->id;
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
            'payment_gateway' => $paymentGateway,
            'payment_status' => $payment_status,
            'transaction_id' => $transactionId, 
            'status' => 'pending',
            'address' => $user->shippingAddresses()->first()->address,
            'cap' => $user->shippingAddresses()->first()->cap,
            'city_id' => $user->shippingAddresses()->first()->city_id,
            'phone' => $user->shippingAddresses()->first()->phone,
            'recipient_name' => $user->shippingAddresses()->first()->recipient_name,
            'note' => $user->shippingAddresses()->first()->note,
            'coupon_id' => $coupon,
            'couponDiscount' => $couponDiscount,
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

        $gmail = app(GmailService::class);

        $gmail->sendEmail(
            $user->email,
            'Ordine #' . $order->order_number,
            'emails.order', // 👈 blade
            [
                'order' => $order,
                'orderItems' => $orderItems
            ]
        );

        return $order->order_number;
    }
}
