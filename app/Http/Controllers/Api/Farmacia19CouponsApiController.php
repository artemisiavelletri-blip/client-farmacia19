<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;

class Farmacia19CouponsApiController extends Controller
{
    private function available()
    {
        return Promotion::query()
            ->where('active', 1)
            ->where('user', 1)
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            });
    }

    private function payload(Promotion $coupon, bool $includeCode = false): array
    {
        $data = [
            'token' => $coupon->token,
            'description' => $coupon->description,
            'fixDiscount' => $coupon->fixDiscount,
            'percentage' => $coupon->percentage,
            'minimum_purchase' => $coupon->minimum_purchase,
            'start_date' => $coupon->start_date,
            'end_date' => $coupon->end_date,
            'application_type' => $coupon->application_type,
            'application_label' => $coupon->application_label,
        ];
        if ($includeCode) {
            $data['name'] = $coupon->name;
            $data['application_url'] = $coupon->application_url;
        }
        return $data;
    }

    public function index(Request $request)
    {
        // Le rotte devono essere nel gruppo Farmacia19ApiToken.
        abort_unless($request->user(), 401);
        $coupons = $this->available()->orderBy('id', 'desc')->paginate(10);
        return response()->json([
            'data' => $coupons->getCollection()->map(fn ($c) => $this->payload($c))->values(),
            'current_page' => $coupons->currentPage(),
            'has_more' => $coupons->hasMorePages(),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function reveal(Request $request)
    {
        abort_unless($request->user(), 401);
        $validated = $request->validate(['token' => ['required', 'string', 'max:255']]);
        $coupon = $this->available()->where('token', $validated['token'])->firstOrFail();
        return response()->json(['success' => true, 'coupon' => $this->payload($coupon, true)])
            ->header('Cache-Control', 'private, no-store');
    }
}
