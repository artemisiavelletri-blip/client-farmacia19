<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class Farmacia19OrdersApiController extends Controller
{
    private function serializeOrder(Order $order, bool $detail = false): array
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
        $result = [
            'order_number' => $order->order_number,
            'created_at' => $order->created_at?->toIso8601String(),
            'total' => (float) $order->total,
            'status' => $order->status,
            'status_step' => (int) ($order->status_step ?? 0),
            'image_url' => $items->first()['image_url'] ?? asset('/storage-admin/products/file-non-disponibile.jpg'),
        ];
        if ($detail) {
            $result += [
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
            ];
        }
        return $result;
    }

    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.product'])
            ->orderByDesc('created_at')
            ->paginate(10);
        return response()->json([
            'data' => $orders->getCollection()->map(fn ($o) => $this->serializeOrder($o))->values(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'has_more' => $orders->hasMorePages(),
        ]);
    }

    public function show(Request $request, string $orderNumber)
    {
        $order = Order::where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->with(['items.product', 'city'])
            ->firstOrFail();
        return response()->json(['data' => $this->serializeOrder($order, true)]);
    }
}
