<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

use App\Models\Tag;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Promotion;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Doctor;

class ProductController extends Controller
{
    public function shop_grid_category(Request $request, $token)
    {
        $category = Category::where('token', $token)->firstOrFail();
        $subcategory = $category->subCategories()->orderBy('name')->get();
        $search = null;

        $query = Product::where('category_id', $category->id);

        if (request('sub_category')) {
            $query->where('subcategory_id', request('sub_category'));
        }

        if (request('search')) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('productGroup', function ($group) {
                        $group->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
            $search = request('search');
        }

        if (request()->filled('brands')) {
            $query->whereIn('brand_id', request('brands'));
        }

        if (mb_strtolower($category->name) === 'calzature') {
            $query = $this->groupFootwearProducts($query);
        }

        $products = $query->with('productGroup')
            ->paginate(12)->onEachSide(0)->withQueryString();

        return view('products.shop-grid', [
            'category' => $category,
            'subcategory' => $subcategory,
            'products' => $products,
            'search' => $search
        ]);
    }

    public function shop_single(Request $request, $id)
    {
        $userId = auth()->id();
        $sessionId = session()->getId();

        $product = Product::where('ean', $id)
            ->orWhere('minsan', $id)
            ->firstOrFail();
        $tags = $product->tags()->get();

        $relatedProducts = $product->relatedProducts(4);

        $product->load('productGroup');

        $variants = collect();

        if ($product->product_group_id) {
            $variants = Product::where('product_group_id', $product->product_group_id)
                ->orderBy('variant_color')
                ->orderBy('variant_size')
                ->get();
        }

        $alreadyViewed = DB::table('product_views')
            ->where('product_id', $product->id)
            ->where(function ($query) use ($userId, $sessionId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->whereDate('viewed_at', today())
            ->exists();

        if (!$alreadyViewed) {
            DB::table('product_views')->insert([
                'product_id' => $product->id,
                'user_id' => $userId,
                'session_id' => $sessionId,
                'viewed_at' => now(),
            ]);

            $product->increment('views_count');
        }

        return view('products.shop-single', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'tags' => $tags,
            'variants' => $variants,
        ]);
    }

    public function shop_search($type = null)
    {
        $search = trim((string) request('search', ''));

        $searchTerms = collect(preg_split('/\s+/', $search))
            ->filter()
            ->values();

        $query = Product::query();

        if ($search !== '') {

            $query->where(function ($q) use ($search, $searchTerms) {

                // Ricerca per nome prodotto
                $q->where(function ($subQuery) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $subQuery->where('name', 'LIKE', '%' . $term . '%');
                    }
                });

                // Ricerca per EAN
                $q->orWhere('ean', $search);

                // Ricerca per MINSAN
                $q->orWhere('minsan', $search);

                // Ricerca per tag
                $q->orWhereHas('tags', function ($subQuery) use ($search) {
                    $subQuery->where(function ($tagQuery) use ($search) {
                        $tagQuery->where('name', 'LIKE', '%' . $search . '%')
                            ->orWhere('slug', 'LIKE', '%' . $search . '%');
                    });
                });

                // Ricerca per brand
                $q->orWhereHas('brandRelation', function ($subQuery) use ($search) {
                    $subQuery->where('name', 'LIKE', '%' . $search . '%');
                });

                // Ricerca per gruppo prodotto
                $q->orWhereHas('productGroup', function ($subQuery) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $subQuery->where('name', 'LIKE', '%' . $term . '%');
                    }
                });
            });
        }

        $category = null;

        if (request('category')) {
            $categoryModel = Category::where('token', request('category'))->first();

            if ($categoryModel) {
                $query->where('category_id', $categoryModel->id);
                $category = $categoryModel->name;
            }
        }

        if (request('brand')) {
            $query->where('brand_id', request('brand'));

            $brand = Brand::find(request('brand'));

            if ($brand) {
                $search = $brand->name;
            }
        }

        $tag = null;

        if (request('tag')) {
            $tagModel = Tag::where('slug', request('tag'))->first();

            if ($tagModel) {
                $query->whereHas('tags', function ($q) use ($tagModel) {
                    $q->where('tags.id', $tagModel->id);
                });

                $tag = $tagModel->name;
            }
        }

        $type_message = null;

        if ($type === 'offerts') {
            $query->where('discountPrice', '>', 0)
                  ->where('stock', '>', 0);

            $type_message = 'Prodotti in Promozione';
        }

        if ($type === 'new') {
            $query->where('new', 1);

            $type_message = 'Novità';
        }

        // Manteniamo la logica di raggruppamento esistente
        $query = $this->groupFootwearProducts($query);

        // Paginazione senza COUNT(*) completo
        $products = $query
            ->with('productGroup')
            ->simplePaginate(12)
            ->withQueryString();

        return view('products.shop-search', [
            'products' => $products,
            'search' => $search,
            'category' => $category,
            'tag' => $tag,
            'type_message' => $type_message,
        ]);
    }

    /**
     * Mostra un solo prodotto per gruppo, scegliendolo tra quelli che
     * soddisfano TUTTI i filtri già applicati (anche EAN e promozioni).
     * I prodotti senza gruppo rimangono singoli.
     */
    private function groupFootwearProducts($query)
    {
        $matchingProducts = clone $query;
        $matchingIds = $matchingProducts->reorder()->select('products.id');

        return $query->where(function ($q) use ($matchingIds) {
            $q->whereNull('products.product_group_id')
                ->orWhereNotExists(function ($subquery) use ($matchingIds) {
                    $subquery->selectRaw('1')
                        ->from('products as other')
                        ->whereColumn('other.product_group_id', 'products.product_group_id')
                        ->whereColumn('other.id', '<', 'products.id')
                        ->whereIn('other.id', $matchingIds);
                });
        });
    }

    public function addCoupon(Request $request)
    {
        if (!$request->filled('coupon')) {
            return;
        }

        $code = trim($request->coupon);
        $user = auth()->user();

        

        $today = Carbon::today();

        $promotion = Promotion::where('name', strtolower($code))
            ->where('active', 1)
            ->where(function ($query) use ($today) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today);
            })
            ->first();

        if (!$promotion) {
            return;
        }

        if (
            $promotion->max_use &&
            Order::where('user_id', $user->id)
                ->where('coupon_id', $promotion->id)
                ->where('status', '!=', 'cancelled')
                ->count() >= $promotion->max_use
        ) {
            return;
        }

        foreach ($user->cartItems as $cartItem) {
            $cartItem->discounts()->sync([$promotion->id]);
        }

        $doctor = Doctor::where('token', $code)->first();

        if ($doctor) {
            $user->prescriber_id = $doctor->token;
            $user->save();
        }
    }

    public function removeCoupon(Request $request)
    {
        $user = auth()->user();

        foreach ($user->cartItems as $cartItem) {

            // Recupera i codici sconto prima di eliminarli
            $discounts = $cartItem->discounts()->get();

            foreach ($discounts as $discount) {

                // Rimuove il medico solo se il codice coincide
                if (
                    $user->prescriber_id !== null &&
                    strcasecmp($discount->name, $user->prescriber_id) === 0
                ) {
                    $user->prescriber_id = null;
                    $user->save();
                }
            }

            // Rimuove gli sconti dal carrello
            $cartItem->discounts()->detach();
        }

        return response()->json([
            'success' => true,
            'message' => 'Coupon rimosso.'
        ]);
    }
}
