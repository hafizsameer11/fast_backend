<?php

namespace App\Services;

use App\Repositories\WalletRepository;

class WalletService
{
    protected $WalletRepository;

    public function __construct(WalletRepository $WalletRepository)
    {
        $this->WalletRepository = $WalletRepository;
    }

    public function all()
    {
        return $this->WalletRepository->all();
    }

    public function find($id)
    {
        return $this->WalletRepository->find($id);
    }

    public function create(array $data)
    {
        return $this->WalletRepository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->WalletRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->WalletRepository->delete($id);
    }
    public function madePayment($userId, $amount)
    {
        return $this->WalletRepository->madePayment($userId, $amount);
    }
    public function getWalletBalance($userId)
    {
        return $this->WalletRepository->getWalletBalance($userId);
    }
    public function getTransactionHistory($userId)
    {
        return $this->WalletRepository->getTransactionHistory($userId);
    }
    public function getVirtualAccount($userId)
    {
        return $this->WalletRepository->getVirtualAccount($userId);
    }
    public function generateVirtualAccount($userId)
    {
        return $this->WalletRepository->generateVirtualAccount($userId);
    }
}
