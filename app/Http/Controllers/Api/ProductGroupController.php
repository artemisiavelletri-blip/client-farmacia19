<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductGroupController extends Controller
{
    public function show(int $id): JsonResponse
    {
        $group = DB::table('product_groups')->where('id', $id)->first();
        abort_unless($group, 404);

        $variants = Product::query()
            ->where('product_group_id', $id)
            ->orderBy('id')
            ->get();

        return response()->json([
            'group' => ['id' => $group->id, 'name' => $group->name, 'slug' => $group->slug],
            'variants' => $variants->map(fn ($p) => CategoryProductsController::productJson($p)),
        ]);
    }
}