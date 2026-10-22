<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Farmacia19PaymentMethodsController extends Controller
{
    public function index(Request $request)
    {
        $cards = PaymentMethod::where('user_id', $request->user()->id)
            ->orderByDesc('default')->orderByDesc('id')->get()
            ->map(fn ($card) => [
                'id' => $card->id,
                'holder_name' => $card->holder_name,
                'brand' => $card->brand,
                'last4' => $card->last4,
                'exp_month' => $card->exp_month,
                'exp_year' => $card->exp_year,
                'is_default' => (bool) ($card->default ?? $card->is_default),
            ])->values();

        return response()->json(['success' => true, 'data' => $cards]);
    }

    public function start(Request $request)
    {
        // Ticket monouso e breve: NON contiene il token Bearer API.
        $ticket = Str::random(64);
        Cache::put('farmacia19_card_bridge_' . hash('sha256', $ticket),
            (int) $request->user()->id, now()->addMinutes(5));

        return response()->json([
            'success' => true,
            'url' => route('farmacia19.card.bridge', ['ticket' => $ticket]),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $userId = (int) $request->user()->id;
        DB::transaction(function () use ($userId, $id) {
            $card = PaymentMethod::where('user_id', $userId)
                ->whereKey($id)->lockForUpdate()->firstOrFail();
            $wasDefault = (bool) $card->default;
            $card->default = 0;
            $card->is_default = 0;
            $card->save();
            $card->delete(); // SoftDeletes, se presente nel modello.

            if ($wasDefault) {
                PaymentMethod::where('user_id', $userId)
                    ->update(['default' => 0, 'is_default' => 0]);
                $replacement = PaymentMethod::where('user_id', $userId)
                    ->orderByDesc('id')->first();
                if ($replacement) {
                    $replacement->default = 1;
                    $replacement->is_default = 1;
                    $replacement->save();
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'Carta rimossa.']);
    }
}
