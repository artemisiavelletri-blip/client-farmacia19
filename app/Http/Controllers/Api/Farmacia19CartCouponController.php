<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Order;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Farmacia19CartCouponController extends Controller
{
    public function summary(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Non autorizzato'], 401);

        $items = $user->cartItems()->with(['product', 'discounts'])->get();
        $coupon = $items->pluck('discounts')->flatten()->first();

        // subtotal è già influenzato dagli sconti percentuali nel modello CartItem.
        // La base visualizzata comprende il risparmio complessivo restituito dall'accessor User.
        $discount = (float) $user->cart_discount;
        $shipping = $items->isEmpty() ? 0.0 : (float) $user->cart_shipping_cost;
        $total = $items->isEmpty() ? 0.0 : (float) $user->cart_total;
        $productsTotal = max(0.0, $total - $shipping + $discount);

        return response()->json([
            'products_total' => round($productsTotal, 2),
            'discount' => round($discount, 2),
            'shipping_cost' => round($shipping, 2),
            'total' => round($total, 2),
            'coupon' => $coupon ? $coupon->name : null,
        ]);
    }

    public function addCoupon(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Non autorizzato'], 401);
        $request->validate(['coupon' => ['required', 'string', 'max:100']]);
        $code = trim($request->input('coupon'));
        $today = Carbon::today();

        $promotion = Promotion::where('name', strtolower($code))
            ->where('active', 1)
            ->where(function ($query) use ($today) {
                $query->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })->first();

        if (!$promotion || !$promotion->isValid()) {
            return response()->json(['success' => false, 'message' => 'Coupon non valido o scaduto.'], 422);
        }

        if ($promotion->max_use && Order::where('user_id', $user->id)
            ->where('coupon_id', $promotion->id)
            ->where('status', '!=', 'cancelled')->count() >= $promotion->max_use) {
            return response()->json(['success' => false, 'message' => 'Coupon già utilizzato.'], 422);
        }

        $items = $user->cartItems()->with('product')->get();
        if ($items->isEmpty()) return response()->json(['success' => false, 'message' => 'Carrello vuoto.'], 422);

        foreach ($items as $item) {
            $item->discounts()->sync([$promotion->id]);
        }

        $doctor = Doctor::where('token', $code)->first();
        if ($doctor) {
            $user->prescriber_id = $doctor->token;
            $user->save();
        }

        return response()->json(['success' => true, 'message' => 'Coupon applicato.']);
    }

    public function removeCoupon(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Non autorizzato'], 401);

        foreach ($user->cartItems()->with('discounts')->get() as $item) {
            foreach ($item->discounts as $discount) {
                if ($user->prescriber_id !== null &&
                    strcasecmp($discount->name, $user->prescriber_id) === 0) {
                    $user->prescriber_id = null;
                    $user->save();
                }
            }
            $item->discounts()->detach();
        }

        return response()->json(['success' => true, 'message' => 'Coupon rimosso.']);
    }
}
