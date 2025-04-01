<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    protected $UserRepository;

    public function __construct(UserRepository $UserRepository)
    {
        $this->UserRepository = $UserRepository;
    }

    public function all()
    {
        return $this->UserRepository->all();
    }

    public function find($id)
    {
        return $this->UserRepository->find($id);
    }


    public function create(array $data)
    {
        try {
            $data['otp'] = rand(100000, 999999);
            return $this->UserRepository->create($data);
        } catch (\Exception $e) {
            throw new \Exception("Error creating user: " . $e->getMessage());
        }
    }

    public function update($id, array $data)
    {
        return $this->UserRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->UserRepository->delete($id);
    }
}
