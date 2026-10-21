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
            ->whereNotNull('position')
            ->orderBy('position')
            ->withCount('products')
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

        return response()->json([
            'categories' => $categories,
            'products_discounted' => $products,
            'sliders' => $sliders,
        ]);
    }
}
