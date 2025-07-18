<?php

namespace App\Repositories;

use App\Models\ParcelBid;
use App\Models\RiderLocation;
use App\Models\SendParcel;
use Illuminate\Support\Facades\Auth;

class ParcelBidRepository
{
    public function all()
    {
        // Add logic to fetch all data
    }
    public function find($id)
    {
        return ParcelBid::with('parcel')->findOrFail($id);
    }


    public function create(array $data)
    {
        $parcelId = $data['send_parcel_id'];
        return ParcelBid::create($data);
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

    public function createRider(array $data)
    {
        $parcelId = $data['send_parcel_id'];
        $parcel = SendParcel::findOrFail($parcelId);
        $rider = Auth::user();

        // Get rider's latest location
        $riderLocation = RiderLocation::where('rider_id', $rider->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$riderLocation) {
            throw new \Exception("Rider location not found.");
        }

        // Calculate distance
        $miles = $this->calculateMiles(
            $parcel->sender_lat,
            $parcel->sender_long,
            $riderLocation->latitude,
            $riderLocation->longitude
        );

        // Calculate required time (e.g., miles * 2 mins)
        $requiredTimeMinutes = ceil($miles * 2); // rounded up

        // Add to data array
        $data['required_time'] = $requiredTimeMinutes;

        return ParcelBid::create($data);
    }

    // return ParcelBid::create($data);
    // }
    public function update($id, array $data)
    {
        // Add logic to update data
    }

    public function delete($id)
    {
        // Add logic to delete data
    }
    public function getBidsForParcel($parcelId)
    {
        $bids = ParcelBid::with(['rider', 'user'])
            ->where('send_parcel_id', $parcelId)
            ->where('created_by', 'rider') // ✅ filters only rider-created bids
            ->get();

        $parcel = SendParcel::where('id', $parcelId)
            ->first();
            $userBid=ParcelBid::where('send_parcel_id', $parcelId)
            ->where('created_by', 'user') // ✅ filters only user-created bids
            ->first();

        return [
            'bids' => $bids,
            'userBid' => $userBid,
            'parcel' => $parcel,
        ];
    }

    public function acceptBid($bidId)
    {
        $bid = ParcelBid::findOrFail($bidId);
        $bid->status = 'accepted';
        $bid->save();

        return $bid;
    }

    public function rejectOtherBids($parcelId, $exceptBidId)
    {
        return ParcelBid::where('send_parcel_id', $parcelId)
            ->where('id', '!=', $exceptBidId)
            ->update(['status' => 'rejected']);
    }
}
