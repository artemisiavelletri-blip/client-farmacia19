<?php

namespace App\Http\Controllers;

use App\Models\Product;

class GoogleMerchantController extends Controller
{
    public function feed()
    {
        $products = Product::query()
            ->with('brandRelation')
            ->where('hidden', 0)
            ->where('sop_otc',0)
            ->where('vet',0)
            ->get();

        return response()
            ->view('feeds.google-merchant', [
                'products' => $products,
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}