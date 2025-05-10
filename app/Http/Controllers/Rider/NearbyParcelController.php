<?php

// app/Http/Controllers/Rider/NearbyParcelController.php
namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Http\Requests\NearbyParcelRequest;
use App\Services\NearbyParcelService;
use App\Helpers\ResponseHelper;

class NearbyParcelController extends Controller
{
    protected $service;

    public function __construct(NearbyParcelService $service)
    {
        $this->service = $service;
    }

    public function index(NearbyParcelRequest $request)
    {
        $data = $request->validated();
        $parcels = $this->service->getNearbyParcels($data['latitude'], $data['longitude']);
        return ResponseHelper::success($parcels, "Nearby parcels retrieved successfully");
    }
}
