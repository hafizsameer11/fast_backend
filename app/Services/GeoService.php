<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        Log::info("Geocode response: ", $res->json());
        if ($res->ok() && isset($res['results'][0]['geometry']['location'])) {
            return $res['results'][0]['geometry']['location'];
        }

        return null;
    }
    public function reverseGeocode(float $latitude, float $longitude): ?string
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng' => "$latitude,$longitude",
            'key' => $this->apiKey,
        ]);

        Log::info("Reverse Geocode response: ", $response->json());

        if (
            $response->ok() &&
            isset($response['results'][0]['formatted_address'])
        ) {
            return $response['results'][0]['formatted_address'];
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
        Log::info("Distance Matrix response: ", $response->json());
        if (
            $response->ok() &&
            isset($response['rows'][0]['elements'][0]['status']) &&
            $response['rows'][0]['elements'][0]['status'] === 'OK'
        ) {
            return $response['rows'][0]['elements'][0]['distance']['value'] / 1000; // return in KM
        }

        return null;
    }
    public function getRoadMetrics(array|string $origin, array|string $destination): ?array
    {
        $origStr = is_array($origin) ? "{$origin['lat']},{$origin['lng']}" : $origin;
        $destStr = is_array($destination) ? "{$destination['lat']},{$destination['lng']}" : $destination;

        $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
            'origins' => $origStr,
            'destinations' => $destStr,
            'key' => $this->apiKey,
            'units' => 'metric'
        ]);

        Log::info("Distance Matrix response: ", $response->json());

        if (
            $response->ok() &&
            isset($response['rows'][0]['elements'][0]['status']) &&
            $response['rows'][0]['elements'][0]['status'] === 'OK'
        ) {
            $element = $response['rows'][0]['elements'][0];
            return [
                'distance_km' => $element['distance']['value'] / 1000,
                'duration_min' => round($element['duration']['value'] / 60, 1),
            ];
        }

        return null;
    }
}
