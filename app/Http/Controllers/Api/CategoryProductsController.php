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
                    $item['group_image_url'] = self::groupImageUrl($group?->image);
                    // Per la scheda gruppo usa la sua immagine, non quella della variante.
                    // Per i gruppi non ripiegare sull'immagine della variante.
                    $item['image_url'] = $item['group_image_url'] ?? '';
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

    public static function groupImageUrl(?string $image): ?string
    {
        if (!$image) return null;
        if (filter_var($image, FILTER_VALIDATE_URL)) return $image;
        return asset('storage-admin/product-groups/' . basename($image));
    }

    public static function productJson($product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => (string) (data_get($product, 'description') ?? ''),
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