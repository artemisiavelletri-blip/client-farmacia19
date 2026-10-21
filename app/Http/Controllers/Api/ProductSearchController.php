<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductSearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        if ($query === '') {
            return response()->json([
                'data' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 20,
                    'total' => 0,
                ],
            ]);
        }

        $products = Product::query()
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', '%' . $query . '%')
                    ->orWhere('ean', $query)
                    ->orWhere('minsan', $query);
            })
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'data' => $products->getCollection()->map(function ($product) {
                $group = $product->product_group_id
                    ? DB::table('product_groups')->where('id', $product->product_group_id)->first()
                    : null;
                return [
                    'group_name' => $group?->name,
                    'group_image_url' => CategoryProductsController::groupImageUrl($group?->image),
                    'id' => $product->id,
                    'product_group_id' => $product->product_group_id,
                    'variant_color' => $product->variant_color,
                    'variant_size' => $product->variant_size,
                    'name' => $product->name,
                    'ean' => $product->ean,
                    'minsan' => $product->minsan,
                    'price' => (float) $product->price,
                    'discount_price' => $product->discountPrice !== null
                        ? (float) $product->discountPrice : null,
                    'image_url' => $product->image
                        ? asset('storage-admin/' . $product->image) : null,
                ];
            }),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }
}