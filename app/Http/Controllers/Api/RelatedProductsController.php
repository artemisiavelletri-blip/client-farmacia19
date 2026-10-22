<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatedProductsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $groupId = $request->integer('group_id') ?: null;
        $productId = $request->integer('product_id') ?: null;

        if (!$groupId && !$productId) {
            return response()->json(['data' => []]);
        }

        // Per una scheda gruppo usiamo una sua variante come prodotto di riferimento.
        // Per i prodotti singoli usiamo direttamente il prodotto aperto.
        $product = $groupId
            ? Product::where('product_group_id', $groupId)->orderBy('id')->first()
            : Product::find($productId);

        if (!$product) {
            return response()->json(['data' => []]);
        }

        // Stessa logica del sito: non sostituire con prodotti casuali della categoria.
        $related = collect($product->relatedProducts(4));

        // Evita di proporre il prodotto aperto o un'altra variante del suo gruppo.
        $related = $related->filter(function ($item) use ($product, $groupId) {
            if (!$item instanceof Product || $item->id === $product->id) return false;
            $currentGroupId = $groupId ?: $product->product_group_id;
            return !$currentGroupId || (int) $item->product_group_id !== (int) $currentGroupId;
        })->unique(fn ($item) => $item->product_group_id
            ? 'group:' . $item->product_group_id
            : 'product:' . $item->id)->values();

        $groups = DB::table('product_groups')
            ->whereIn('id', $related->pluck('product_group_id')->filter()->unique())
            ->get()->keyBy('id');

        return response()->json([
            'data' => $related->map(function (Product $item) use ($groups) {
                $data = CategoryProductsController::productJson($item);
                if ($item->product_group_id) {
                    $group = $groups->get($item->product_group_id);
                    $data['group_name'] = $group?->name;
                    $data['group_image_url'] = CategoryProductsController::groupImageUrl($group?->image);
                    $data['image_url'] = $data['group_image_url'] ?? '';
                }
                return $data;
            })->values(),
        ]);
    }
}
