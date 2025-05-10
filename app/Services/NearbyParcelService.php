<?php

// app/Services/NearbyParcelService.php
namespace App\Services;

use App\Repositories\NearbyParcelRepository;
use Illuminate\Support\Facades\Http;

class NearbyParcelService
{
    protected $repo;

    public function __construct(NearbyParcelRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getNearbyParcels($lat, $lng, $address = null, $radiusKm = 10)
    {
        if ((!$lat || !$lng) && $address) {
            $coords = $this->geocodeAddress($address);
            if (!$coords)
                return collect(); // return empty if geocoding fails
            $lat = $coords['lat'];
            $lng = $coords['lng'];
        }

        return $this->repo->getParcelsWithinRadius($lat, $lng, $radiusKm);
    }

    protected function geocodeAddress($address)
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key' => env('GOOGLE_MAPS_API_KEY')
        ]);

        $json = $response->json();
        if (!empty($json['results'][0]['geometry']['location'])) {
            return $json['results'][0]['geometry']['location'];
        }

        return null;
    }
}
