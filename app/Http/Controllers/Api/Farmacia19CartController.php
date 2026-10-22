<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Farmacia19CartController extends Controller
{
    private function summary($user): array
    {
        $items = $user->cartItems()->with('product')->get();
        return [
            'cart_count' => (int) $items->sum('quantity'),
            'cart_total' => round((float) $items->sum(fn ($item) => (float) $item->subtotal), 2),
            'items' => $items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product?->name ?? 'Prodotto',
                'image_url' => $item->product?->image
                    ? asset('storage-admin/' . ltrim($item->product->image, '/')) : null,
                'quantity' => (int) $item->quantity,
                'subtotal' => round((float) $item->subtotal, 2),
                'unit_price' => $item->quantity > 0
                    ? round((float) $item->subtotal / (int) $item->quantity, 2) : 0,
                'available' => $item->product && $item->product->stock > 0,
            ])->values(),
        ];
    }

    public function index(Request $request)
    {
        return response()->json(['success' => true] + $this->summary($request->user()));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);
        $user = $request->user();
        DB::transaction(function () use ($data, $user) {
            $product = Product::whereKey($data['product_id'])->lockForUpdate()->firstOrFail();
            $quantity = (int) $data['quantity'];
            // Il tuo carrello sottrae già la giacenza all'aggiunta: non sottrarre
            // nuovamente la quantità precedentemente inserita nel carrello.
            if ($product->stock < $quantity) {
                throw ValidationException::withMessages(['quantity' => 'Quantità non disponibile.']);
            }
            $item = $user->cartItems()->where('product_id', $product->id)->first();
            if ($item) {
                $item->quantity += $quantity;
                $item->save();
            } else {
                $item = $user->cartItems()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                ]);
                $discountIds = $user->cartItems()->where('id', '!=', $item->id)
                    ->with('discounts')->get()
                    ->flatMap(fn ($cartItem) => $cartItem->discounts->pluck('id'))
                    ->unique()->values()->all();
                if ($discountIds) $item->discounts()->syncWithoutDetaching($discountIds);
            }
            $product->stock -= $quantity;
            $product->save();
        }, 3);
        return response()->json(['success' => true, 'message' => 'Prodotto aggiunto al carrello.'] + $this->summary($user));
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]);
        $user = $request->user();
        DB::transaction(function () use ($user, $id, $data) {
            $item = $user->cartItems()->findOrFail($id);
            $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();
            $new = (int) $data['quantity'];
            $difference = $new - (int) $item->quantity;
            if ($difference > $product->stock) {
                throw ValidationException::withMessages(['quantity' => 'Quantità non disponibile.']);
            }
            $product->stock -= $difference;
            $product->save();
            $item->quantity = $new;
            $item->save();
        }, 3);
        return response()->json(['success' => true] + $this->summary($user));
    }

    public function remove(Request $request, int $id)
    {
        $user = $request->user();
        DB::transaction(function () use ($user, $id) {
            $item = $user->cartItems()->findOrFail($id);
            $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();
            $product->stock += $item->quantity;
            $product->save();
            $item->discounts()->detach();
            $item->delete();
        }, 3);
        return response()->json(['success' => true] + $this->summary($user));
    }
}
