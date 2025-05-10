<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeoService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GOOGLE_MAPS_API_KEY');
    }

    public function geocodeToLatLng(string $location): ?array
    {
        if (preg_match('/^-?\d+(\.\d+)?\s*,\s*-?\d+(\.\d+)?$/', $location)) {
            [$lat, $lng] = explode(',', $location);
            return ['lat' => trim($lat), 'lng' => trim($lng)];
        }

        $res = Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
            'address' => $location,
            'key' => $this->apiKey
        ]);

        if ($res->ok() && isset($res['results'][0]['geometry']['location'])) {
            return $res['results'][0]['geometry']['location'];
        }

        return null;
    }

    public function getRoadDistance(array|string $origin, array|string $destination): ?float
    {
        $origStr = is_array($origin) ? "{$origin['lat']},{$origin['lng']}" : $origin;
        $destStr = is_array($destination) ? "{$destination['lat']},{$destination['lng']}" : $destination;

        $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
            'origins' => $origStr,
            'destinations' => $destStr,
            'key' => $this->apiKey,
            'units' => 'metric'
        ]);

        if (
            $response->ok() &&
            isset($response['rows'][0]['elements'][0]['status']) &&
            $response['rows'][0]['elements'][0]['status'] === 'OK'
        ) {
            return $response['rows'][0]['elements'][0]['distance']['value'] / 1000; // return in KM
        }

        return null;
    }
}
