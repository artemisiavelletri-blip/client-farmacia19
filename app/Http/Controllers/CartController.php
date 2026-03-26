<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\CartItem;

class CartController extends Controller
{
    public function add(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'redirect' => route('login')
            ], 401);
        }

        $user = Auth::user();
        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity ?? 1;

        // Calcola quantità disponibile
        $available = $product->stock;
        $cartItem = $user->cartItems()->where('product_id', $product->id)->first();
        if ($cartItem) {
            $available -= $cartItem->quantity;
        }

        if ($quantity > $available) {
            return response()->json([
                'message' => "Quantità non disponibile. Rimangono $available unità."
            ], 400);
        }

        // Aggiungi o aggiorna carrello
        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            $user->cartItems()->create([
                'product_id' => $product->id,
                'quantity' => $quantity
            ]);
        }

        // Aggiorna lo stock
        $product->stock -= $quantity;
        $product->save();

        return response()->json([
            'message' => 'Prodotto aggiunto al carrello',
            'cart_count' => $user->cartItems()->count(),
            'cart_total' => $user->cartItems()->with('product')->get()->sum->subtotal
        ]);

    }

    public function shop_cart()
    {
        return view('cart.shop-cart');
    }

    public function shop_checkout()
    {
        $user = User::findOrFail(Auth::user()->id);

        if($user->cartItems->isEmpty()){
            return view('cart.shop-cart');
        }

        return view('cart.shop-checkout', [
            'user' => $user
        ]);
    }

    public function remove($id)
    {
        $user = auth()->user();

        // Trova il prodotto nel carrello
        $cartItem = $user->cartItems()->where('id', $id)->firstOrFail();

        // Incrementa lo stock del prodotto
        $product = $cartItem->product;
        if ($product) {
            $product->stock += $cartItem->quantity;
            $product->save();
        }

        // Rimuovi l'item dal carrello
        $cartItem->delete();

        // Ricalcola il totale e il count
        $cartItems = $user->cartItems()->with('product')->get();
        $total = $cartItems->sum->subtotal;

        return response()->json([
            'message' => 'Prodotto rimosso dal carrello',
            'cart_count' => $cartItems->sum('quantity'),
            'cart_total' => number_format($total, 2)
        ]);

    }

    public function updateQuantity(Request $request, $cartItemId)
    {
        $user = auth()->user();

        // Trova il cart item
        $cartItem = $user->cartItems()->with('product')->findOrFail($cartItemId);
        $product = $cartItem->product;

        $newQuantity = (int) $request->quantity;

        if ($newQuantity < 1) {
            return response()->json(['message' => 'Quantità minima 1'], 400);
        }

        // Calcola lo stock disponibile
        // stock reale + quantità attuale in carrello (per non penalizzare l'utente)
        $available = $product->stock + $cartItem->quantity;

        if ($newQuantity > $available) {
            return response()->json([
                'message' => "Quantità non disponibile. Rimangono $available unità."
            ], 400);
        }

        // Aggiorna lo stock del prodotto
        $product->stock = $available - $newQuantity;
        $product->save();

        // Aggiorna il carrello
        $cartItem->quantity = $newQuantity;
        $cartItem->save();

        // Ricalcola totale
        $cartItems = $user->cartItems()->with('product')->get();
        $cartTotal = $cartItems->sum->subtotal;

        return response()->json([
            'message' => 'Quantità aggiornata',
            'cart_item_id' => $cartItem->id,
            'quantity' => $cartItem->quantity,
            'subtotal' => number_format($cartItem->subtotal, 2),
            'cart_total' => number_format($cartTotal, 2)
        ]);
    }

    public function shop_checkout_complete(Request $request)
    {
        if(isset($request['success'])){
            $order = Order::where('order_number',$request['order_number'])->firstOrFail();

            return view('cart.shop-checkout-complete', [
                'order_number' => $request['order_number'],
                'total' => $order->total,
                'payment_method' => $request['payment_method']
            ]);
        }

        return redirect()->route('index');
    }

}

