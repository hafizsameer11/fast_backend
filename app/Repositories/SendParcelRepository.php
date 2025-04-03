<?php

namespace App\Repositories;
use App\Models\SendParcel;

class SendParcelRepository
{
    public function all()
    {
        return SendParcel::all();
    }

    public function find($id)
    {
        // Add logic to find data by ID
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
        // Add logic to update data
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

}