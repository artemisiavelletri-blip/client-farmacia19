<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Farmacia19FcmTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'min:20', 'max:512'],
            'platform' => ['sometimes', 'in:android,ios'],
        ]);

        $userId = $request->user()->id;
        $now = now();

        // Un token appartiene a un solo account per volta, anche dopo cambio login.
        DB::table('farmacia19_fcm_tokens')->upsert([
            [
                'user_id' => $userId,
                'token' => $data['token'],
                'platform' => $data['platform'] ?? 'android',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['token'], ['user_id', 'platform', 'updated_at']);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'min:20', 'max:512'],
        ]);

        DB::table('farmacia19_fcm_tokens')
            ->where('user_id', $request->user()->id)
            ->where('token', $data['token'])
            ->delete();

        return response()->json(['success' => true]);
    }
}
