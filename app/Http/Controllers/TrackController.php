<?php

namespace App\Http\Controllers;

use App\Services\TrackingService;
use App\Helpers\ResponseHelper;

class TrackController extends Controller
{
    protected $service;

    public function __construct(TrackingService $service)
{
        $this->service = $service;
    }

    public function userViewRiderLocation($parcelId)
    {
        $location = $this->service->getRiderLocationByParcel($parcelId);
        return $location
            ? ResponseHelper::success($location, "Rider location retrieved")
            : ResponseHelper::error("Rider location not available", 404);
    }

    public function riderRouteToDelivery($parcelId)
    {
        $route = $this->service->getRouteToPickupOrDelivery($parcelId);
        return $route
            ? ResponseHelper::success($route, "Route retrieved")
            : ResponseHelper::error("Could not retrieve route", 404);
    }
}
