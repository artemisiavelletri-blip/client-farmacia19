<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
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
        if (!$groupId && !$productId) return response()->json(['data' => []]);

        if ($groupId) {
            $group = DB::table('product_groups')->where('id', $groupId)->first();
            if (!$group) return response()->json(['data' => []]);
            $category = Category::find($group->category_id);
        } else {
            $product = Product::find($productId);
            if (!$product) return response()->json(['data' => []]);
            $category = Category::query()->whereHas('products', function ($query) use ($productId) {
                $query->where('products.id', $productId);
            })->first();
        }

        if (!$category) return response()->json(['data' => []]);

        $representatives = $category->products()
            ->whereNotNull('products.product_group_id')
            ->when($groupId, fn ($q) => $q->where('products.product_group_id', '!=', $groupId))
            ->selectRaw('MIN(products.id)')
            ->groupBy('products.product_group_id');

        $products = $category->products()
            ->where(function ($query) use ($representatives) {
                $query->whereNull('products.product_group_id')
                    ->orWhereIn('products.id', $representatives);
            })
            ->when($groupId, fn ($q) => $q->where(function ($sub) use ($groupId) {
                $sub->whereNull('products.product_group_id')
                    ->orWhere('products.product_group_id', '!=', $groupId);
            }))
            ->when($productId, fn ($q) => $q->where('products.id', '!=', $productId))
            ->orderBy('products.id')->limit(10)->get();

        $groups = DB::table('product_groups')
            ->whereIn('id', $products->pluck('product_group_id')->filter()->unique())
            ->get()->keyBy('id');

        return response()->json(['data' => $products->map(function ($product) use ($groups) {
            $item = CategoryProductsController::productJson($product);
            if ($product->product_group_id) {
                $group = $groups->get($product->product_group_id);
                $item['group_name'] = $group?->name;
                $item['group_image_url'] = CategoryProductsController::groupImageUrl($group?->image);
                $item['image_url'] = $item['group_image_url'] ?? '';
            }
            return $item;
        })]);
    }
}