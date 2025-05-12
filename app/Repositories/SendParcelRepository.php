<?php

namespace App\Repositories;

use App\Models\ParcelPayment;
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

        $parcels = SendParcel::with('user')
            ->where('is_assigned', false)
            ->whereNotNull('payment_method')
            ->latest()
            ->get();


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
        return SendParcel::with('acceptedBid.rider', 'bids')->find($id); // ensures latest data
    }
    public function details($id)
    {
        $user = Auth::user();

        if ($user->role === 'user') {
            $parcel = SendParcel::with('acceptedBid.rider', 'bids')->find($id);
        } else {
            $parcel = SendParcel::with('user')->find($id);
        }

        if (!$parcel) {
            return response()->json(['error' => 'Parcel not found'], 404);
        }

        // Resolve coordinates from addresses
        $senderCoords = $this->geoService->geocodeToLatLng($parcel->sender_address);
        $receiverCoords = $this->geoService->geocodeToLatLng($parcel->receiver_address);

        // Add coordinates to parcel response (as custom attributes)
        $parcel->sender_coordinates = $senderCoords ?? null;
        $parcel->receiver_coordinates = $receiverCoords ?? null;

        return $parcel;
    }


    public function create(array $data)
    {
        // Ensure initial status is ordered (optional safety)
        $data['status'] = 'ordered';

        // Set the ordered_at timestamp
        $data['ordered_at'] = now();

        return SendParcel::create($data);
    }

    public function update($id, array $data, $step = null)
    {
        $parcel = SendParcel::findOrFail($id);
        $parcel->update($data);
        //create parcel payment
        if ($step == 4) {
            $parcelPayment = new ParcelPayment();
            $parcelPayment->parcel_id = $parcel->id;
            $parcelPayment->amount = $data['amount'];
            $parcelPayment->payment_method = $data['payment_method'];
            $parcelPayment->payment_status = 'pending';
            $refference = 'PAY-' . strtoupper(uniqid());
            $parcelPayment->payment_reference = $refference;
            $parcelPayment->delivery_fee = $data['delivery_fee'];
            $parcelPayment->is_pod = $data['pay_on_delivery'] ? true : false;
            $parcelPayment->save();
        }


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
        return SendParcel::with('acceptedBid.rider', 'user', 'parcelPayment')->where('user_id', $userId)->get();
    }
    public function getParcelForRider($userId)
    {
        return SendParcel::with('acceptedBid', 'user', 'rider', 'parcelPayment')->where('rider_id', $userId)->get();
    }
    public function cancelParcel($id, $reason)
    {
        $parcel = SendParcel::findOrFail($id);
        $parcel->is_canceled = true;
        $parcel->cancellation_reason = $reason;
        $parcel->save();
        return $parcel;
    }
}
