<?php

// app/Repositories/NearbyParcelRepository.php
namespace App\Repositories;

use App\Models\SendParcel;

class NearbyParcelRepository
{
    public function getParcelsWithinRadius($lat, $lng, $radiusKm)
    {
        return SendParcel::selectRaw("*, 
            (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
            cos(radians(longitude) - radians(?)) + 
            sin(radians(?)) * sin(radians(latitude)))) AS distance", 
            [$lat, $lng, $lat])
            ->having("distance", "<=", $radiusKm)
            ->where('is_assigned', false)
            ->orderBy('distance', 'asc')
            ->get();
    }
}
