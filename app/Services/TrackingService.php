<?php
namespace App\Services;

use App\Repositories\TrackingRepository;
use Illuminate\Support\Facades\Http;

class TrackingService
{
    protected $repo;

    public function __construct(TrackingRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getRiderLocationByParcel($parcelId)
    {
        $riderId = $this->repo->getAssignedRiderId($parcelId);
        if (!$riderId) return null;

        return $this->repo->getRiderLocation($riderId);
    }

    public function getRouteToPickupOrDelivery($parcelId)
    {
        $rider = auth()->user();
        $current = $this->repo->getRiderLocation($rider->id);
        $parcel = $this->repo->getParcel($parcelId);

        if (!$current || !$parcel) return null;

        $destination = !$parcel->is_pickup_confirmed
            ? $parcel->sender_address
            : $parcel->receiver_address;

        $coords = $this->getCoordinatesFromAddress($destination);

        if (!$coords) return null;

        return $this->getDirections(
            $current->latitude,
            $current->longitude,
            $coords['lat'],
            $coords['lng']
        );
    }

    protected function getCoordinatesFromAddress($address)
    {
        $res = Http::withOptions([
            'verify' => base_path('cacert.pem')
        ])->get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key' => env('GOOGLE_MAPS_API_KEY')
        ]);

        return $res->json()['results'][0]['geometry']['location'] ?? null;
    }

    protected function getDirections($fromLat, $fromLng, $toLat, $toLng)
    {
        $res = Http::withOptions([
            'verify' => base_path('cacert.pem')
        ])->get('https://maps.googleapis.com/maps/api/directions/json', [
            'origin' => "$fromLat,$fromLng",
            'destination' => "$toLat,$toLng",
            'key' => env('GOOGLE_MAPS_API_KEY')
        ]);

        $data = $res->json();
        return $data['routes'][0] ?? null;
    }
}
