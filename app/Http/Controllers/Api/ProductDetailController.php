<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductDetailController extends Controller
{
    public function show(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        return response()->json([
            'product' => CategoryProductsController::productJson($product),
        ]);
    }
}