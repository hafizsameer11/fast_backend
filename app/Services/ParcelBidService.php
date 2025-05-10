<?php

namespace App\Services;

use App\Models\RiderLocation;
use App\Repositories\ParcelBidRepository;
use App\Models\SendParcel;
use Illuminate\Support\Facades\Http;

class ParcelBidService
{
    protected $repo, $geoService;

    public function __construct(ParcelBidRepository $repo, GeoService $geoService)

    {
        $this->geoService = $geoService;
        $this->repo = $repo;
    }

    public function createBid(array $data)
    {
        \Log::info('Creating Rider Bid:', $data);
        return $this->repo->create($data);
    }

    public function getParcelBids($parcelId)
    {
        return $this->repo->getBidsForParcel($parcelId);
    }

    public function getBid($bidId)
    {
        return $this->repo->find($bidId);
    }

    public function createUserBid(array $data)
    {
        return $this->repo->create($data);
    }

    public function acceptBid($bidId)
    {
        $bid = $this->repo->acceptBid($bidId);

        $parcel = $bid->parcel;
        $parcel->rider_id = $bid->rider_id;
        $parcel->accepted_bid_id = $bid->id;
        $parcel->is_assigned = true;
        $parcel->status = 'picked_up';
        $parcel->picked_up_at = now();

        if (!$parcel->pickup_code) {
            $parcel->pickup_code = rand(1000, 9999);
        }
        if (!$parcel->delivery_code) {
            $parcel->delivery_code = rand(1000, 9999);
        }
        $riderLocation = RiderLocation::where('rider_id', $bid->rider_id)->first();
        //check if rider location is not null
        if ($riderLocation) {
            $start_location = $this->geoService->reverseGeocode($riderLocation->latitude, $riderLocation->longitude);

            if ($start_location) {
                $parcel->rider_start_location = $start_location;
            }
        }


        $parcel->save();
        $this->repo->rejectOtherBids($parcel->id, $bid->id);

        return $parcel->load('acceptedBid', 'bids');
    }

    public function acceptUserBid($bidId)
    {
        $bid = $this->repo->acceptBid($bidId);

        $parcel = $bid->parcel;
        $parcel->rider_id = auth()->id();
        $parcel->accepted_bid_id = $bid->id;
        $parcel->is_assigned = true;
        $parcel->status = 'picked_up';
        $parcel->picked_up_at = now();
        $parcel->save();

        $this->repo->rejectOtherBids($parcel->id, $bid->id);

        return $parcel->load('acceptedBid', 'bids');
    }

    public function getParcelBidsWithinRange($parcelId, $riderLat, $riderLng, $maxDistance = 10)
    {
        $bids = $this->repo->getBidsForParcel($parcelId);

        return $bids->filter(function ($bid) use ($riderLat, $riderLng, $maxDistance) {
            $parcel = $bid->parcel;

            if (!$parcel) return false;

            $parcelLat = $parcel->latitude;
            $parcelLng = $parcel->longitude;

            if ((!$parcelLat || !$parcelLng) && $parcel->sender_address) {
                $coords = $this->getCoordinatesFromAddress($parcel->sender_address);
                if (!$coords) return false;

                $parcelLat = $coords['lat'];
                $parcelLng = $coords['lng'];
            }

            if (!$parcelLat || !$parcelLng) return false;

            $distance = $this->calculateDistance($riderLat, $riderLng, $parcelLat, $parcelLng);
            return $distance <= $maxDistance;
        });
    }

    protected function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    protected function getCoordinatesFromAddress($address)
    {
        $response = Http::withOptions([
            'verify' => base_path('cacert.pem') // Full path to your downloaded file
        ])->get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key' => env('GOOGLE_MAPS_API_KEY')
        ]);

        $json = $response->json();
        return $json['results'][0]['geometry']['location'] ?? null;
    }
}
