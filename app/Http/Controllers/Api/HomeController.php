<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Image;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->orderBy('position')
            ->get()
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'token' => $category->token,
                'image_url' => $category->image
                    ? asset('storage-admin/' . $category->image)
                    : null,
                'products_count' => $category->products_count,
            ]);

        $products = Product::query()
            ->where('discounted', 1)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'ean' => $product->ean,
                    'minsan' => $product->minsan,
                    'price' => (float) $product->price,
                    'discount_price' =>
                        $product->discountPrice !== null
                            ? (float) $product->discountPrice
                            : null,
                    'image_url' => $product->image
                        ? asset('storage-admin/' . $product->image)
                        : null,
                ];
            });

        $sliders = Image::query()
            ->where('dashboard', 1)
            ->orderBy('position')
            ->get()
            ->map(fn ($image) => [
                'id' => $image->id,
                'image_url' => $image->path
                    ? asset('storage-admin/' . $image->path)
                    : null,
                'link' => $image->link,
            ]);

        $bestSellersByCategory = Category::whereNotNull('position')
        ->orderBy('position')
        ->with('productsBestseller')
        ->get()
        ->map(function ($category) {
            $seen = [];
            $products = $category->productsBestseller
                ->filter(function ($product) use (&$seen) {
                    $key = $product->product_group_id
                        ? 'group:' . $product->product_group_id
                        : 'product:' . $product->id;
                    if (isset($seen[$key])) return false;
                    $seen[$key] = true;
                    return true;
                })
                ->map(function ($product) {
                    $item = CategoryProductsController::productJson($product);
                    if ($product->product_group_id) {
                        $group = DB::table('product_groups')
                            ->where('id', $product->product_group_id)->first();
                        $item['group_name'] = $group?->name;
                        $item['group_image_url'] = CategoryProductsController::groupImageUrl($group?->image);
                        $item['image_url'] = $item['group_image_url'] ?? '';
                    }
                    return $item;
                })->values()->all();

            return [
                'category_name' => $category->name,
                'products' => $products,
            ];
        })
        ->filter(fn ($section) => count($section['products']) > 0)
        ->values()
        ->all();

        return response()->json([
            'categories' => $categories,
            'products_discounted' => $products,
            'sliders' => $sliders,
            'best_sellers_by_category' => $bestSellersByCategory,
        ]);
    }
}
