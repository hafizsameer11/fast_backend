<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\RiderVerificationRepository;
use Exception;

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
        if ($step == 1) {
            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                throw new Exception("User not found");
            }
        } else {
            // Get the most recently created user
            $user = User::latest()->first();
            if (!$user) {
                throw new Exception("No users found in the system.");
            }
        }

        $userId = $user->id;

        return $this->RiderVerificationRepository->createOrUpdate($userId, $data);
    }
}
