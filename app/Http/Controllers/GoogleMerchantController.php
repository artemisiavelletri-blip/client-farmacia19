<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class GoogleMerchantController extends Controller
{
    public function feed()
    {
        $path = storage_path('app/feeds/google-merchant.xml');

        abort_unless(File::exists($path), 404);

        return response()->file($path, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}