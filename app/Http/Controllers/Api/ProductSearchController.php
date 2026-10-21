<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductSearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));
        if ($term === '') {
            return $this->respond(collect(), 1, 1, 0);
        }

        // Codici EAN/MINSAN: conserva l'esatta variante identificata dallo scanner.
        // Non raggruppare questa ricerca: Flutter deve conoscere l'ID della variante.
        $exact = Product::query()->where('ean', $term)->orWhere('minsan', $term)->first();
        if ($exact !== null) {
            return $this->respond(collect([$exact]), 1, 1, 1);
        }

        $like = '%' . addcslashes($term, '%_\\') . '%';

        // I gruppi corrispondono quando combacia il nome del gruppo O di una variante.
        $matchingGroups = DB::table('product_groups')
            ->select('id')
            ->where('name', 'like', $like)
            ->orWhereIn('id', DB::table('products')
                ->select('product_group_id')
                ->whereNotNull('product_group_id')
                ->where('name', 'like', $like));

        // Un solo prodotto rappresentativo per gruppo, anche se molte varianti
        // corrispondono alla ricerca. I prodotti senza gruppo restano indipendenti.
        $representatives = DB::table('products')
            ->selectRaw('MIN(id)')
            ->whereIn('product_group_id', $matchingGroups)
            ->groupBy('product_group_id');

        $page = Product::query()
            ->where(function ($query) use ($like, $representatives) {
                $query->where(function ($single) use ($like) {
                    $single->whereNull('product_group_id')
                        ->where('name', 'like', $like);
                })->orWhereIn('id', $representatives);
            })
            ->orderBy('name')
            ->paginate(20);

        return $this->respond($page->getCollection(),
            $page->currentPage(), $page->lastPage(), $page->total());
    }

    private function respond($products, int $current, int $last, int $total): JsonResponse
    {
        $groupIds = $products->pluck('product_group_id')->filter()->unique()->values();
        $groups = DB::table('product_groups')->whereIn('id', $groupIds)->get()->keyBy('id');

        return response()->json([
            'data' => $products->map(function ($product) use ($groups) {
                $group = $product->product_group_id
                    ? $groups->get($product->product_group_id) : null;
                $item = CategoryProductsController::productJson($product);
                $item['group_name'] = $group?->name;
                $item['group_image_url'] = CategoryProductsController::groupImageUrl($group?->image);
                // Per la griglia dei risultati testuali il gruppo ha una sua immagine.
                // L'immagine della variante resta in variant_image_url per lo scanner.
                $item['variant_image_url'] = $item['image_url'];
                if ($group !== null) {
                    $item['image_url'] = $item['group_image_url'] ?? '';
                }
                return $item;
            })->values(),
            'pagination' => [
                'current_page' => $current,
                'last_page' => $last,
                'per_page' => 20,
                'total' => $total,
            ],
        ]);
    }
}