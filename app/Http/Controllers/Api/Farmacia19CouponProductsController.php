<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Farmacia19CouponProductsController extends Controller
{
    public function index(Request $request, string $token)
    {
        abort_unless($request->user(), 401);
        $coupon = Promotion::query()->where('token', $token)->where('active', 1)
            ->where('user', 1)
            ->where(function ($q) { $q->whereNull('end_date')->orWhereDate('end_date', '>=', today()); })
            ->firstOrFail();

        // Usa la stessa relazione categoria/prodotti dell'API categorie esistente.
        if ($coupon->product_id) {
            $query = Product::query()->where('products.id', $coupon->product_id);
        } elseif ($coupon->brand_id) {
            $query = Product::query()->where('products.brand_id', $coupon->brand_id);
        } elseif ($coupon->category_id) {
            $category = Category::findOrFail($coupon->category_id);
            $query = $category->products();
            if ($coupon->subcategory_id) {
                $query->where('products.subcategory_id', $coupon->subcategory_id);
            }
        } elseif ((bool) $coupon->all_products) {
            $query = Product::query();
        } else {
            return response()->json(['message' => 'Coupon senza ambito prodotti configurato.'], 422);
        }

        // Rappresenta una sola variante per gruppo, come nel catalogo attuale.
        // La sottoquery deriva dallo stesso filtro per non mostrare gruppi estranei.
        $matching = clone $query;
        $representatives = $matching->whereNotNull('products.product_group_id')
            ->selectRaw('MIN(products.id)')->groupBy('products.product_group_id');
        $page = $query->where(function ($q) use ($representatives) {
            $q->whereNull('products.product_group_id')->orWhereIn('products.id', $representatives);
        })->orderBy('products.id')->paginate(20);

        $groupIds = $page->getCollection()->pluck('product_group_id')->filter()->unique();
        $groups = DB::table('product_groups')->whereIn('id', $groupIds)->get()->keyBy('id');
        return response()->json([
            'data' => $page->getCollection()->map(function ($p) use ($groups) {
                $item = CategoryProductsController::productJson($p);
                if ($p->product_group_id) {
                    $group = $groups->get($p->product_group_id);
                    $item['group_name'] = $group?->name;
                    $item['group_image_url'] = CategoryProductsController::groupImageUrl($group?->image);
                    $item['image_url'] = $item['group_image_url'] ?? '';
                }
                return $item;
            })->values(),
            'pagination' => [
                'current_page' => $page->currentPage(), 'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(), 'total' => $page->total(),
            ],
        ])->header('Cache-Control', 'private, no-store');
    }
}
