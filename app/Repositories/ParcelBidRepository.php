<?php

namespace App\Repositories;
use App\Models\ParcelBid;
use App\Models\SendParcel;

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
