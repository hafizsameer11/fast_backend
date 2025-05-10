<?php

// app/Http/Controllers/Rider/DistanceController.php
namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckProximityRequest;
use App\Services\DistanceCheckService;
use App\Helpers\ResponseHelper;

class DistanceController extends Controller
{
    protected $service;

    public function __construct(DistanceCheckService $service)
    {
        $this->service = $service;
    }

    public function check(CheckProximityRequest $request)
    {
        $data = $request->validated();

        $inRange = $this->service->isWithin10Km(
            $data['rider_lat'],
            $data['rider_lng'],
            $data['parcel_lat'] ?? null,
            $data['parcel_lng'] ?? null,
            $data['parcel_address'] ?? null
        );

        return ResponseHelper::success([
            'in_range' => $inRange,
            'message' => $inRange ? 'Within 10KM' : 'Too far'
        ]);
    }
}

