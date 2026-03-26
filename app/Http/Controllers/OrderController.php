<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Validator;
use App\Services\Track123Service;
use Illuminate\Support\Facades\Mail;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Carbon\Carbon;
use Cmixin\BusinessDay;

use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\ReasonReturn;
use App\Mail\RefundMail;
use App\Mail\OrderCancelledMail;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id',auth()->user()->id)->orderBy('created_at','desc')->paginate(10);
        return view('auth.order-list', [
            'orders' => $orders
        ]);
    }

    public function orderDetail(Request $request,$id)
    {
        $order = Order::with('city')->where('order_number',$id)->firstOrFail();

        $courierCode = 'poste-italiane';
        $trackingNumber = $order->tracking_number;

        // Chiama la funzione dal servizio
        $service = new Track123Service();

        $trackingData = $service->track($order->tracking_number, 'poste-italiane');

        $cancelled = false;
        if ($order->created_at >= now()->subHours(24) && !$order->tracking_number && $order->status != 'cancelled') {
            $cancelled = true;
        }       

        // Abilitiamo la gestione dei giorni lavorativi per l'Italia
        BusinessDay::enable(Carbon::class, 'it-national');

        // Data dell'ordine
        $ordine = Carbon::parse($order->created_at);

        // Calcolo il limite minimo (3 giorni lavorativi dopo l'ordine)
        $inizioFinestra = $ordine->copy()->addBusinessDays(3);

        // Calcolo il limite massimo (15 giorni naturali dall'inizio della finestra)
        $fineFinestra = $inizioFinestra->copy()->addDays(15);

        $refund = false;
        $oggi = Carbon::now();
        // Controllo se la data calcolata rientra nella finestra
        if ($oggi->between($inizioFinestra, $fineFinestra) && $order->status != 'cancelled' && $order->returns->isEmpty()) {
            $refund = true;
        }

        return view('auth.order-detail', [
            'order' => $order,
            'trackingData' => $trackingData['tracking'],
            'status' => $trackingData['status'],
            'cancelled' => $cancelled,
            'refund' => $refund
        ]);
    }

    public function orderDelete(Request $request, $id)
    {
        $order = Order::where('order_number',$id)->where('user_id',auth()->user()->id)->firstOrFail();
        $method = $order->payment_method;
        if ($order->created_at >= now()->subHours(24) && !$order->tracking_number && $order->status != 'cancelled') {

            if($order->payment_method == 'stripe'){
                Stripe::setApiKey(config('services.stripe.secret'));

                try {
                    $refund = \Stripe\Refund::create([
                        'payment_intent' => $order->transaction_id,
                    ]);

                    $order->update([
                        'payment_status' => 'refunded'
                    ]);                    

                } catch (\Exception $e) {
                    return back()->with('error', $e->getMessage());
                }
            }

            try{
                foreach ($order->items as $item) {
                    $product = $item->product()->first();
                    $product->stock += $item->quantity;
                    $product->save();
                }

                $order->status = 'cancelled';
                $order->save();

                switch ($method) {
                    case 'stripe':
                        $payment_method_telegram = 'Carta';
                        break;
                    case 'paypal':
                        $payment_method_telegram = 'PayPal';
                        break;
                    case 'bank_transfer':
                        $payment_method_telegram = 'Bonifico';
                        break;
                    case 'cod':
                        $payment_method_telegram = 'Contrassegno';
                        break;
                }

                $chatId = '-5268274429';
                $text = "❌ <b>Ordine Annullato.</b>\n<b>Ordine Numero:</b> " . $order->order_number .  "\n<b>Medoto Pagamento:</b> " . $payment_method_telegram;

                $response = Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML'
                ]);

                Mail::to(auth()->user()->email)->send(new OrderCancelledMail($order));

                return redirect()
                    ->back()
                    ->with('success', 'Ordine annullato con successo.');
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        return redirect()
            ->back()
            ->with('error', "Non è possibile annullare l'ordine in quanto sono passate 24 ore.");
    }

    public function refundRequest(Request $request,$id)
    {
        $order = Order::where('order_number',$id)->where('user_id',auth()->user()->id)->firstOrFail();

        $reason_return = ReasonReturn::get();

        return view('auth.refund-request', [
            'order' => $order,
            'reason_return' => $reason_return
        ]);
    }

    public function refundRequestPost(Request $request,$id)
    {
        // Validazione
        $validator = Validator::make($request->all(), [
            'option' => ['required','in:1,2,3,4,5,6,7,8,9,10'],
            'images' => [
                'required_if:option,1,2,8,10',
                'array',
                'min:2',
                'max:3'
            ],

            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048'
            ],
            'message' => 'required',
            'products' => 'required|array|min:1'
        ], [
            'option.required' => 'Il campo motivo è obbligatorio',
            'message.required' => 'Il campo commento è obbligatorio',
            'images.required_if' => 'Le immagini sono obbligatorie',
            'images.max' => 'Devi caricare massimo 3 immagini',
            'images.min' => 'Devi caricare minimo 2 immagini',
            'products.required' => 'Selezionare almeno un prodotto per cui richiedere il reso',
            'products.min' => 'Selezionare almeno un prodotto per cui richiedere il reso',
            'products.array' => 'Selezionare almeno un prodotto per cui richiedere il reso'
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $order = Order::where('order_number',$id)->firstOrFail();

        $token = strtoupper(Str::random(10));

        $return = new OrderReturn();
        $return->order_id = $order->id;
        $return->reason_id = $request->option;
        $return->message = $request->message;

        do {
            $token = strtoupper(Str::random(10));
        } while (OrderReturn::where('token', $token)->exists());
        $return->token = $token;

        $paths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $paths[] = $image->store('returns', 'local'); 
            }
        }

        $return->image1 = $paths[0] ?? null;
        $return->image2 = $paths[1] ?? null;
        $return->image3 = $paths[2] ?? null;

        $return->status = 0;

        $return->save();

        foreach ($request->products as $productId) {
            $return->items()->create([
                'product_id' => $productId,
            ]);
        }

        switch ($order->payment_method) {
            case 'stripe':
                $payment_method_telegram = 'Carta';
                break;
            case 'paypal':
                $payment_method_telegram = 'PayPal';
                break;
            case 'bank_transfer':
                $payment_method_telegram = 'Bonifico';
                break;
            case 'cash_on_delivery':
                $payment_method_telegram = 'Contrassegno';
                break;
        }

        Mail::to(auth()->user()->email)->send(new RefundMail($order->order_number,'Richiesta di rimborso – Ordine #' . $order->order_number));

        $chatId = '-5268274429';
        $text = "⚠️ <b>Richiesta Reso.</b>\n<b>Reso Numero:</b> " . $return->token .  "\n<b>Medoto Pagamento:</b> " . $payment_method_telegram;

        $response = Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);

        return redirect()
            ->route('orderDetail', $id)
            ->with('success', 'Richiesta di reso inviata con successo.');

    }
}
