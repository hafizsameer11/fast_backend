<?php

namespace App\Repositories;

use App\Models\ParcelPayment;
use App\Models\RiderLocation;
use App\Models\SendParcel;
use App\Services\GeoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SendParcelRepository
{
    private $geoService;
    public function __construct(GeoService $geoService)
    {
        $this->geoService = $geoService;
    }
   private function calculateMiles($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 3958.8; // miles

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) ** 2 +
            cos($lat1) * cos($lat2) *
            sin($dLon / 2) ** 2;

        $c = 2 * asin(sqrt($a));

        return $earthRadius * $c;
    }
public function all($latitude, $longitude)
{
    $dbLocation = RiderLocation::where('rider_id', auth()->id())
        ->orderBy('created_at', 'desc')
        ->first();

    $riderLocation = [
        'latitude' => $dbLocation?->latitude ?? $latitude,
        'longitude' => $dbLocation?->longitude ?? $longitude,
    ];

    $parcels = SendParcel::with('user')
        ->where('is_assigned', false)
        ->whereNotNull('payment_method')
        ->latest()
        ->get();

    $updatedParcels = $parcels->map(function ($parcel) use ($riderLocation) {
        $defaultEta = 10;

        $hasRiderCoords = $riderLocation['latitude'] && $riderLocation['longitude'];
        $hasSenderCoords = $parcel->sender_lat && $parcel->sender_long;
        $hasReceiverCoords = $parcel->receiver_lat && $parcel->receiver_long;

        // ETA to sender
        if ($hasRiderCoords && $hasSenderCoords) {
            $milesToSender = $this->calculateMiles(
                $riderLocation['latitude'],
                $riderLocation['longitude'],
                $parcel->sender_lat,
                $parcel->sender_long
            );
            $etaToSender = ceil($milesToSender * 2);
        } else {
            $etaToSender = $defaultEta;
        }

        // ETA to receiver
        if ($hasSenderCoords && $hasReceiverCoords) {
            $milesToReceiver = $this->calculateMiles(
                $parcel->sender_lat,
                $parcel->sender_long,
                $parcel->receiver_lat,
                $parcel->receiver_long
            );
            $etaToReceiver = ceil($milesToReceiver * 2);
        } else {
            $etaToReceiver = $defaultEta;
        }

        $parcel->eta_to_sender_min = $etaToSender;
        $parcel->eta_to_receiver_min = $etaToReceiver;

        return $parcel;
    });

    return $updatedParcels;
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
        $geoService = new GeoService();

        // Geocode sender address
        if (isset($data['sender_address'])) {
            $senderCoords = $geoService->geocodeToLatLng($data['sender_address']);
            if ($senderCoords) {
                $data['sender_lat'] = $senderCoords['lat'];
                $data['sender_long'] = $senderCoords['lng'];
            } else {
                Log::info("Invalid receiver address — geocoding failed.");
            }
        }

        // Geocode receiver address
        if (isset($data['receiver_address'])) {
            $receiverCoords = $geoService->geocodeToLatLng($data['receiver_address']);
            if ($receiverCoords) {
                $data['receiver_lat'] = $receiverCoords['lat'];
                $data['receiver_long'] = $receiverCoords['lng'];
            } else {
                Log::info("Invalid receiver address — geocoding failed.");
                // throw new \Exception("Invalid receiver address — geocoding failed.");
            }
        }

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
