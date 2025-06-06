<?php

namespace App\Repositories;

use App\Models\SendParcel;

class ParcelHistoryRepository
{
    public function getRiderParcelsByStatus($riderId, $type)
    {
        if ($type === 'active') {
            return SendParcel::where('rider_id', $riderId)
            ->with('user')
                ->whereNotIn('status', ['delivered'])
                ->latest()->get();
        }

        return SendParcel::where('rider_id', $riderId)
        ->with('user')
            ->where('status', 'delivered')
            ->latest()->get();
    }

    public function getUserScheduledParcels($userId)
    {
        return SendParcel::where('user_id', $userId)->with('acceptedBid.rider')
            ->where('is_assigned', false)
            ->latest()->get();
    }

    public function getUserActiveParcels($userId)
    {
        return SendParcel::where('user_id', $userId)->with('acceptedBid.rider')
            ->where('is_assigned', true)
            ->whereNotIn('status', ['delivered'])
            ->latest()->get();
    }

    public function getUserDeliveredParcels($userId)
    {
        return SendParcel::where('user_id', $userId)->with('acceptedBid.rider')
            ->where('status', 'delivered')
            ->latest()->get();
    }
}
