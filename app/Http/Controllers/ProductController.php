<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Tag;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;

class ProductController extends Controller
{
    public function shop_grid_category(Request $request,$token)
    {
        $category = Category::where('token',$token)->firstOrFail();
        $subcategory = $category->subCategories()->orderBy('name')->get();
        
        $query = Product::where('category_id', $category->id);

        if (request('sub_category')) {
            $query->where('subcategory_id', request('sub_category'));
        }

        if (request('search')) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%');
            });
        }

        if (request()->filled('brands')) {
            $query->whereIn('brand_id', request('brands'));
        }

        $products = $query->paginate(12)->onEachSide(0)->withQueryString();

        return view('products.shop-grid', [
            'category' => $category,
            'subcategory' => $subcategory,
            'products' => $products
        ]);
    }


    public function shop_single(Request $request,$id)
    {
        $userId = auth()->id();
        $sessionId = session()->getId();

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

            // contatore veloce
            $product->increment('views_count');
        }

        $product = Product::where('ean', $id)
                   ->orWhere('minsan', $id)
                   ->firstOrFail();
        $tags = $product->tags()->get();

        $relatedProducts = $product->relatedProducts(4);

        return view('products.shop-single', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'tags' => $tags
        ]);
    }

    public function shop_search($type = null)
    {
        $search = request('search');
        $query = Product::where(function ($q) {
            $q->where('name', 'LIKE', '%' . request('search') . '%')
            ->orWhere('ean', request('search'))
            ->orWhere('minsan', request('search'))

            ->orWhereHas('tags', function ($q) {
              $q->where('name', 'LIKE', '%' . request('search') . '%')
                ->orWhere('slug', 'LIKE', '%' . request('search') . '%');
          })

          ->orWhereHas('brandRelation', function ($q) {
              $q->where('name', 'LIKE', '%' . request('search') . '%');
          });
        });

        $category = null;

        if(request('category')){
            $category = Category::where('token',request('category'));
            if($category->exists()){
                $category = $category->get()->first();
                $query->where('category_id',$category->id);
                $category = $category->name;
            }
            else {
                $category = null;
            }
        }

        if(request('brand')){
            $query->where('brand_id',request('brand'));
            $search = Brand::where('id',request('brand'))->get()->first()->name;
        }


        if (request('tag')) {
            $tagSlug = request('tag');
            
            $tag = Tag::where('slug', $tagSlug)->first();

            if ($tag) {
                // Filtra i prodotti che hanno questo tag
                $query->whereHas('tags', function($q) use ($tag) {
                    $q->where('tags.id', $tag->id);
                });
            }
        }

        if(isset($tag)){
            $tag = $tag->name;
        } else {
            $tag = null;
        }

        $type_message = null;

        if ($type == 'offerts') {
            $query->where('discountPrice', '>', 0);
            $type_message = 'Prodotti in Promozione';
        } 

        if ($type == 'new') {
            $query->where('new', 1);
            $type_message = 'Novità';
        }           

        $products = $query->paginate(12)->onEachSide(0)->withQueryString();

        return view('products.shop-search', [
            'products' => $products,
            'search' => $search,
            'category' => $category,
            'tag' => $tag,
            'type_message' => $type_message
        ]);
    }
}
