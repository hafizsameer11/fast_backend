<?php

namespace App\Services;

use App\Repositories\ParcelHistoryRepository;

class ParcelHistoryService
{
    protected $repository;

    public function __construct(ParcelHistoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getRiderHistory($riderId)
    {
        return [
            'active' => $this->repository->getRiderParcelsByStatus($riderId, 'active'),
            'delivered' => $this->repository->getRiderParcelsByStatus($riderId, 'delivered'),
        ];
    }

    public function getUserHistory($userId)
    {
        return [
            'scheduled' => $this->repository->getUserScheduledParcels($userId),
            'active' => $this->repository->getUserActiveParcels($userId),
            'delivered' => $this->repository->getUserDeliveredParcels($userId),
        ];
    }
}
