<?php

namespace App\Repositories;

use App\Models\RiderLocation;

class RiderLocationRepository
{
    public function all()
    {
        return RiderLocation::all();
    }

    public function find($id)
    {
        return RiderLocation::find($id);
    }

    public function create(array $data)
    {
        return RiderLocation::create($data);
    }

    public function update($id, array $data)
    {
        $location = $this->find($id);
        if ($location) {
            $location->update($data);
        }
        return $location;
    }

    public function delete($id)
    {
        $location = $this->find($id);
        if ($location) {
            $location->delete();
        }
        return $location;
    }

    public function updateOrCreateLocation(array $data)
    {
        return RiderLocation::updateOrCreate(
            ['rider_id' => $data['rider_id']],
            ['latitude' => $data['latitude'], 'longitude' => $data['longitude']]
        );
    }

    public function getByRiderId($riderId)
    {
        return RiderLocation::where('rider_id', $riderId)->first();
    }
}
