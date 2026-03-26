<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\City;

class CityController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));

        $cities = City::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($q) . '%'])
            ->orderByRaw('LOWER(name) = ? DESC', [strtolower($q)])
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json($cities);
    }
}
