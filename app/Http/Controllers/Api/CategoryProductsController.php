<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryProductsController extends Controller
{
    public function index(string $token): JsonResponse
    {
        $category = Category::where('token', $token)->firstOrFail();

        // Riutilizza la relazione products() già usata nella homepage Blade.
        $page = $category->products()->paginate(20);

        $products = $page->getCollection()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'ean' => $product->ean,
                'minsan' => $product->minsan,
                'price' => (float) $product->price,
                'discount_price' => $product->discountPrice !== null
                    ? (float) $product->discountPrice : null,
                'image_url' => $product->image
                    ? asset('storage-admin/' . $product->image) : null,
            ];
        });

        return response()->json([
            'category' => [
                'name' => $category->name,
                'token' => $category->token,
            ],
            'data' => $products,
            'pagination' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }
}