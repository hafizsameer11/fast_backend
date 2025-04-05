<?php

namespace App\Repositories;
use App\Models\ParcelBid;

class ParcelBidRepository
{
    public function all()
    {
        // Add logic to fetch all data
    }

    public function find($id)
    {
        // Add logic to find data by ID
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
        return ParcelBid::where('send_parcel_id', $parcelId)->with('rider')->get();
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