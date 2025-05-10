<?php

namespace App\Repositories;

use App\Models\SendParcel;
use App\Models\RiderLocation;

class TrackingRepository
{
    public function getAssignedRiderId($parcelId)
    {
        $parcel = SendParcel::find($parcelId);
        return $parcel && $parcel->rider_id ? $parcel->rider_id : null;
    }

    public function getRiderLocation($riderId)
    {
        return RiderLocation::where('rider_id', $riderId)->first();
    }

    public function getParcel($parcelId)
    {
        return SendParcel::find($parcelId);
    }
}
