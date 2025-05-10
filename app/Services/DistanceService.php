<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DistanceService
{
    // If you need Google Maps distance
    protected $googleApiKey;

    public function __construct()
    {
        $this->googleApiKey = env('GOOGLE_MAPS_API_KEY');
    }

    public function calculateDistanceInKm($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    public function getLatLngFromAddress($address)
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key' => $this->googleApiKey
        ]);

        $data = $response->json();

        if (!empty($data['results'][0]['geometry']['location'])) {
            return $data['results'][0]['geometry']['location']; // ['lat' => ..., 'lng' => ...]
        }

        return null;
    }

    public function isWithin10Km($riderLat, $riderLng, $parcelLat = null, $parcelLng = null, $parcelAddress = null)
    {
        if (!$parcelLat || !$parcelLng) {
            if ($parcelAddress) {
                $coords = $this->getLatLngFromAddress($parcelAddress);
                if (!$coords) return false;

                $parcelLat = $coords['lat'];
                $parcelLng = $coords['lng'];
            } else {
                return false;
            }
        }

        $distance = $this->calculateDistanceInKm($riderLat, $riderLng, $parcelLat, $parcelLng);
        return $distance <= 10;
    }
}
