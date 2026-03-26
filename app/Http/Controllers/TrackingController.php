<?php

namespace App\Http\Controllers;

use App\Services\TrackingMoreService;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    protected $trackingMore;

    public function __construct(TrackingMoreService $trackingMore)
    {
        $this->trackingMore = $trackingMore;
    }

    public function track(Request $request)
    {
        $request->validate([
            'carrier' => 'required|string',
            'tracking_number' => 'required|string'
        ]);

        $tracking = (new TrackingMoreService())->track('poste-italiane', $request['tracking_number']);

        return response()->json($tracking);
    }
}