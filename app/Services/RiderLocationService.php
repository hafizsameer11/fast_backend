<?php

namespace App\Services;

use App\Repositories\RiderLocationRepository;

class RiderLocationService
{
    protected $RiderLocationRepository;

    public function __construct(RiderLocationRepository $RiderLocationRepository)
    {
        $this->RiderLocationRepository = $RiderLocationRepository;
    }

    public function all()
    {
        return $this->RiderLocationRepository->all();
    }

    public function find($id)
    {
        return $this->RiderLocationRepository->find($id);
    }

    public function create(array $data)
    {
        return $this->RiderLocationRepository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->RiderLocationRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->RiderLocationRepository->delete($id);
    }

    public function updateOrCreateLocation(array $data)
    {
        return $this->RiderLocationRepository->updateOrCreateLocation($data);
    }

    public function getByRiderId($riderId)
    {
        return $this->RiderLocationRepository->getByRiderId($riderId);
    }
}
