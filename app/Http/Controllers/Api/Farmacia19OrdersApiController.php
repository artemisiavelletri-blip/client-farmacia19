<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\ReasonReturn;
use App\Services\Track123Service;
use App\Services\Google\GmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Telegram\Bot\Laravel\Facades\Telegram;
use Carbon\Carbon;
use Cmixin\BusinessDay;

class Farmacia19OrdersApiController extends Controller
{
    private function mine(Request $request, string $number): Order
    {
        return Order::where('user_id', $request->user()->id)->where('order_number', $number)->firstOrFail();
    }

    private function cancellationAllowed(Order $order): bool
    {
        return $order->status !== 'cancelled' && !$order->tracking_number
            && $order->created_at >= now()->subHours(24);
    }

    private function refundAllowed(Order $order): bool
    {
        if ($order->status === 'cancelled' || $order->returns()->exists()) return false;
        BusinessDay::enable(Carbon::class, 'it-national');
        $start = Carbon::parse($order->created_at)->addBusinessDays(3);
        return now()->between($start, $start->copy()->addDays(15));
    }

    private function serialize(Order $order, bool $detail = false): array
    {
        $items = $order->items->map(function ($item) {
            $product = $item->product;
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'price' => (float) $item->price,
                'image_url' => asset('/storage-admin/' . ($product?->image ?: 'products/file-non-disponibile.jpg')),
            ];
        });
        $data = [
            'order_number' => $order->order_number,
            'created_at' => $order->created_at?->toIso8601String(),
            'total' => (float) $order->total,
            'status' => $order->status,
            'status_step' => (int) ($order->status_step ?? 0),
            'image_url' => $items->first()['image_url'] ?? asset('/storage-admin/products/file-non-disponibile.jpg'),
        ];
        if ($detail) {
            $data += [
                'items' => $items->values()->all(),
                'recipient_name' => $order->recipient_name,
                'address' => $order->address,
                'cap' => $order->cap,
                'city' => $order->city?->name,
                'province' => $order->city?->province,
                'region' => $order->city?->region,
                'phone' => $order->phone,
                'note' => $order->note,
                'subtotal' => (float) $order->subtotal,
                'shipping_cost' => (float) $order->shipping_cost,
                'total_vat' => (float) $order->total_vat,
                'coupon_discount' => (float) ($order->couponDiscount ?? 0),
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'tracking_number' => $order->tracking_number,
                'can_cancel' => $this->cancellationAllowed($order),
                'can_refund' => $this->refundAllowed($order),
                'return' => $order->returns->first() ? [
                    'status' => $order->returns->first()->status,
                    'token' => $order->returns->first()->token,
                    'refund_type' => $order->returns->first()->refund_type,
                    'refund_value' => $order->returns->first()->refund_value,
                    'tracking_refund' => $order->returns->first()->tracking_refund,
                ] : null,
            ];
        }
        return $data;
    }

    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.product'])->orderByDesc('created_at')->paginate(10);
        return response()->json([
            'data' => $orders->getCollection()->map(fn ($o) => $this->serialize($o))->values(),
            'current_page' => $orders->currentPage(), 'last_page' => $orders->lastPage(),
            'has_more' => $orders->hasMorePages(),
        ]);
    }

    public function show(Request $request, string $orderNumber)
    {
        $order = $this->mine($request, $orderNumber);
        $order->load(['items.product', 'city', 'returns']);
        return response()->json(['data' => $this->serialize($order, true)]);
    }

    public function tracking(Request $request, string $orderNumber)
    {
        $order = $this->mine($request, $orderNumber);
        if (!$order->tracking_number) return response()->json(['data' => ['tracking_number' => null, 'status' => null, 'events' => []]]);
        try {
            $data = Cache::remember('farmacia19_tracking_' . $order->id . '_' . sha1($order->tracking_number), now()->addMinutes(10),
                fn () => (new Track123Service())->track($order->tracking_number, 'poste-italiane'));
            return response()->json(['data' => [
                'tracking_number' => $order->tracking_number,
                'status' => $data['status'] ?? null,
                'events' => is_array($data['tracking'] ?? null) ? array_values($data['tracking']) : [],
            ]]);
        } catch (\Throwable $e) {
            Log::warning('Tracking app non disponibile', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Tracciamento temporaneamente non disponibile.'], 503);
        }
    }

    public function cancel(Request $request, string $orderNumber)
    {
        $order = $this->mine($request, $orderNumber);
        $lock = Cache::lock('farmacia19_cancel_' . $order->id, 30);
        if (!$lock->get()) return response()->json(['message' => 'Operazione già in corso.'], 409);
        try {
            $result = DB::transaction(function () use ($order, $request) {
                $current = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                if (!$this->cancellationAllowed($current)) return false;
                $current->load('items');
                foreach ($current->items as $item) {
                    $product = $item->product()->lockForUpdate()->first();
                    if ($product) { $product->stock += $item->quantity; $product->save(); }
                }
                $current->status = 'cancelled';
                $current->save();
                return true;
            });
            if (!$result) return response()->json(['message' => 'Non è più possibile annullare questo ordine.'], 409);
            try {
                $order->refresh();
                $method = ['stripe'=>'Carta','paypal'=>'PayPal','bank_transfer'=>'Bonifico','cod'=>'Contrassegno','cash_on_delivery'=>'Contrassegno'][$order->payment_method] ?? $order->payment_method;
                Telegram::sendMessage(['chat_id' => config('services.telegram.orders_chat_id', '-5268274429'),
                    'text' => '❌ <b>Ordine Annullato.</b>' . "\n<b>Ordine Numero:</b> " . e($order->order_number) . "\n<b>Metodo Pagamento:</b> " . e($method), 'parse_mode'=>'HTML']);
                app(GmailService::class)->sendEmail($request->user()->email, 'Ordine #' . $order->order_number . ' - Cancellato', 'emails.orderCancelled', ['order'=>$order]);
            } catch (\Throwable $e) {
                Log::error('Notifica annullamento app fallita', ['order_id'=>$order->id, 'error'=>$e->getMessage()]);
            }
            return response()->json(['message' => 'Ordine annullato con successo.']);
        } finally { $lock->release(); }
    }

    public function reasons(Request $request)
    {
        return response()->json(['data' => ReasonReturn::all()->map(fn ($r) => [
            'id' => $r->id, 'label' => $r->name ?? $r->reason ?? $r->title ?? ('Motivo ' . $r->id),
        ])->values()]);
    }

    public function refund(Request $request, string $orderNumber)
    {
        $validated = $request->validate([
            'option' => ['required', 'integer', 'exists:reason_returns,id'],
            'message' => ['required', 'string', 'max:3000'],
            'products' => ['required', 'array', 'min:1'],
            'products.*' => ['required', 'integer', 'distinct'],
            'images' => ['required_if:option,1,2,8,10', 'array', 'min:2', 'max:3'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);
        $order = $this->mine($request, $orderNumber);
        $validProductIds = $order->items()->pluck('product_id')->map(fn ($id) => (int) $id)->all();
        foreach ($validated['products'] as $productId) {
            if (!in_array((int) $productId, $validProductIds, true))
                return response()->json(['message' => 'Uno dei prodotti non appartiene all’ordine.'], 422);
        }
        $lock = Cache::lock('farmacia19_refund_' . $order->id, 30);
        if (!$lock->get()) return response()->json(['message'=>'Richiesta già in elaborazione.'],409);
        $paths = [];
        try {
            if (!$this->refundAllowed($order)) return response()->json(['message'=>'Non è possibile richiedere il reso per questo ordine.'],409);
            foreach ($request->file('images', []) as $image) $paths[] = $image->store('returns', 'local');
            $return = DB::transaction(function () use ($order, $validated, $paths) {
                $current = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                if (!$this->refundAllowed($current)) return null;
                do { $token = strtoupper(Str::random(10)); } while (OrderReturn::where('token', $token)->exists());
                $return = new OrderReturn();
                $return->order_id = $current->id;
                $return->reason_id = $validated['option'];
                $return->message = $validated['message'];
                $return->token = $token;
                $return->image1 = $paths[0] ?? null;
                $return->image2 = $paths[1] ?? null;
                $return->image3 = $paths[2] ?? null;
                $return->status = 0;
                $return->save();
                foreach ($validated['products'] as $id) $return->items()->create(['product_id'=>$id]);
                return $return;
            });
            if (!$return) return response()->json(['message'=>'Richiesta già presente o non consentita.'],409);
            try {
                app(GmailService::class)->sendEmail($request->user()->email, 'Richiesta di rimborso – Ordine #' . $order->order_number,
                    'emails.refund', ['order_number'=>$order->order_number]);
                Telegram::sendMessage(['chat_id'=>config('services.telegram.orders_chat_id','-5268274429'),
                    'text'=>'⚠️ <b>Richiesta Reso.</b>' . "\n<b>Reso Numero:</b> " . e($return->token), 'parse_mode'=>'HTML']);
            } catch (\Throwable $e) { Log::error('Notifica reso app fallita', ['order_id'=>$order->id,'error'=>$e->getMessage()]); }
            return response()->json(['message'=>'Richiesta di reso inviata con successo.', 'token'=>$return->token],201);
        } finally { $lock->release(); }
    }
}
