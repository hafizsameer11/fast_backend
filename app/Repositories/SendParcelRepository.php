<?php

namespace App\Repositories;

use App\Models\SendParcel;

class SendParcelRepository
{
    public function all()
    {
        return SendParcel::with('user')->where('is_assigned', false)->latest()->get();
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
        return SendParcel::where('user_id', $userId)->with('acceptedBid.rider','user')->get();
    }
}
