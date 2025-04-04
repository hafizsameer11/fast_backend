<?php

namespace App\Services;

use App\Repositories\RiderVerificationRepository;

class RiderVerificationService
{
    protected $RiderVerificationRepository;

    public function __construct(RiderVerificationRepository $RiderVerificationRepository)
    {
        $this->RiderVerificationRepository = $RiderVerificationRepository;
    }

    public function all()
    {
        return $this->RiderVerificationRepository->all();
    }

    public function find($id)
    {
        return $this->RiderVerificationRepository->find($id);
    }

    public function create(array $data)
    {
        return $this->RiderVerificationRepository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->RiderVerificationRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->RiderVerificationRepository->delete($id);
    }
    public function storeStep($step, $data)
    {
        $userId = auth()->id(); // if using auth:sanctum
        return $this->RiderVerificationRepository->createOrUpdate($userId, $data);
    }
}