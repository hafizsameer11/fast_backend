<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RiderLocation;
use App\Helpers\ResponseHelper;

class RiderLocationController extends Controller
{
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $riderId = auth()->id();

        $location = RiderLocation::updateOrCreate(
            ['rider_id' => $riderId],
            ['latitude' => $request->latitude, 'longitude' => $request->longitude]
        );

        return ResponseHelper::success($location, 'Location updated');
    }

    public function getRiderLocation($riderId)
    {
        $location = RiderLocation::where('rider_id', $riderId)->first();

        if (!$location) {
            return ResponseHelper::error('Location not found', 404);
        }

        return ResponseHelper::success($location, 'Rider location fetched');
    }
}
