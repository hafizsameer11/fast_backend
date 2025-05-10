<?php

namespace App\Repositories;

use App\Models\RiderLocation;
use App\Models\SendParcel;
use App\Services\GeoService;
use Illuminate\Support\Facades\Auth;

class SendParcelRepository
{
    private $geoService;
    public function __construct(GeoService $geoService)
    {
        $this->geoService = $geoService;
    }

    public function all($latitude, $longitude)
    {
        $user = Auth::user();
        $riderLocation = new RiderLocation();
        $riderLocation->rider_id = $user->id;
        $riderLocation->latitude = $latitude;
        $riderLocation->longitude = $longitude;
        $riderLocation->save();
        $riderLat = $latitude;
        $riderLng = $longitude;
        $radius = 100; // in KM

        if (!$riderLat || !$riderLng) {
            return response()->json(['error' => 'Missing rider coordinates'], 422);
        }

        $riderLocation = ['lat' => $riderLat, 'lng' => $riderLng];
        $filteredParcels = [];

        $parcels = SendParcel::with('user')->where('is_assigned', false)->latest()->get();

        foreach ($parcels as $parcel) {
            $senderRaw = $parcel->sender_address;
            $receiverRaw = $parcel->receiver_address;

            $resolvedSender = $this->geoService->geocodeToLatLng($senderRaw);
            $resolvedReceiver = $this->geoService->geocodeToLatLng($receiverRaw);

            if ($resolvedSender && $resolvedReceiver) {
                $toSender = $this->geoService->getRoadMetrics($riderLocation, $resolvedSender);
                $toReceiver = $this->geoService->getRoadMetrics($resolvedSender, $resolvedReceiver) ?? ['distance_km' => null, 'duration_min' => null];


                if ($toSender && $toSender['distance_km'] <= $radius) {
                    $parcel->distance_to_sender_km = round($toSender['distance_km'], 2);
                    $parcel->eta_to_sender_min = $toSender['duration_min'];

                    $parcel->distance_to_receiver_km = round($toReceiver['distance_km'], 2);
                    $parcel->eta_to_receiver_min = $toReceiver['duration_min'];

                    $filteredParcels[] = $parcel;
                }
            }
        }

        return $filteredParcels;
    }


    public function find($id)
    {
        return SendParcel::with('acceptedBid.rider', 'bids')->find($id)?->fresh(); // ensures latest data
    }

    public function create(array $data)
    {
        // Ensure initial status is ordered (optional safety)
        $data['status'] = 'ordered';

        // Set the ordered_at timestamp
        $data['ordered_at'] = now();

        return SendParcel::create($data);
    }

    public function update($id, array $data)
    {
        $parcel = SendParcel::findOrFail($id);
        $parcel->update($data);
        return $parcel;
    }

    public function delete($id)
    {
        // Add logic to delete data
    }
    public function updateStatus($id, $status)
    {
        $parcel = SendParcel::findOrFail($id);
        $parcel->status = $status;

        // Store the timestamp based on status
        $timestampField = match ($status) {
            'ordered' => 'ordered_at',
            'picked_up' => 'picked_up_at',
            'in_transit' => 'in_transit_at',
            'delivered' => 'delivered_at',
        };

        $parcel->$timestampField = now();

        $parcel->save();
        return $parcel;
    }
    public function getParcelForUser($userId)
    {
        return SendParcel::where('user_id', $userId)->with('acceptedBid.rider', 'user')->get();
    }
}
