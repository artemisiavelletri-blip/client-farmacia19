<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Farmacia19CitiesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:80'],
        ]);

        // ATTENZIONE: verificare che la tabella sia "cities" e il campo sia "name".
        $cities = DB::table('cities')
            ->select('id', 'name')
            ->where('name', 'like', '%'.$data['q'].'%')
            ->orderBy('name')
            ->limit(30)
            ->get();

        return response()->json(['data' => $cities]);
    }
}