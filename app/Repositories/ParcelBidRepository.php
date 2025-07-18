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


  public function create(array $data)
{
    $parcelId = $data['send_parcel_id'];
    $parcel = SendParcel::findOrFail($parcelId);
    $user = Auth::user();

    // Only calculate distance & time if user is a rider
    if ($user->role === 'rider') {
        // Get rider's latest location
        $riderLocation = RiderLocation::where('rider_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($riderLocation) {
            // Calculate distance
            $miles = $this->calculateMiles(
                $parcel->sender_lat,
                $parcel->sender_long,
                $riderLocation->latitude,
                $riderLocation->longitude
            );

            // Calculate required time (e.g., miles * 2 mins)
            $requiredTimeMinutes = ceil($miles * 2);

            // Add to data array
            $data['required_time'] = $requiredTimeMinutes;
        }
    }

    return ParcelBid::create($data);
}



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

        return [
            'bids' => $bids,
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
