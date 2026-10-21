<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CategoryProductsController extends Controller
{
    public function index(string $token): JsonResponse
    {
        $category = Category::where('token', $token)->firstOrFail();

        // Un prodotto senza gruppo = una scheda; un gruppo = una scheda.
        // La sottoquery mantiene gli stessi vincoli della relazione category->products().
        $representatives = $category->products()
            ->whereNotNull('products.product_group_id')
            ->selectRaw('MIN(products.id)')
            ->groupBy('products.product_group_id');

        $page = $category->products()
            ->where(function ($query) use ($representatives) {
                $query->whereNull('products.product_group_id')
                    ->orWhereIn('products.id', $representatives);
            })
            ->orderBy('products.id')
            ->paginate(20);

        return response()->json([
            'category' => ['name' => $category->name, 'token' => $category->token],
            'data' => $page->getCollection()->map(function ($p) {
                $item = self::productJson($p);
                if ($p->product_group_id) {
                    $group = DB::table('product_groups')->where('id', $p->product_group_id)->first();
                    $item['group_name'] = $group?->name;
                    // La tabella product_groups non ha una colonna immagine:
                    // usiamo l'immagine del prodotto rappresentativo.
                }
                return $item;
            }),
            'pagination' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public static function productJson($product): array
    {
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
            'product_group_id' => $product->product_group_id,
            'variant_color' => $product->variant_color,
            'variant_size' => $product->variant_size,
        ];
    }
}