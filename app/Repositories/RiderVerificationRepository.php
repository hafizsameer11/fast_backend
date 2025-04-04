<?php

namespace App\Repositories;

use App\Models\RiderVerification;

class RiderVerificationRepository
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
        // Add logic to create data
    }

    public function update($id, array $data)
    {
        // Add logic to update data
    }

    public function delete($id)
    {
        // Add logic to delete data
    }
    public function createOrUpdate($userId, array $newData)
    {
        $existing = RiderVerification::where('user_id', $userId)->first();

        if ($existing) {
            $existing->update($newData);
            return $existing;
        }

        return RiderVerification::create(array_merge(['user_id' => $userId], $newData));
    }
}