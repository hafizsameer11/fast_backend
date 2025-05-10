<?php

namespace App\Services;

use App\Repositories\WithdrawalRepository;
use Exception;

class WithdrawalService
{
    protected $withdrawalRepository; // ✅ Correct property name

       // ✅ Ensure correct dependency injection
       public function __construct(WithdrawalRepository $withdrawalRepository)
       {
           $this->withdrawalRepository = $withdrawalRepository;
       }

    public function all()
    {
        return $this->withdrawalRepository->all();
    }

    public function find($id)
    {
        return $this->withdrawalRepository->find($id);
    }

    public function create(array $data)
    {
        try {
            // ✅ Ensure withdrawalRepository is properly injected
            if (!$this->withdrawalRepository) {
                throw new Exception("WithdrawalRepository not initialized properly.");
            }

            return $this->withdrawalRepository->create($data);
        } catch (\Throwable $th) {
            throw new Exception("Error creating withdrawal: " . $th->getMessage());
        }
    }
    public function update($id, array $data)
    {
        return $this->WithdrawalRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->WithdrawalRepository->delete($id);
    }
    public function getAll()
    {
        return $this->withdrawalRepository->all();
    }
}
