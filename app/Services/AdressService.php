<?php

namespace App\Services;

use App\Repositories\AddressRepository;
use Illuminate\Support\Facades\Auth;

// use Illuminate\Support\Facades\Auth;

class AdressService
{
    protected $AdressRepository;

    public function __construct(AddressRepository $AdressRepository)
    {
        $this->AdressRepository = $AdressRepository;
    }

    public function all()
    {
        return $this->AdressRepository->all();  // Calls the Repository
    }

    public function find($id)
    {
        return $this->AdressRepository->find($id);
    }

    public function create(array $data)
    {
        try {
            $user = Auth::user();
            $data['user_id'] = $user->id; // Assuming you want to set the user_id from the authenticated user
            return $this->AdressRepository->create($data);
        } catch (\Throwable $th) {
            throw new \Exception("Error creating Address:", $th->getMessage());
        }
    }

    public function update($id, array $data)
    {

        return $this->AdressRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->AdressRepository->delete($id);
    }
}
